<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Chamber;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Database\Seeders\EngineeringArticleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EngineeringCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChamberSeeder::class);
    }

    private function user(string $role = 'admin'): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }

    private function record(string $module, array $data): int
    {
        return DB::table('eng_'.$module)->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
    }

    public function test_all_chambers_are_seeded_without_duplicates_and_reseeding_preserves_closure(): void
    {
        Chamber::where('slug', 'ingenierie')->update(['is_open' => false]);
        $this->seed(ChamberSeeder::class);
        $this->assertDatabaseCount('chambers', 13);
        $this->assertDatabaseCount('categories', 13);
        $this->assertFalse(Chamber::where('slug', 'ingenierie')->first()->is_open);
    }

    public function test_guests_and_nonmembers_cannot_access_crm_and_exports(): void
    {
        $this->get(route('engineering.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($this->user('author'))->get(route('engineering.dashboard'))->assertForbidden();
        $this->get(route('engineering.export', 'clients'))->assertForbidden();
    }

    public function test_only_superadmin_can_close_and_reopen_and_closure_blocks_reads_writes_and_exports(): void
    {
        $this->actingAs($this->user())->patch(route('chambers.status', 'ingenierie'), ['is_open' => 0])->assertForbidden();
        $this->actingAs($this->user('superadmin'))->patch(route('chambers.status', 'ingenierie'), ['is_open' => 0])->assertRedirect();
        $this->get(route('engineering.dashboard'))->assertForbidden();
        $this->get(route('engineering.export', 'clients'))->assertForbidden();
        $this->post(route('engineering.store', 'clients'), ['name' => 'Blocked', 'status' => 'active'])->assertForbidden();
        $this->assertDatabaseCount('eng_clients', 0);
        $this->get(route('chambers.show', 'ingenierie'))->assertOk()->assertSee('Chambre fermée');
        $this->patch(route('chambers.status', 'ingenierie'), ['is_open' => 1])->assertRedirect();
        $this->get(route('engineering.dashboard'))->assertOk();
        $this->assertDatabaseCount('chamber_audits', 2);
    }

    public function test_admin_can_create_edit_filter_and_delete_client_with_audit_trail(): void
    {
        $this->actingAs($this->user());
        $this->post(route('engineering.store', 'clients'), ['name' => 'Alpha', 'email' => 'hello@example.com', 'status' => 'active'])->assertRedirect();
        $client = DB::table('eng_clients')->first();
        $this->put(route('engineering.update', ['clients', $client->id]), ['name' => 'Alpha révisé', 'status' => 'archived'])->assertRedirect();
        $this->get(route('engineering.index', ['module' => 'clients', 'status' => 'active']))->assertOk()->assertDontSee('Alpha révisé');
        $this->get(route('engineering.show', ['clients', $client->id]))->assertOk()->assertSee('Alpha révisé');
        $this->delete(route('engineering.destroy', ['clients', $client->id]))->assertRedirect();
        $this->assertDatabaseCount('eng_clients', 0);
        $this->assertDatabaseCount('chamber_audits', 3);
    }

    public function test_member_access_is_revocable_and_members_cannot_grant_access_or_delete(): void
    {
        $engineer = $this->user('author');
        $membership = $this->record('team', ['user_id' => $engineer->id, 'role' => 'engineer', 'status' => 'active']);
        $this->actingAs($engineer)->get(route('engineering.dashboard'))->assertOk();
        $this->post(route('engineering.store', 'team'), ['user_id' => $this->user('author')->id, 'role' => 'manager', 'status' => 'active'])->assertForbidden();
        $client = $this->record('clients', ['name' => 'Client', 'status' => 'active']);
        $this->delete(route('engineering.destroy', ['clients', $client]))->assertForbidden();
        DB::table('eng_team')->where('id', $membership)->update(['status' => 'inactive']);
        $this->get(route('engineering.index', 'clients'))->assertForbidden();
    }

    public function test_related_client_mismatch_is_rejected_and_used_clients_cannot_be_deleted(): void
    {
        $client = $this->record('clients', ['name' => 'One', 'status' => 'active']);
        $other = $this->record('clients', ['name' => 'Two', 'status' => 'active']);
        $contact = $this->record('contacts', ['name' => 'Contact', 'client_id' => $other]);
        $this->actingAs($this->user())->post(route('engineering.store', 'opportunities'), [
            'name' => 'Mismatch', 'client_id' => $client, 'contact_id' => $contact,
            'amount' => 1000, 'probability' => 50, 'status' => 'new',
        ])->assertSessionHasErrors('contact_id');
        $this->delete(route('engineering.destroy', ['clients', $other]))->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('eng_clients', ['id' => $other]);
    }

    public function test_payments_cannot_exceed_balance_and_paid_invoice_cannot_be_cancelled_or_reduced(): void
    {
        $client = $this->record('clients', ['name' => 'Client', 'status' => 'active']);
        $invoice = $this->record('invoices', ['reference' => 'INV-001', 'client_id' => $client, 'amount' => 1000, 'status' => 'issued']);
        $payment = ['name' => 'Acompte', 'invoice_id' => $invoice, 'amount' => 600, 'payment_date' => today()->format('Y-m-d'), 'method' => 'bank'];
        $this->actingAs($this->user())->post(route('engineering.store', 'payments'), $payment)->assertRedirect();
        $this->post(route('engineering.store', 'payments'), $payment)->assertSessionHasErrors('amount');
        $this->put(route('engineering.update', ['invoices', $invoice]), ['reference' => 'INV-001', 'client_id' => $client, 'amount' => 500, 'status' => 'issued'])->assertSessionHasErrors('amount');
        $this->put(route('engineering.update', ['invoices', $invoice]), ['reference' => 'INV-001', 'client_id' => $client, 'amount' => 1000, 'status' => 'cancelled'])->assertSessionHasErrors('amount');
        $this->get(route('engineering.show', ['invoices', $invoice]))->assertOk()->assertSee('400,00');
        $this->assertDatabaseCount('eng_payments', 1);
    }

    public function test_engineers_cannot_accept_livrables_and_project_task_mismatch_is_rejected(): void
    {
        $client = $this->record('clients', ['name' => 'Client', 'status' => 'active']);
        $project = $this->record('projects', ['name' => 'Project', 'client_id' => $client, 'budget' => 1000, 'status' => 'active']);
        $engineer = $this->user('author');
        $this->record('team', ['user_id' => $engineer->id, 'role' => 'engineer', 'status' => 'active']);
        $data = ['name' => 'Architecture', 'project_id' => $project, 'status' => 'accepted', 'version' => '1.0', 'document_url' => 'https://example.com/design.pdf'];
        $this->actingAs($engineer)->post(route('engineering.store', 'deliverables'), $data)->assertSessionHasErrors('status');
        $this->actingAs($this->user())->post(route('engineering.store', 'deliverables'), $data)->assertRedirect();
        $this->get(route('engineering.reports'))->assertOk()->assertSee('Project');
    }

    public function test_all_crm_pages_render_and_exports_are_safe(): void
    {
        $this->actingAs($this->user());
        foreach (array_keys(config('engineering.modules')) as $module) {
            $this->get(route('engineering.index', $module))->assertOk();
            $this->get(route('engineering.create', $module))->assertOk();
        }
        $this->record('clients', ['name' => '=HYPERLINK("bad")', 'status' => 'active']);
        $response = $this->get(route('engineering.export', 'clients'))->assertOk();
        $this->assertStringContainsString("'=HYPERLINK", $response->streamedContent());
        $this->get(route('engineering.index', 'unknown'))->assertNotFound();
        $this->get(route('engineering.audit'))->assertOk();
    }

    public function test_engineering_article_is_published_once_and_supports_online_image(): void
    {
        $this->user();
        $this->seed(EngineeringArticleSeeder::class);
        $this->seed(EngineeringArticleSeeder::class);
        $this->assertDatabaseCount('articles', 1);
        $article = Article::first();
        $this->assertEquals('ingenierie', $article->category->slug);
        $this->assertEquals(1, $article->isActive);
        $this->assertStringStartsWith('https://images.unsplash.com/', $article->imageUrl());
    }

    public function test_full_project_delivery_and_billing_workflow_with_reports_and_linked_filters(): void
    {
        $admin = $this->user();
        $engineer = $this->user('author');
        $this->actingAs($admin);
        $create = function (string $module, array $data): int {
            $this->post(route('engineering.store', $module), $data)->assertSessionHasNoErrors()->assertRedirect();
            $id = DB::table('eng_'.$module)->max('id');
            $this->get(route('engineering.show', [$module, $id]))->assertOk();
            $this->get(route('engineering.edit', [$module, $id]))->assertOk();

            return $id;
        };
        $create('team', ['user_id' => $engineer->id, 'role' => 'engineer', 'status' => 'active']);
        $client = $create('clients', ['name' => 'Atelier Alpha', 'status' => 'active']);
        $contact = $create('contacts', ['name' => 'Contact Alpha', 'client_id' => $client]);
        $opportunity = $create('opportunities', ['name' => 'Étude du réseau', 'client_id' => $client, 'contact_id' => $contact, 'owner_id' => $admin->id, 'amount' => 1500000, 'probability' => 100, 'status' => 'won']);
        $this->get(route('engineering.create', ['module' => 'projects', 'opportunity_id' => $opportunity]))->assertOk()->assertSee('Étude du réseau');
        $project = $create('projects', ['name' => 'Architecture Alpha', 'client_id' => $client, 'opportunity_id' => $opportunity, 'budget' => 1500000, 'status' => 'active']);
        $task = $create('tasks', ['name' => 'Dessiner le schéma', 'project_id' => $project, 'assignee_id' => $engineer->id, 'status' => 'done', 'priority' => 'high']);
        $create('deliverables', ['name' => 'Schéma validé', 'project_id' => $project, 'task_id' => $task, 'status' => 'accepted', 'version' => '1.0', 'document_url' => 'https://example.com/schema.pdf']);
        $quote = $create('quotes', ['reference' => 'DEV-2026-001', 'client_id' => $client, 'project_id' => $project, 'amount' => 1500000, 'status' => 'accepted']);
        $this->get(route('engineering.create', ['module' => 'invoices', 'quote_id' => $quote]))->assertOk()->assertSee('1500000');
        $invoice = $create('invoices', ['reference' => 'FAC-2026-001', 'client_id' => $client, 'project_id' => $project, 'quote_id' => $quote, 'amount' => 1500000, 'status' => 'issued']);
        $create('payments', ['name' => 'Règlement Alpha', 'invoice_id' => $invoice, 'amount' => 1500000, 'payment_date' => today()->format('Y-m-d'), 'method' => 'bank']);
        $create('expenses', ['name' => 'Matériel Alpha', 'project_id' => $project, 'amount' => 200000, 'spent_on' => today()->format('Y-m-d'), 'category' => 'equipment']);
        $create('activities', ['name' => 'Réception avec le client', 'client_id' => $client, 'project_id' => $project, 'contact_id' => $contact, 'type' => 'meeting', 'status' => 'done']);
        $this->get(route('engineering.index', ['module' => 'tasks', 'project_id' => $project]))->assertOk()->assertSee('Dessiner le schéma');
        $this->get(route('engineering.index', ['module' => 'invoices', 'unpaid' => 1]))->assertOk()->assertDontSee('FAC-2026-001');
        $this->get(route('engineering.reports'))->assertOk()->assertSee('1 300 000,00')->assertSee('1/1');
        $this->get(route('engineering.dashboard'))->assertOk()->assertSee('1 500 000');
        $this->assertDatabaseCount('chamber_audits', 12);
    }
}
