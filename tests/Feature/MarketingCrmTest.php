<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Chamber;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Database\Seeders\MarketingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarketingCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChamberSeeder::class);
        Chamber::where('slug', 'marketing')->update(['is_open' => true]);
    }

    private function user(string $role = 'admin'): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }

    private function record(string $module, array $data): int
    {
        return DB::table('mkt_'.$module)->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
    }

    private function campaign(array $overrides = []): int
    {
        $client = $this->record('clients', ['name' => 'Client campagne', 'status' => 'active']);

        return $this->record('campaigns', $overrides + [
            'name' => 'Campagne Alpha', 'client_id' => $client, 'status' => 'active', 'objective' => 'leads',
            'start_date' => today()->subDays(10)->format('Y-m-d'), 'due_date' => today()->addDays(10)->format('Y-m-d'),
            'budget' => 100000, 'brief' => 'Acquisition Alpha',
        ]);
    }

    private function channel(): int
    {
        return $this->record('channels', ['name' => 'Web', 'type' => 'website', 'status' => 'active']);
    }

    private function resultData(int $campaign, int $channel): array
    {
        return ['name' => 'Relevé Alpha', 'campaign_id' => $campaign, 'channel_id' => $channel,
            'recorded_on' => today()->format('Y-m-d'), 'impressions' => 1000, 'clicks' => 100,
            'leads' => 20, 'conversions' => 4, 'spend' => 10000, 'revenue' => 40000];
    }

    public function test_access_is_isolated_between_marketing_and_engineering(): void
    {
        $this->get(route('marketing.dashboard'))->assertRedirect(route('login'));
        $member = $this->user('author');
        DB::table('eng_team')->insert(['user_id' => $member->id, 'role' => 'engineer', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($member)->get(route('marketing.dashboard'))->assertForbidden();
        $this->post(route('marketing.store', 'clients'), ['name' => 'Blocked', 'status' => 'active'])->assertForbidden();
        $this->record('team', ['user_id' => $member->id, 'role' => 'marketer', 'status' => 'active']);
        $this->get(route('marketing.dashboard'))->assertOk();
        DB::table('mkt_team')->where('user_id', $member->id)->update(['status' => 'inactive']);
        $this->get(route('marketing.export', 'clients'))->assertForbidden();
    }

    public function test_only_superadmin_closes_marketing_and_all_operations_stop_without_affecting_engineering(): void
    {
        $this->actingAs($this->user())->patch(route('chambers.status', 'marketing'), ['is_open' => 0])->assertForbidden();
        $this->actingAs($this->user('superadmin'))->patch(route('chambers.status', 'marketing'), ['is_open' => 0])->assertRedirect();
        foreach (['dashboard', 'reports', 'calendar', 'pipeline', 'audit'] as $route) {
            $this->get(route('marketing.'.$route))->assertForbidden();
        }
        $this->get(route('marketing.export', 'clients'))->assertForbidden();
        $this->post(route('marketing.store', 'clients'), ['name' => 'Blocked', 'status' => 'active'])->assertForbidden();
        $this->post(route('marketing.convert', 1))->assertForbidden();
        $this->get(route('engineering.dashboard'))->assertOk();
        $this->get(route('chambers.show', 'marketing'))->assertOk()->assertSee('CRM Marketing est prêt');
        $this->patch(route('chambers.status', 'marketing'), ['is_open' => 1])->assertRedirect();
        $this->get(route('marketing.dashboard'))->assertOk();
        $this->assertDatabaseHas('chamber_audits', ['chamber_slug' => 'marketing', 'action' => 'close']);
        $this->assertDatabaseCount('mkt_clients', 0);
    }

    public function test_team_management_and_deletion_are_reserved_to_admins(): void
    {
        $user = $this->user('author');
        $this->record('team', ['user_id' => $user->id, 'role' => 'marketer', 'status' => 'active']);
        $client = $this->record('clients', ['name' => 'Client Alpha', 'status' => 'active']);
        $this->actingAs($user)->post(route('marketing.store', 'team'), ['user_id' => $this->user('author')->id, 'role' => 'manager', 'status' => 'active'])->assertForbidden();
        $this->delete(route('marketing.destroy', ['clients', $client]))->assertForbidden();
        $this->post(route('marketing.store', 'clients'), ['name' => 'Client Beta', 'status' => 'active'])->assertRedirect();
    }

    public function test_creative_approval_requires_manager_and_real_publication_metadata(): void
    {
        $marketer = $this->user('author');
        $membership = $this->record('team', ['user_id' => $marketer->id, 'role' => 'marketer', 'status' => 'active']);
        $data = ['name' => 'Contenu Alpha', 'campaign_id' => $this->campaign(), 'channel_id' => $this->channel(),
            'format' => 'social', 'status' => 'review', 'version' => '1.0', 'copy' => 'Découvrez Alpha'];
        $this->actingAs($marketer)->post(route('marketing.store', 'contents'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('marketing.store', 'contents'), array_replace($data, ['status' => 'approved', 'content_url' => 'https://example.com/content']))->assertSessionHasErrors('status');
        DB::table('mkt_team')->where('id', $membership)->update(['role' => 'manager']);
        $this->post(route('marketing.store', 'contents'), array_replace($data, ['status' => 'approved']))->assertSessionHasErrors('content_url');
        $this->post(route('marketing.store', 'contents'), array_replace($data, ['status' => 'scheduled', 'content_url' => 'https://example.com/content']))->assertSessionHasErrors('due_date');
        $published = array_replace($data, ['status' => 'published', 'content_url' => 'https://example.com/content', 'published_on' => today()->addDay()->format('Y-m-d')]);
        $this->post(route('marketing.store', 'contents'), $published)->assertSessionHasErrors('published_on');
        $published['published_on'] = today()->format('Y-m-d');
        $this->post(route('marketing.store', 'contents'), $published)->assertSessionHasNoErrors()->assertRedirect();
        DB::table('mkt_team')->where('id', $membership)->update(['role' => 'marketer']);
        $id = DB::table('mkt_contents')->where('status', 'published')->value('id');
        $this->put(route('marketing.update', ['contents', $id]), array_replace($published, ['copy' => 'Unapproved change']))->assertSessionHasErrors('status');
    }

    public function test_daily_results_are_unique_validated_editable_and_reported_without_double_counting(): void
    {
        $campaign = $this->campaign();
        $channel = $this->channel();
        $data = $this->resultData($campaign, $channel);
        $this->actingAs($this->user())->post(route('marketing.store', 'results'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $id = DB::table('mkt_results')->value('id');
        $this->post(route('marketing.store', 'results'), $data)->assertSessionHasErrors('recorded_on');
        $this->put(route('marketing.update', ['results', $id]), array_replace($data, ['revenue' => 50000]))->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('marketing.store', 'results'), array_replace($data, ['recorded_on' => today()->subDay()->format('Y-m-d'), 'clicks' => 2000]))->assertSessionHasErrors('clicks');
        $this->post(route('marketing.store', 'results'), array_replace($data, ['recorded_on' => today()->addDay()->format('Y-m-d')]))->assertSessionHasErrors('recorded_on');
        $this->post(route('marketing.store', 'expenses'), ['name' => 'Visuels', 'campaign_id' => $campaign, 'amount' => 5000, 'spent_on' => today()->format('Y-m-d'), 'category' => 'production'])->assertRedirect();
        $this->get(route('marketing.reports'))->assertOk()->assertSee('10,00 %')->assertSee('500,00')->assertSee('2 500,00')->assertSee('5,00 ×')->assertSee('85 000,00');
        $this->get(route('marketing.show', ['campaigns', $campaign]))->assertOk()->assertSee('85 000');
        $this->assertDatabaseCount('mkt_results', 1);
    }

    public function test_cross_client_and_cross_campaign_links_are_rejected(): void
    {
        $one = $this->campaign();
        $two = $this->campaign(['name' => 'Autre campagne']);
        $client = DB::table('mkt_campaigns')->where('id', $one)->value('client_id');
        $content = $this->record('contents', ['name' => 'Other content', 'campaign_id' => $two, 'channel_id' => $this->channel(), 'format' => 'social', 'status' => 'draft', 'version' => '1', 'copy' => 'Test']);
        $this->actingAs($this->user())->post(route('marketing.store', 'tasks'), ['name' => 'Mismatch', 'campaign_id' => $one, 'content_id' => $content, 'status' => 'todo', 'priority' => 'normal'])->assertSessionHasErrors('content_id');
        $this->post(route('marketing.store', 'assets'), ['name' => 'Wrong client', 'client_id' => $client, 'campaign_id' => $two, 'type' => 'logo', 'version' => '1', 'file_url' => 'https://example.com/logo.png', 'usage_rights' => 'Owned', 'status' => 'draft'])->assertSessionHasErrors('client_id');
    }

    public function test_lead_conversion_is_repeatable_without_duplicate_clients_and_consent_blocks_outreach(): void
    {
        $this->actingAs($this->user());
        $lead = $this->record('leads', ['name' => 'Prospect Alpha', 'email' => 'prospect@example.com', 'status' => 'qualified', 'contact_permission' => 'no']);
        $this->post(route('marketing.store', 'activities'), ['name' => 'Call', 'lead_id' => $lead, 'type' => 'call', 'status' => 'planned'])->assertSessionHasErrors('lead_id');
        $this->post(route('marketing.store', 'activities'), ['name' => 'Note', 'lead_id' => $lead, 'type' => 'note', 'status' => 'done'])->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('marketing.convert', $lead))->assertRedirect();
        $this->post(route('marketing.convert', $lead))->assertRedirect();
        $this->assertDatabaseCount('mkt_clients', 1);
        $this->assertDatabaseHas('mkt_leads', ['id' => $lead, 'status' => 'won', 'client_id' => DB::table('mkt_clients')->value('id')]);
        $this->get(route('marketing.pipeline'))->assertOk()->assertSee('Prospect Alpha');
    }

    public function test_audits_and_exports_remain_separate_between_chambers(): void
    {
        $this->actingAs($this->user());
        $this->post(route('marketing.store', 'clients'), ['name' => '=FORMULA', 'status' => 'active'])->assertRedirect();
        $this->post(route('engineering.store', 'clients'), ['name' => 'Engineering private name', 'status' => 'active'])->assertRedirect();
        $this->get(route('marketing.audit'))->assertOk()->assertDontSee('Engineering private name');
        $this->get(route('engineering.audit'))->assertOk()->assertDontSee('=FORMULA');
        $response = $this->get(route('marketing.export', 'clients'))->assertOk();
        $this->assertStringContainsString("'=FORMULA", $response->streamedContent());
        $this->assertStringNotContainsString('Engineering private name', $response->streamedContent());
        $this->get(route('marketing.index', 'clients.fields'))->assertNotFound();
    }

    public function test_all_marketing_pages_render_and_calendar_respects_month(): void
    {
        $this->actingAs($this->user());
        foreach (array_keys(config('marketing.modules')) as $module) {
            $this->get(route('marketing.index', $module))->assertOk();
            $this->get(route('marketing.create', $module))->assertOk();
        }
        $campaign = $this->campaign();
        $channel = $this->channel();
        $this->record('contents', ['name' => 'Publication ce mois', 'campaign_id' => $campaign, 'channel_id' => $channel, 'format' => 'social', 'status' => 'scheduled', 'version' => '1', 'due_date' => today()->format('Y-m-d'), 'copy' => 'Message', 'content_url' => 'https://example.com/post']);
        $this->get(route('marketing.calendar', ['month' => today()->format('Y-m')]))->assertOk()->assertSee('Publication ce mois');
        $this->get(route('marketing.calendar', ['month' => today()->addMonth()->format('Y-m')]))->assertOk()->assertDontSee('Publication ce mois');
        $this->get(route('marketing.calendar', ['month' => 'invalid']))->assertSessionHasErrors('month');
        $this->get(route('marketing.reports'))->assertOk();
        $this->get(route('marketing.dashboard'))->assertOk();
    }

    public function test_dashboard_kpis_open_their_lists_with_expected_campaign_filter(): void
    {
        $this->actingAs($this->user());
        $this->campaign(['name' => 'Active campaign', 'status' => 'active']);
        $this->campaign(['name' => 'Paused campaign', 'status' => 'paused']);
        $dashboard = $this->get(route('marketing.dashboard'))->assertOk();
        preg_match_all('/<a class="eng-kpi" href="([^"]+)"/', $dashboard->getContent(), $matches);
        $this->assertGreaterThanOrEqual(8, count($matches[1]));
        foreach (array_slice($matches[1], 0, 8) as $index => $href) {
            $response = $this->get(html_entity_decode($href))->assertOk();
            if ($index === 0) {
                $response->assertSee('Active campaign')->assertDontSee('Paused campaign');
            }
        }
    }

    public function test_marketing_seeders_publish_article_once_and_preserve_closure_and_existing_channels(): void
    {
        Chamber::where('slug', 'marketing')->update(['is_open' => false]);
        $this->seed(MarketingSeeder::class);
        DB::table('mkt_channels')->where('name', 'Facebook')->update(['status' => 'inactive']);
        $this->seed(MarketingSeeder::class);
        $this->assertDatabaseCount('mkt_channels', 7);
        $this->assertDatabaseHas('mkt_channels', ['name' => 'Facebook', 'status' => 'inactive']);
        $this->assertDatabaseCount('articles', 1);
        $this->assertFalse(Chamber::where('slug', 'marketing')->first()->is_open);
        $article = Article::first();
        $this->assertEquals('marketing', $article->category->slug);
        $this->assertEquals(1, $article->isActive);
        $this->assertStringStartsWith('https://images.unsplash.com/', $article->imageUrl());
    }

    public function test_complete_marketing_workflow_and_all_record_screens(): void
    {
        $admin = $this->user();
        $marketer = $this->user('author');
        $this->actingAs($admin);
        $create = function (string $module, array $data): int {
            $this->post(route('marketing.store', $module), $data)->assertSessionHasNoErrors()->assertRedirect();
            $id = DB::table('mkt_'.$module)->max('id');
            $this->get(route('marketing.show', [$module, $id]))->assertOk();
            $this->get(route('marketing.edit', [$module, $id]))->assertOk();

            return $id;
        };
        $create('team', ['user_id' => $marketer->id, 'role' => 'marketer', 'status' => 'active']);
        $client = $create('clients', ['name' => 'Marque Alpha', 'status' => 'active']);
        $contact = $create('contacts', ['name' => 'Contact Alpha', 'client_id' => $client]);
        $channel = $create('channels', ['name' => 'Canal Alpha', 'type' => 'social', 'owner_id' => $marketer->id, 'status' => 'active']);
        $audience = $create('audiences', ['name' => 'Entrepreneurs', 'client_id' => $client, 'status' => 'active', 'consent_basis' => 'Inscriptions volontaires']);
        $strategy = $create('strategies', ['name' => 'Positionnement Alpha', 'client_id' => $client, 'owner_id' => $admin->id, 'version' => '1', 'status' => 'approved', 'positioning' => 'Proximité et expertise', 'objectives' => '20 prospects qualifiés']);
        $campaign = $create('campaigns', ['name' => 'Lancement Alpha', 'client_id' => $client, 'strategy_id' => $strategy, 'audience_id' => $audience, 'owner_id' => $marketer->id, 'objective' => 'leads', 'status' => 'active', 'start_date' => today()->subDay()->format('Y-m-d'), 'due_date' => today()->addDays(10)->format('Y-m-d'), 'budget' => 100000, 'brief' => 'Présenter la nouvelle offre']);
        $lead = $create('leads', ['name' => 'Prospect acquis', 'campaign_id' => $campaign, 'channel_id' => $channel, 'assignee_id' => $marketer->id, 'status' => 'qualified', 'contact_permission' => 'yes']);
        $contentData = ['name' => 'Publication Alpha', 'campaign_id' => $campaign, 'channel_id' => $channel, 'owner_id' => $marketer->id, 'format' => 'social', 'status' => 'scheduled', 'version' => '1', 'copy' => 'Découvrez notre offre', 'content_url' => 'https://example.com/post', 'due_date' => today()->format('Y-m-d')];
        $content = $create('contents', $contentData);
        $task = $create('tasks', ['name' => 'Préparer le visuel', 'campaign_id' => $campaign, 'content_id' => $content, 'assignee_id' => $marketer->id, 'status' => 'todo', 'priority' => 'high']);
        $create('results', $this->resultData($campaign, $channel));
        $create('expenses', ['name' => 'Création visuelle', 'campaign_id' => $campaign, 'amount' => 5000, 'spent_on' => today()->format('Y-m-d'), 'category' => 'production']);
        $create('activities', ['name' => 'Relance Alpha', 'campaign_id' => $campaign, 'lead_id' => $lead, 'contact_id' => $contact, 'assignee_id' => $marketer->id, 'type' => 'call', 'status' => 'planned']);
        $create('assets', ['name' => 'Logo Alpha', 'client_id' => $client, 'campaign_id' => $campaign, 'owner_id' => $marketer->id, 'type' => 'logo', 'version' => '1', 'file_url' => 'https://example.com/logo.png', 'usage_rights' => 'Création client', 'status' => 'approved']);
        $this->get(route('marketing.index', ['module' => 'tasks', 'campaign_id' => $campaign]))->assertOk()->assertSee('Préparer le visuel');
        $this->get(route('marketing.calendar'))->assertOk()->assertSee('Publication Alpha');
        $this->get(route('marketing.pipeline'))->assertOk()->assertSee('Prospect acquis');
        $this->get(route('marketing.reports'))->assertOk()->assertSee('4,00 ×')->assertSee('85 000,00');
        $this->post(route('marketing.convert', $lead))->assertRedirect();
        $this->post(route('marketing.convert', $lead))->assertRedirect();
        $this->assertDatabaseCount('mkt_clients', 2);
        $this->assertDatabaseHas('mkt_tasks', ['id' => $task, 'campaign_id' => $campaign]);
        $this->delete(route('marketing.destroy', ['campaigns', $campaign]))->assertSessionHasErrors('delete');
        $this->get(route('marketing.dashboard'))->assertOk()->assertSee('40 000');
    }
}
