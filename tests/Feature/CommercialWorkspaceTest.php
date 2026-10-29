<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\CommercialAccessAccount;
use App\Models\User;
use Database\Seeders\CommercialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommercialWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CommercialSeeder::class);
        Chamber::query()->update(['is_open' => true]);
        Storage::fake('commercial_private');
    }

    private function agent(string $profile = 'commercial-agent'): User
    {
        $user = User::factory()->create(['role' => 'author', 'email_verified_at' => now()]);
        $employee = DB::table('rh_employees')->insertGetId(['name' => $user->name, 'user_id' => $user->id, 'registration' => 'STAFF-'.$user->id, 'department_id' => DB::table('rh_departments')->value('id'), 'position' => 'Commercial', 'profession' => 'sales', 'hired_on' => '2026-01-01', 'status' => 'active']);
        CommercialAccessAccount::create(['user_id' => $user->id, 'employee_id' => $employee, 'is_active' => true])->assignRole($profile);
        return $user;
    }

    private function employee(User $user): int { return DB::table('rh_employees')->where('user_id', $user->id)->value('id'); }

    private function record(string $module, array $data): int
    {
        return DB::table('com_'.$module)->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
    }

    private function client(User $agent, string $name = 'Client Alpha'): int
    {
        return $this->record('clients', ['name' => $name, 'employee_id' => $this->employee($agent), 'status' => 'active']);
    }

    private function quote(User $agent, int $client): array
    {
        return ['name' => 'DEV-ALPHA', 'employee_id' => $this->employee($agent), 'client_id' => $client, 'chamber_id' => Chamber::where('slug', 'ingenierie')->value('id'), 'amount' => 1, 'valid_until' => '2027-12-31', 'status' => 'accepted', 'items_present' => 1, 'items' => [
            ['description' => 'Conception de plateforme', 'quantity' => '2', 'unit_price' => '12500'],
            ['description' => 'Formation utilisateurs', 'quantity' => '1.5', 'unit_price' => '10000'],
        ]];
    }

    public function test_detailed_quote_total_is_computed_and_flows_to_order_and_print(): void
    {
        $agent = $this->agent(); $client = $this->client($agent);
        $input = $this->quote($agent, $client);
        $this->actingAs($agent)->post('/commerciale/quotes', $input)->assertRedirect();
        $quote = DB::table('com_quotes')->first();
        $this->assertSame(40000.0, (float) $quote->amount);
        $this->assertDatabaseCount('com_quote_items', 2);
        $this->get('/commerciale/quotes/'.$quote->id)->assertOk()->assertSee('Conception de plateforme');
        $this->get('/commerciale/quotes/'.$quote->id.'/modifier')->assertOk()->assertSee('items[0][quantity]', false);
        $this->get('/commerciale/quotes/'.$quote->id.'/imprimer')->assertOk()->assertSee('40 000,00')->assertSee('Client Alpha');
        $this->get('/commerciale/orders/nouveau?quote_id='.$quote->id)->assertOk()->assertViewHas('record', fn ($prefill) => $prefill->client_id === $client && $prefill->employee_id === $this->employee($agent));
        $this->post('/commerciale/orders', ['name' => 'CMD-ALPHA', 'employee_id' => $this->employee($agent), 'client_id' => $client, 'quote_id' => $quote->id, 'date' => '2026-10-29', 'status' => 'confirmed', 'amount' => 1])->assertRedirect();
        $this->assertDatabaseHas('com_orders', ['quote_id' => $quote->id, 'amount' => 40000]);
        $this->put('/commerciale/quotes/'.$quote->id, array_replace($input, ['items' => [['description' => 'Modification refusée', 'quantity' => 1, 'unit_price' => 1]]]))->assertSessionHasErrors('status');
        $this->assertDatabaseHas('com_quote_items', ['quote_id' => $quote->id, 'description' => 'Conception de plateforme']);
    }

    public function test_quote_line_validation_rejects_bad_values_without_partial_writes(): void
    {
        $agent = $this->agent(); $input = $this->quote($agent, $this->client($agent));
        $this->actingAs($agent)->post('/commerciale/quotes', array_replace($input, ['items' => []]))->assertSessionHasErrors('items');
        $this->post('/commerciale/quotes', array_replace($input, ['items' => [['description' => 'Invalide', 'quantity' => -1, 'unit_price' => 10]]]))->assertSessionHasErrors('items.0.quantity');
        $this->post('/commerciale/quotes', array_replace($input, ['items' => [['description' => 'Invalide', 'quantity' => 1, 'unit_price' => -10]]]))->assertSessionHasErrors('items.0.unit_price');
        $this->assertDatabaseCount('com_quotes', 0);
        $this->assertDatabaseCount('com_quote_items', 0);
    }

    public function test_agent_cannot_print_edit_or_prefill_another_agents_dossier(): void
    {
        $one = $this->agent(); $two = $this->agent(); $client = $this->client($one);
        $this->actingAs($one)->post('/commerciale/quotes', $this->quote($one, $client))->assertRedirect();
        $id = DB::table('com_quotes')->value('id');
        $this->actingAs($two)->get('/commerciale/quotes/'.$id.'/imprimer')->assertNotFound();
        $this->get('/commerciale/quotes/'.$id.'/modifier')->assertNotFound();
        $this->get('/commerciale/orders/nouveau?quote_id='.$id)->assertForbidden();
        $this->get('/commerciale/contacts/nouveau?client_id='.$client)->assertForbidden();
    }

    public function test_client_view_links_contacts_exchanges_and_next_sales_steps(): void
    {
        $agent = $this->agent(); $client = $this->client($agent);
        $this->actingAs($agent)->post('/commerciale/contacts', ['name' => 'Directeur Alpha', 'employee_id' => $this->employee($agent), 'client_id' => $client, 'email' => 'directeur@example.com'])->assertRedirect();
        $this->post('/commerciale/interactions', ['name' => 'Appel de découverte', 'employee_id' => $this->employee($agent), 'client_id' => $client, 'date' => '2026-10-29', 'type' => 'call', 'summary' => 'Besoin de suivi scolaire.'])->assertRedirect();
        $this->get('/commerciale/clients/'.$client)->assertOk()->assertSee('Vue complète du client')->assertSee('Contacts et interlocuteurs')->assertSee('Échanges et comptes rendus')->assertSee('Devis et propositions');
        $this->get('/commerciale/contacts?client_id='.$client)->assertOk()->assertSee('Directeur Alpha');
        $this->get('/commerciale/interactions?client_id='.$client)->assertOk()->assertSee('Appel de découverte');
    }

    public function test_pipeline_is_scoped_and_changes_are_audited(): void
    {
        $one = $this->agent(); $two = $this->agent();
        $mine = $this->record('opportunities', ['name' => 'Projet autorisé', 'employee_id' => $this->employee($one), 'client_id' => $this->client($one), 'amount' => 25000, 'status' => 'qualified']);
        $secret = $this->record('opportunities', ['name' => 'Projet confidentiel', 'employee_id' => $this->employee($two), 'client_id' => $this->client($two), 'amount' => 30000, 'status' => 'qualified']);
        $this->actingAs($one)->get('/commerciale/pipeline')->assertOk()->assertSee('Projet autorisé')->assertDontSee('Projet confidentiel');
        $this->patch('/commerciale/opportunites/'.$secret.'/etape', ['status' => 'won'])->assertNotFound();
        $this->patch('/commerciale/opportunites/'.$mine.'/etape', ['status' => 'negotiation'])->assertRedirect();
        $this->assertDatabaseHas('chamber_audits', ['chamber_slug' => 'commerciale', 'module' => 'opportunities', 'record_id' => $mine, 'action' => 'pipeline']);
    }

    public function test_agenda_combines_only_own_open_actions_in_the_selected_week(): void
    {
        $one = $this->agent(); $two = $this->agent(); $client = $this->client($one);
        $this->record('appointments', ['name' => 'Rendez-vous Alpha', 'employee_id' => $this->employee($one), 'client_id' => $client, 'starts_at' => '2026-10-29 10:00:00', 'status' => 'planned']);
        $this->record('followups', ['name' => 'Relance Alpha', 'employee_id' => $this->employee($one), 'client_id' => $client, 'due_date' => '2026-10-30', 'status' => 'planned']);
        $this->record('visits', ['name' => 'Visite terminée', 'employee_id' => $this->employee($one), 'date' => '2026-10-29', 'location' => 'Ziniaré', 'purpose' => 'sales', 'status' => 'done']);
        $this->record('visits', ['name' => 'Visite confidentielle', 'employee_id' => $this->employee($two), 'date' => '2026-10-29', 'location' => 'Ziniaré', 'purpose' => 'sales', 'status' => 'planned']);
        $this->actingAs($one)->get('/commerciale/agenda?from=2026-10-26')->assertOk()->assertSee('Rendez-vous Alpha')->assertSee('Relance Alpha')->assertDontSee('Visite terminée')->assertDontSee('Visite confidentielle');
        $this->get('/commerciale/agenda?from=2026-11-02')->assertOk()->assertDontSee('Rendez-vous Alpha')->assertDontSee('Relance Alpha');
    }

    public function test_private_documents_check_parent_access_and_cannot_be_moved_by_url(): void
    {
        $one = $this->agent(); $two = $this->agent(); $client = $this->client($one); $other = $this->client($two);
        $this->actingAs($one)->post('/commerciale/clients/'.$client.'/documents', ['name' => 'Brief client', 'document' => UploadedFile::fake()->create('brief.pdf', 10, 'application/pdf')])->assertRedirect();
        $document = DB::table('com_documents')->first();
        Storage::disk('commercial_private')->assertExists($document->path);
        $this->get('/commerciale/clients/'.$client.'/documents/'.$document->id)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/commerciale/clients/'.$client)->assertOk()->assertSee('Brief client')->assertDontSee($document->path);
        $this->actingAs($two)->get('/commerciale/clients/'.$client.'/documents/'.$document->id)->assertNotFound();
        $this->get('/commerciale/clients/'.$other.'/documents/'.$document->id)->assertNotFound();
        $this->actingAs($one)->post('/commerciale/clients/'.$client.'/documents', ['name' => 'Script interdit', 'document' => UploadedFile::fake()->create('script.html', 1, 'text/html')])->assertSessionHasErrors('document');
    }

    public function test_archive_hides_and_locks_records_but_keeps_history_and_allows_restore(): void
    {
        $agent = $this->agent(); $client = $this->client($agent, 'Client à archiver');
        $this->actingAs($agent)->patch('/commerciale/clients/'.$client.'/archiver', ['archived' => 1])->assertForbidden();
        $manager = $this->agent('commercial-responsable');
        $this->actingAs($manager)->patch('/commerciale/clients/'.$client.'/archiver', ['archived' => 1])->assertRedirect();
        $this->actingAs($agent)->get('/commerciale/clients')->assertOk()->assertDontSee('Client à archiver');
        $this->get('/commerciale/clients?archived=1')->assertOk()->assertSee('Client à archiver');
        $this->get('/commerciale/clients/'.$client)->assertOk()->assertSee('Dossier archivé');
        $this->put('/commerciale/clients/'.$client, ['name' => 'Modifié', 'employee_id' => $this->employee($agent), 'status' => 'active'])->assertForbidden();
        $this->actingAs($manager)->patch('/commerciale/clients/'.$client.'/archiver', ['archived' => 0])->assertRedirect();
        $this->actingAs($agent)->get('/commerciale/clients')->assertOk()->assertSee('Client à archiver');
        $this->assertDatabaseHas('chamber_audits', ['module' => 'clients', 'record_id' => $client, 'action' => 'restore']);
    }
}
