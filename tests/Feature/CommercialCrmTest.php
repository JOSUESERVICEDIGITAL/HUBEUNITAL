<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Chamber;
use App\Models\CommercialAccessAccount;
use App\Models\User;
use Database\Seeders\CommercialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommercialCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CommercialSeeder::class);
        Chamber::query()->update(['is_open' => true]);
    }

    private function record(string $module, array $data): int
    {
        return DB::table('com_'.$module)->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
    }

    private function user(string $profile = 'commercial-agent'): User
    {
        $user = User::factory()->create(['role' => $profile === 'root' ? 'superadmin' : 'author', 'email_verified_at' => now()]);
        $employee = DB::table('rh_employees')->insertGetId(['user_id' => $user->id, 'name' => $user->name, 'registration' => 'STAFF-'.$user->id, 'department_id' => DB::table('rh_departments')->value('id'), 'position' => 'Agent', 'profession' => 'sales', 'hired_on' => '2026-01-01', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        if ($profile !== 'root') CommercialAccessAccount::create(['user_id' => $user->id, 'employee_id' => $employee, 'is_active' => true])->assignRole($profile);
        return $user;
    }

    private function employee(User $user): int { return DB::table('rh_employees')->where('user_id', $user->id)->value('id'); }

    private function client(User $user, string $name = 'École Alpha'): int
    {
        return $this->record('clients', ['name' => $name, 'employee_id' => $this->employee($user), 'status' => 'active']);
    }

    private function order(User $agent): int
    {
        $client = $this->client($agent);
        $quote = $this->record('quotes', ['name' => 'DEV-'.DB::table('com_quotes')->count(), 'employee_id' => $this->employee($agent), 'client_id' => $client, 'chamber_id' => Chamber::where('slug', 'ingenierie')->value('id'), 'amount' => 100000, 'valid_until' => '2027-12-31', 'status' => 'accepted']);
        return $this->record('orders', ['name' => 'CMD-'.$quote, 'employee_id' => $this->employee($agent), 'client_id' => $client, 'quote_id' => $quote, 'date' => '2026-10-01', 'status' => 'confirmed', 'amount' => 100000]);
    }

    private function problem(User $agent, string $name = 'Absences scolaires sur papier'): array
    {
        return ['name' => $name, 'employee_id' => $this->employee($agent), 'sector' => 'Éducation', 'location' => 'Ziniaré', 'latitude' => 12.58234, 'longitude' => -1.29876, 'description' => 'Les parents sont informés tardivement.', 'frequency' => 'daily', 'impact' => 'Notifications retardées', 'source_method' => 'voluntary', 'priority' => 'high'];
    }

    public function test_19_business_entries_and_8_field_entries_render(): void
    {
        $this->actingAs($this->user('root'));
        $this->assertCount(25, config('commercial.modules'));
        $this->get('/commerciale')->assertOk();
        foreach (array_keys(config('commercial.modules')) as $module) {
            $this->get('/commerciale/'.$module)->assertOk();
            $this->get('/commerciale/'.$module.'/nouveau')->assertOk();
        }
        foreach (['statistiques', 'cartographie', 'transmissions', 'historique', 'acces'] as $page) $this->get('/commerciale/'.$page)->assertOk();
        $this->get('/chambres/commerciale')->assertOk()->assertSee('Ouvrir la Chambre Commerciale');
    }

    public function test_agent_only_sees_own_clients_and_cannot_impersonate_or_export(): void
    {
        $one = $this->user(); $two = $this->user();
        $mine = $this->client($one, 'Client autorisé'); $other = $this->client($two, 'Client confidentiel');
        $this->actingAs($one)->get('/commerciale/clients')->assertOk()->assertSee('Client autorisé')->assertDontSee('Client confidentiel');
        $this->get('/commerciale/clients/'.$other)->assertNotFound();
        $this->get('/commerciale/clients?employee_id='.$this->employee($two))->assertOk()->assertDontSee('Client confidentiel');
        $this->post('/commerciale/clients', ['name' => 'Faux', 'employee_id' => $this->employee($two), 'status' => 'active'])->assertForbidden();
        $this->get('/commerciale/clients/export')->assertForbidden();
        $this->get('/rh/payroll')->assertForbidden();
        $this->get('/commerciale/acces')->assertForbidden();
        $this->get('/commerciale/clients/'.$mine)->assertOk();
    }

    public function test_active_rh_employment_and_chamber_opening_are_required(): void
    {
        $agent = $this->user();
        $this->actingAs($agent)->get('/commerciale')->assertOk();
        Chamber::where('slug', 'commerciale')->update(['is_open' => false]);
        $this->get('/commerciale')->assertForbidden();
        $this->post('/commerciale/prospects', [])->assertForbidden();
        Chamber::where('slug', 'commerciale')->update(['is_open' => true]);
        DB::table('rh_employees')->where('user_id', $agent->id)->update(['status' => 'departed']);
        $this->get('/commerciale')->assertForbidden();
    }

    public function test_qualified_prospect_converts_once_and_order_amount_comes_from_accepted_quote(): void
    {
        $agent = $this->user(); $employee = $this->employee($agent);
        $prospect = $this->record('prospects', ['name' => 'École nouvelle', 'employee_id' => $employee, 'status' => 'qualified']);
        $this->actingAs($agent)->post('/commerciale/prospects/'.$prospect.'/convertir')->assertRedirect();
        $this->post('/commerciale/prospects/'.$prospect.'/convertir')->assertSessionHasErrors('status');
        $client = DB::table('com_prospects')->where('id', $prospect)->value('converted_client_id');
        $quote = $this->record('quotes', ['name' => 'DEV-ALPHA', 'employee_id' => $employee, 'client_id' => $client, 'chamber_id' => Chamber::where('slug', 'ingenierie')->value('id'), 'amount' => 50000, 'valid_until' => '2027-01-01', 'status' => 'accepted']);
        $data = ['name' => 'CMD-ALPHA', 'employee_id' => $employee, 'client_id' => $client, 'quote_id' => $quote, 'date' => '2026-10-01', 'status' => 'confirmed', 'amount' => 1];
        $this->post('/commerciale/orders', $data)->assertRedirect();
        $this->assertDatabaseHas('com_orders', ['quote_id' => $quote, 'amount' => 50000]);
        $this->post('/commerciale/orders', $data)->assertSessionHasErrors('quote_id');
    }

    public function test_juridique_approval_is_required_for_signature_and_terms_changes_reset_it(): void
    {
        $agent = $this->user(); $order = $this->order($agent); $sale = DB::table('com_orders')->find($order);
        $data = ['name' => 'Contrat Alpha', 'employee_id' => $sale->employee_id, 'client_id' => $sale->client_id, 'order_id' => $order, 'starts_on' => '2026-10-01', 'terms' => 'Conditions initiales', 'status' => 'review'];
        $this->actingAs($agent)->post('/commerciale/contracts', array_replace($data, ['status' => 'signed', 'legal_status' => 'approved']))->assertSessionHasErrors('status');
        $this->post('/commerciale/contracts', $data)->assertRedirect();
        $id = DB::table('com_contracts')->value('id');
        $this->post('/commerciale/contrats/'.$id.'/avis', ['legal_status' => 'approved', 'legal_notes' => 'Valide'])->assertForbidden();
        $this->actingAs($this->user('commercial-juridique'))->post('/commerciale/contrats/'.$id.'/avis', ['legal_status' => 'approved', 'legal_notes' => 'Vérification effectuée'])->assertRedirect();
        $this->actingAs($agent)->put('/commerciale/contracts/'.$id, array_replace($data, ['status' => 'signed']))->assertRedirect();
        $this->put('/commerciale/contracts/'.$id, array_replace($data, ['terms' => 'Nouvelles conditions', 'status' => 'signed']))->assertSessionHasErrors('status');
        $this->put('/commerciale/contracts/'.$id, array_replace($data, ['terms' => 'Nouvelles conditions']))->assertRedirect();
        $this->assertDatabaseHas('com_contracts', ['id' => $id, 'legal_status' => 'pending']);
    }

    public function test_finance_owns_invoices_and_payments_and_prevents_overpayment(): void
    {
        $agent = $this->user(); $order = $this->order($agent); $sale = DB::table('com_orders')->find($order);
        $data = ['name' => 'FAC-ALPHA', 'employee_id' => $sale->employee_id, 'client_id' => $sale->client_id, 'order_id' => $order, 'issued_on' => '2026-10-01', 'due_date' => '2026-11-01', 'amount' => 100000, 'status' => 'issued'];
        $this->actingAs($agent)->post('/commerciale/invoices', $data)->assertForbidden();
        $finance = $this->user('commercial-finance');
        $this->actingAs($finance)->get('/commerciale/invoices/nouveau')->assertOk()->assertSee('CMD-');
        $this->post('/commerciale/invoices', $data)->assertRedirect();
        $id = DB::table('com_invoices')->value('id');
        $this->post('/commerciale/factures/'.$id.'/paiement', ['reference' => 'PAY-1', 'paid_on' => '2026-10-02', 'amount' => 70000])->assertRedirect();
        $this->post('/commerciale/factures/'.$id.'/paiement', ['reference' => 'PAY-2', 'paid_on' => '2026-10-02', 'amount' => 40000])->assertSessionHasErrors('amount');
        $this->actingAs($agent)->get('/commerciale/invoices/'.$id)->assertOk()->assertSee('70 000');
        $this->post('/commerciale/factures/'.$id.'/paiement', ['reference' => 'PAY-3', 'paid_on' => '2026-10-02', 'amount' => 1])->assertForbidden();
        $this->actingAs($finance)->put('/commerciale/invoices/'.$id, array_replace($data, ['amount' => 50000]))->assertSessionHasErrors('amount');
    }

    public function test_commission_needs_separate_hr_and_finance_approval_and_creates_one_payroll_line(): void
    {
        $agent = $this->user(); $order = $this->order($agent);
        $manager = $this->user('commercial-responsable');
        $data = ['name' => 'Commission Alpha', 'employee_id' => $this->employee($agent), 'order_id' => $order, 'period' => '2026-10', 'rate' => 5, 'amount' => 99999, 'status' => 'finance_approved'];
        $this->actingAs($manager)->post('/commerciale/commissions', $data)->assertRedirect();
        $id = DB::table('com_commissions')->value('id');
        $this->assertDatabaseHas('com_commissions', ['id' => $id, 'amount' => 5000, 'status' => 'proposed']);
        $this->post('/commerciale/commissions/'.$id.'/valider')->assertForbidden();
        $hr = $this->user('commercial-rh'); $finance = $this->user('commercial-finance');
        $this->actingAs($finance)->get('/commerciale/commissions/'.$id)->assertNotFound();
        $this->actingAs($hr)->post('/commerciale/commissions/'.$id.'/valider')->assertRedirect();
        $this->assertDatabaseCount('rh_payroll', 0);
        $this->actingAs($finance)->post('/commerciale/commissions/'.$id.'/valider')->assertRedirect();
        $this->assertDatabaseHas('rh_payroll', ['employee_id' => $this->employee($agent), 'amount' => 5000, 'status' => 'approved']);
        $this->post('/commerciale/commissions/'.$id.'/valider')->assertSessionHasErrors('status');
        $this->assertDatabaseCount('rh_payroll', 1);
    }

    public function test_reporter_cannot_advance_other_agents_cases_or_approve_own_findings(): void
    {
        $one = $this->user('commercial-veille'); $two = $this->user('commercial-veille');
        $this->actingAs($one)->post('/commerciale/problems', $this->problem($one) + ['status' => 'deployed'])->assertRedirect();
        $id = DB::table('com_problems')->value('id');
        $this->assertDatabaseHas('com_problems', ['id' => $id, 'status' => 'reported', 'latitude' => 12.58, 'longitude' => -1.30]);
        $this->actingAs($two)->get('/commerciale/problems/'.$id)->assertNotFound();
        $this->post('/commerciale/observations', ['name' => 'Intrusion', 'employee_id' => $this->employee($two), 'problem_id' => $id, 'date' => '2026-10-29', 'summary' => 'Rapport'])->assertForbidden();
        $this->actingAs($one)->post('/commerciale/besoins/'.$id.'/etape', ['stage' => 'verifying', 'stage_notes' => 'Rapport transmis'])->assertRedirect();
        $this->post('/commerciale/besoins/'.$id.'/etape', ['stage' => 'validated', 'stage_notes' => 'Auto-validation'])->assertForbidden();
    }

    public function test_full_need_to_project_pipeline_with_shared_chambers_and_reporter_followup(): void
    {
        $agent = $this->user('commercial-veille'); $innovation = $this->user('commercial-innovation'); $engineer = $this->user('commercial-ingenierie'); $technical = $this->user('commercial-technique');
        $this->actingAs($agent)->post('/commerciale/problems', $this->problem($agent))->assertRedirect();
        $id = DB::table('com_problems')->value('id');
        $this->actingAs($innovation)->get('/commerciale/problems/'.$id)->assertNotFound();
        $this->actingAs($agent)->post('/commerciale/besoins/'.$id.'/etape', ['stage' => 'verifying', 'stage_notes' => 'Besoin anonymisé transmis'])->assertRedirect();
        $this->actingAs($innovation)->get('/commerciale/problems/'.$id)->assertOk();
        $this->post('/commerciale/besoins/'.$id.'/etape', ['stage' => 'study', 'stage_notes' => 'Saut interdit'])->assertSessionHasErrors('stage');
        $this->post('/commerciale/clusters', ['name' => 'Gestion scolaire', 'sector' => 'Éducation', 'summary' => 'Besoins récurrents des établissements'])->assertRedirect();
        $cluster = DB::table('com_clusters')->value('id');
        $this->post('/commerciale/besoins/'.$id.'/etape', ['stage' => 'validated', 'cluster_id' => $cluster, 'stage_notes' => 'Besoin regroupé et validé'])->assertRedirect();
        Chamber::where('slug', 'commerciale')->update(['is_open' => false]);
        $this->actingAs($engineer)->post('/commerciale/besoins/'.$id.'/etape', ['stage' => 'study', 'stage_notes' => 'Étude engagée'])->assertRedirect();
        $this->post('/commerciale/besoins/'.$id.'/etape', ['stage' => 'proposed', 'stage_notes' => 'Projet'])->assertSessionHasErrors('stage');
        $proposalData = ['name' => 'Application de présence', 'problem_id' => $id, 'chamber_id' => Chamber::where('slug', 'developpement')->value('id'), 'hypothesis' => 'Notification des parents', 'feasibility' => 'Test sur un établissement volontaire'];
        $this->post('/commerciale/proposals', $proposalData)->assertRedirect();
        $this->post('/commerciale/besoins/'.$id.'/etape', ['stage' => 'proposed', 'stage_notes' => 'Projet étudié et proposé'])->assertRedirect();
        $proposal = DB::table('com_proposals')->value('id');
        $solution = ['name' => 'Prototype Alpha', 'problem_id' => $id, 'proposal_id' => $proposal, 'chamber_id' => $proposalData['chamber_id'], 'results' => 'Tests volontaires satisfaisants', 'deployed_on' => '2026-10-01'];
        $this->actingAs($technical)->post('/commerciale/solutions', $solution)->assertRedirect();
        foreach (['development', 'tested', 'deployed'] as $stage) $this->post('/commerciale/besoins/'.$id.'/etape', ['stage' => $stage, 'stage_notes' => 'Suivi documenté '.$stage])->assertRedirect();
        Chamber::where('slug', 'commerciale')->update(['is_open' => true]);
        $this->actingAs($agent)->get('/commerciale/problems/'.$id)->assertOk()->assertSee('Déployé')->assertSee('Projet étudié et proposé');
        $this->get('/commerciale/cartographie')->assertOk()->assertSee('Ziniaré');
        $this->assertDatabaseHas('com_problems', ['id' => $id, 'status' => 'deployed', 'cluster_id' => $cluster]);
    }

    public function test_roles_and_seed_preserve_existing_chambers_and_rh_employees(): void
    {
        $root = $this->user('root'); $agent = $this->user('commercial-agent');
        $this->actingAs($root)->post('/commerciale/acces', ['employee_id' => $this->employee($agent), 'profile' => 'commercial-veille', 'is_active' => 1])->assertRedirect();
        $this->assertTrue(CommercialAccessAccount::where('user_id', $agent->id)->first()->hasRole('commercial-veille'));
        $count = DB::table('rh_employees')->count();
        $this->seed(CommercialSeeder::class);
        $this->assertDatabaseCount('chambers', 13);
        $this->assertSame($count, DB::table('rh_employees')->count());
        $this->assertSame(1, Article::where('slug', 'chambre-commerciale-eunital')->count());
        $this->actingAs($agent)->get('/commerciale/clients')->assertForbidden();
        $this->get('/commerciale/problems')->assertOk();
    }

    public function test_commercial_leave_opinion_precedes_rh_decision_without_exposing_private_reasons(): void
    {
        $agent = $this->user(); $employee = $this->employee($agent);
        $manager = $this->user('commercial-responsable'); $root = $this->user('root');
        DB::table('rh_balances')->insert(['employee_id' => $employee, 'year' => 2026, 'type' => 'annual', 'entitlement' => 10]);
        $data = ['employee_id' => $employee, 'type' => 'annual', 'starts_on' => '2026-11-02', 'ends_on' => '2026-11-06', 'days' => 5, 'status' => 'pending', 'notes' => 'Motif personnel confidentiel'];
        $this->actingAs($root)->post('/rh/leaves', $data)->assertRedirect();
        $id = DB::table('rh_leaves')->value('id');
        $this->put('/rh/leaves/'.$id, array_replace($data, ['status' => 'approved']))->assertSessionHasErrors('status');
        $this->actingAs($agent)->post('/commerciale/conges/'.$id.'/avis', ['commercial_opinion' => 'favorable', 'commercial_notes' => 'Auto-avis'])->assertForbidden();
        $this->actingAs($manager)->get('/commerciale/disponibilites-rh')->assertOk()->assertDontSee('Motif personnel confidentiel');
        $this->post('/commerciale/conges/'.$id.'/avis', ['commercial_opinion' => 'favorable', 'commercial_notes' => 'Couverture des visites organisée'])->assertRedirect();
        $this->actingAs($root)->put('/rh/leaves/'.$id, array_replace($data, ['status' => 'approved']))->assertRedirect();
        $this->assertDatabaseHas('rh_balances', ['employee_id' => $employee, 'used' => 5]);
        $this->actingAs($manager)->post('/commerciale/conges/'.$id.'/avis', ['commercial_opinion' => 'unfavorable', 'commercial_notes' => 'Trop tard'])->assertSessionHasErrors('status');
    }

    public function test_editing_a_pending_commercial_leave_resets_its_opinion(): void
    {
        $agent = $this->user(); $root = $this->user('root');
        $data = ['employee_id' => $this->employee($agent), 'type' => 'annual', 'starts_on' => '2026-11-02', 'ends_on' => '2026-11-06', 'days' => 5, 'status' => 'pending'];
        $this->actingAs($root)->post('/rh/leaves', $data)->assertRedirect();
        $id = DB::table('rh_leaves')->value('id');
        $this->actingAs($this->user('commercial-responsable'))->post('/commerciale/conges/'.$id.'/avis', ['commercial_opinion' => 'favorable', 'commercial_notes' => 'Avis initial'])->assertRedirect();
        $this->actingAs($root)->put('/rh/leaves/'.$id, array_replace($data, ['days' => 4]))->assertRedirect();
        $this->assertDatabaseHas('rh_leaves', ['id' => $id, 'commercial_opinion' => 'pending', 'commercial_notes' => null]);
    }

    public function test_reseeding_preserves_articles_whose_slugs_changed_after_editing(): void
    {
        $rh = Article::where('slug', 'chambre-rh-eunital')->firstOrFail();
        $commercial = Article::where('slug', 'chambre-commerciale-eunital')->firstOrFail();
        DB::table('articles')->where('id', $rh->id)->update(['slug' => 'presentation-rh-modifiee', 'description' => 'Texte RH personnalisé']);
        DB::table('articles')->where('id', $commercial->id)->update(['slug' => 'presentation-commerciale-modifiee']);
        $count = Article::count();
        $this->seed(CommercialSeeder::class);
        $this->assertSame($count, Article::count());
        $this->assertDatabaseHas('articles', ['id' => $rh->id, 'slug' => 'presentation-rh-modifiee', 'description' => 'Texte RH personnalisé']);
        $this->assertDatabaseHas('articles', ['id' => $commercial->id, 'slug' => 'presentation-commerciale-modifiee']);
    }
}
