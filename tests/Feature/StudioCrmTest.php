<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Chamber;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Database\Seeders\StudioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudioCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChamberSeeder::class);
        Chamber::where('slug', 'studio')->update(['is_open' => true]);
    }

    private function user(string $role = 'admin'): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }

    private function record(string $module, array $data): int
    {
        return DB::table('std_'.$module)->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
    }

    private function project(): int
    {
        $client = $this->record('clients', ['name' => 'Client Studio', 'status' => 'active']);

        return $this->record('projects', ['name' => 'Film Alpha', 'client_id' => $client, 'format' => 'video', 'status' => 'preproduction', 'budget' => 500000, 'description' => 'Présentation de la marque']);
    }

    private function location(): int
    {
        return $this->record('locations', ['name' => 'Plateau test', 'type' => 'studio', 'address' => 'Adresse test', 'status' => 'active']);
    }

    private function equipment(): int
    {
        return $this->record('equipment', ['name' => 'Caméra test', 'inventory_code' => 'CAM-'.DB::table('std_equipment')->count(), 'type' => 'camera', 'status' => 'available']);
    }

    private function sessionData(int $project, int $location, string $start = '10:00', string $end = '12:00'): array
    {
        $day = today(config('studio.timezone'))->addDay()->format('Y-m-d');

        return ['name' => 'Tournage Alpha', 'project_id' => $project, 'location_id' => $location, 'type' => 'video', 'status' => 'confirmed', 'starts_at' => $day.'T'.$start, 'ends_at' => $day.'T'.$end];
    }

    private function seedSession(array $data): int
    {
        $data['starts_at'] = str_replace('T', ' ', $data['starts_at']).':00';
        $data['ends_at'] = str_replace('T', ' ', $data['ends_at']).':00';

        return $this->record('sessions', $data);
    }

    public function test_access_and_audit_are_isolated_from_other_chambers(): void
    {
        $this->get(route('studio.dashboard'))->assertRedirect(route('login'));
        $user = $this->user('author');
        DB::table('eng_team')->insert(['user_id' => $user->id, 'role' => 'engineer', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($user)->get(route('studio.dashboard'))->assertForbidden();
        $this->record('team', ['user_id' => $user->id, 'role' => 'technician', 'status' => 'active']);
        $this->get(route('studio.dashboard'))->assertOk();
        $this->get(route('marketing.dashboard'))->assertForbidden();
        $this->post(route('studio.store', 'clients'), ['name' => 'Studio private name', 'status' => 'active'])->assertRedirect();
        $this->actingAs($this->user())->post(route('engineering.store', 'clients'), ['name' => 'Engineering private name', 'status' => 'active'])->assertRedirect();
        $this->get(route('studio.audit'))->assertOk()->assertDontSee('Engineering private name');
        $this->get(route('engineering.audit'))->assertOk()->assertDontSee('Studio private name');
        DB::table('std_team')->where('user_id', $user->id)->update(['status' => 'inactive']);
        $this->actingAs($user)->get(route('studio.export', 'clients'))->assertForbidden();
    }

    public function test_only_superadmin_controls_opening_and_closure_blocks_all_crm_operations(): void
    {
        $this->actingAs($this->user())->patch(route('chambers.status', 'studio'), ['is_open' => 0])->assertForbidden();
        $this->actingAs($this->user('superadmin'))->patch(route('chambers.status', 'studio'), ['is_open' => 0])->assertRedirect();
        foreach (['dashboard', 'reports', 'planning', 'audit'] as $route) {
            $this->get(route('studio.'.$route))->assertForbidden();
        }
        $this->get(route('studio.export', 'clients'))->assertForbidden();
        $this->post(route('studio.store', 'clients'), ['name' => 'Blocked', 'status' => 'active'])->assertForbidden();
        $this->get(route('chambers.show', 'studio'))->assertOk()->assertSee('CRM Studio est prêt');
        $this->get(route('engineering.dashboard'))->assertOk();
        $this->patch(route('chambers.status', 'studio'), ['is_open' => 1])->assertRedirect();
        $this->get(route('studio.dashboard'))->assertOk();
        $this->assertDatabaseCount('std_clients', 0);
        $this->assertDatabaseHas('chamber_audits', ['chamber_slug' => 'studio', 'action' => 'close']);
    }

    public function test_sessions_validate_times_and_prevent_location_overlap_but_allow_adjacent_sessions(): void
    {
        $project = $this->project();
        $location = $this->location();
        $this->actingAs($this->user());
        $data = $this->sessionData($project, $location);
        $this->post(route('studio.store', 'sessions'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $id = DB::table('std_sessions')->value('id');
        $this->get(route('studio.edit', ['sessions', $id]))->assertOk()->assertSee($data['starts_at'], false);
        $this->post(route('studio.store', 'sessions'), $this->sessionData($project, $location, '11:00', '13:00'))->assertSessionHasErrors('starts_at');
        $this->post(route('studio.store', 'sessions'), $this->sessionData($project, $location, '12:00', '13:00'))->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('studio.store', 'sessions'), $this->sessionData($project, $location, '14:00', '13:00'))->assertSessionHasErrors('ends_at');
        $this->assertDatabaseCount('std_sessions', 2);
    }

    public function test_equipment_reservations_cannot_overlap_and_rescheduling_rechecks_resources(): void
    {
        $project = $this->project();
        $equipment = $this->equipment();
        $first = $this->seedSession($this->sessionData($project, $this->location()));
        $secondData = $this->sessionData($project, $this->location(), '14:00', '16:00');
        $second = $this->seedSession($secondData);
        $third = $this->seedSession($this->sessionData($project, $this->location(), '11:00', '13:00'));
        $this->actingAs($this->user());
        foreach ([$first, $second] as $session) {
            $this->post(route('studio.store', 'reservations'), ['name' => 'Caméra', 'session_id' => $session, 'equipment_id' => $equipment, 'status' => 'reserved'])->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->post(route('studio.store', 'reservations'), ['name' => 'Conflit', 'session_id' => $third, 'equipment_id' => $equipment, 'status' => 'reserved'])->assertSessionHasErrors('equipment_id');
        $this->put(route('studio.update', ['sessions', $second]), array_replace($secondData, ['starts_at' => $this->sessionData($project, $secondData['location_id'], '11:00', '13:00')['starts_at']]))->assertSessionHasErrors('starts_at');
        $this->assertDatabaseCount('std_reservations', 2);
    }

    public function test_crew_assignment_overlap_and_membership_are_checked(): void
    {
        $project = $this->project();
        $one = $this->seedSession($this->sessionData($project, $this->location()));
        $two = $this->seedSession($this->sessionData($project, $this->location(), '11:00', '13:00'));
        $technician = $this->user('author');
        $this->record('team', ['user_id' => $technician->id, 'role' => 'technician', 'status' => 'active']);
        $this->actingAs($this->user())->post(route('studio.store', 'assignments'), ['name' => 'Caméra', 'session_id' => $one, 'user_id' => $technician->id, 'role' => 'camera', 'status' => 'assigned'])->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('studio.store', 'assignments'), ['name' => 'Conflit', 'session_id' => $two, 'user_id' => $technician->id, 'role' => 'camera', 'status' => 'assigned'])->assertSessionHasErrors('user_id');
        $outsider = $this->user('author');
        $this->post(route('studio.store', 'assignments'), ['name' => 'Non membre', 'session_id' => $two, 'user_id' => $outsider->id, 'role' => 'sound', 'status' => 'assigned'])->assertSessionHasErrors('user_id');
    }

    public function test_checked_out_equipment_must_be_returned_before_other_checkout_or_session_cancellation(): void
    {
        $project = $this->project();
        $equipment = $this->equipment();
        $data = $this->sessionData($project, $this->location());
        $one = $this->seedSession($data);
        $two = $this->seedSession($this->sessionData($project, $this->location(), '14:00', '16:00'));
        $this->actingAs($this->user());
        $first = ['name' => 'Sortie caméra', 'session_id' => $one, 'equipment_id' => $equipment, 'status' => 'checked_out'];
        $this->post(route('studio.store', 'reservations'), $first)->assertSessionHasNoErrors()->assertRedirect();
        $reservation = DB::table('std_reservations')->value('id');
        $this->post(route('studio.store', 'reservations'), ['name' => 'Autre sortie', 'session_id' => $two, 'equipment_id' => $equipment, 'status' => 'checked_out'])->assertSessionHasErrors('equipment_id');
        $this->put(route('studio.update', ['sessions', $one]), array_replace($data, ['status' => 'cancelled']))->assertSessionHasErrors('status');
        $this->put(route('studio.update', ['reservations', $reservation]), array_replace($first, ['status' => 'cancelled']))->assertSessionHasErrors('status');
        $this->put(route('studio.update', ['reservations', $reservation]), array_replace($first, ['status' => 'returned']))->assertSessionHasNoErrors()->assertRedirect();
        $this->put(route('studio.update', ['sessions', $one]), array_replace($data, ['status' => 'completed']))->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('studio.store', 'reservations'), ['name' => 'Nouvelle sortie', 'session_id' => $two, 'equipment_id' => $equipment, 'status' => 'checked_out'])->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_session_cancellation_releases_reserved_equipment_and_crew_with_audit(): void
    {
        $project = $this->project();
        $equipment = $this->equipment();
        $data = $this->sessionData($project, $this->location());
        $session = $this->seedSession($data);
        $admin = $this->user();
        $reservation = $this->record('reservations', ['name' => 'Réservation', 'session_id' => $session, 'equipment_id' => $equipment, 'status' => 'reserved']);
        $assignment = $this->record('assignments', ['name' => 'Réalisation', 'session_id' => $session, 'user_id' => $admin->id, 'role' => 'director', 'status' => 'assigned']);
        $this->actingAs($admin)->put(route('studio.update', ['sessions', $session]), array_replace($data, ['status' => 'cancelled']))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('std_reservations', ['id' => $reservation, 'status' => 'cancelled']);
        $this->assertDatabaseHas('std_assignments', ['id' => $assignment, 'status' => 'cancelled']);
        $this->assertDatabaseCount('chamber_audits', 3);
    }

    public function test_maintenance_resources_and_wrong_production_links_are_rejected(): void
    {
        $project = $this->project();
        $location = $this->location();
        $equipment = $this->equipment();
        $data = $this->sessionData($project, $location);
        $session = $this->seedSession($data);
        DB::table('std_equipment')->where('id', $equipment)->update(['status' => 'maintenance']);
        $this->actingAs($this->user())->post(route('studio.store', 'reservations'), ['name' => 'Maintenance', 'session_id' => $session, 'equipment_id' => $equipment, 'status' => 'reserved'])->assertSessionHasErrors('equipment_id');
        $this->post(route('studio.store', 'assets'), ['name' => 'Mauvais média', 'project_id' => $this->project(), 'session_id' => $session, 'type' => 'rush', 'version' => '1', 'file_url' => 'https://example.com/rush', 'usage_rights' => 'Client', 'status' => 'draft'])->assertSessionHasErrors('session_id');
        $this->put(route('studio.update', ['locations', $location]), ['name' => 'Lieu fermé', 'type' => 'studio', 'address' => 'Adresse', 'status' => 'unavailable'])->assertSessionHasErrors('status');
    }

    public function test_technicians_cannot_approve_renders_or_manage_access_and_delete_records(): void
    {
        $technician = $this->user('author');
        $this->record('team', ['user_id' => $technician->id, 'role' => 'technician', 'status' => 'active']);
        $project = $this->project();
        $deliverable = ['name' => 'Master Alpha', 'project_id' => $project, 'version' => '1', 'format' => 'master', 'status' => 'accepted', 'document_url' => 'https://example.com/master'];
        $this->actingAs($technician)->post(route('studio.store', 'deliverables'), $deliverable)->assertSessionHasErrors('status');
        $this->post(route('studio.store', 'team'), ['user_id' => $this->user('author')->id, 'role' => 'manager', 'status' => 'active'])->assertForbidden();
        $this->delete(route('studio.destroy', ['projects', $project]))->assertForbidden();
        DB::table('std_team')->where('user_id', $technician->id)->update(['role' => 'manager']);
        $this->post(route('studio.store', 'deliverables'), $deliverable)->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_all_pages_render_and_kpi_links_open_their_filtered_sections(): void
    {
        $this->actingAs($this->user());
        foreach (array_keys(config('studio.modules')) as $module) {
            $this->get(route('studio.index', $module))->assertOk();
            $this->get(route('studio.create', $module))->assertOk();
        }
        $dashboard = $this->get(route('studio.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord Studio')
            ->assertSee('Planning de tournage');
        preg_match_all('/<a class="eng-kpi" href="([^"]+)"/', $dashboard->getContent(), $matches);
        $this->assertGreaterThanOrEqual(8, count($matches[1]));
        foreach (array_slice($matches[1], 0, 8) as $href) {
            $this->get(html_entity_decode($href))->assertOk();
        }
        $this->get(route('studio.planning'))->assertOk();
        $this->get(route('studio.planning', ['month' => 'invalid']))->assertSessionHasErrors('month');
        $this->get(route('studio.reports'))->assertOk();
        $this->get(route('studio.index', 'projects.fields'))->assertNotFound();
        $this->record('clients', ['name' => '=FORMULA', 'status' => 'active']);
        $csv = $this->get(route('studio.export', 'clients'))->assertOk()->streamedContent();
        $this->assertStringContainsString("'=FORMULA", $csv);
    }

    public function test_planning_includes_sessions_crossing_month_boundaries(): void
    {
        $month = today(config('studio.timezone'))->startOfMonth();
        $this->record('sessions', ['name' => 'Séance de nuit', 'project_id' => $this->project(), 'location_id' => $this->location(), 'type' => 'video', 'status' => 'confirmed', 'starts_at' => $month->copy()->subHour()->format('Y-m-d H:i:s'), 'ends_at' => $month->copy()->addHour()->format('Y-m-d H:i:s')]);
        $this->actingAs($this->user())->get(route('studio.planning', ['month' => $month->format('Y-m')]))->assertOk()->assertSee('Séance de nuit');
        $this->get(route('studio.planning', ['month' => $month->copy()->addMonth()->format('Y-m')]))->assertOk()->assertDontSee('Séance de nuit');
    }

    public function test_seeders_publish_one_studio_article_and_preserve_closed_status(): void
    {
        Chamber::where('slug', 'studio')->update(['is_open' => false]);
        $this->seed(StudioSeeder::class);
        $this->seed(StudioSeeder::class);
        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseCount('std_equipment', 0);
        $this->assertFalse(Chamber::where('slug', 'studio')->first()->is_open);
        $article = Article::first();
        $this->assertEquals('studio', $article->category->slug);
        $this->assertEquals(1, $article->isActive);
        $this->assertStringStartsWith('https://images.unsplash.com/', $article->imageUrl());
    }

    public function test_full_production_workflow_from_brief_to_delivery_and_payment(): void
    {
        $admin = $this->user();
        $technician = $this->user('author');
        $this->actingAs($admin);
        $create = function (string $module, array $data): int {
            $this->post(route('studio.store', $module), $data)->assertSessionHasNoErrors()->assertRedirect();
            $id = DB::table('std_'.$module)->max('id');
            $this->get(route('studio.show', [$module, $id]))->assertOk();
            $this->get(route('studio.edit', [$module, $id]))->assertOk();

            return $id;
        };
        $create('team', ['user_id' => $technician->id, 'role' => 'technician', 'status' => 'active']);
        $client = $create('clients', ['name' => 'Client Film', 'status' => 'active']);
        $contact = $create('contacts', ['name' => 'Contact Film', 'client_id' => $client]);
        $projectData = ['name' => 'Film de présentation', 'client_id' => $client, 'owner_id' => $admin->id, 'format' => 'video', 'status' => 'preproduction', 'budget' => 500000, 'description' => 'Présentation de la marque'];
        $project = $create('projects', $projectData);
        $this->put(route('studio.update', ['projects', $project]), array_replace($projectData, ['status' => 'delivered']))->assertSessionHasErrors('status');
        $create('briefs', ['name' => 'Brief Film', 'project_id' => $project, 'contact_id' => $contact, 'version' => '1', 'status' => 'approved', 'objectives' => 'Présenter le service', 'specifications' => 'Vidéo 1080p avec sous-titres', 'usage_rights' => 'Diffusion autorisée par le client']);
        $location = $create('locations', ['name' => 'Plateau Film', 'type' => 'studio', 'address' => 'Adresse du plateau', 'status' => 'active']);
        $equipment = $create('equipment', ['name' => 'Caméra Film', 'inventory_code' => 'CAM-FILM-1', 'type' => 'camera', 'status' => 'available']);
        $sessionData = $this->sessionData($project, $location);
        $session = $create('sessions', $sessionData);
        $reservationData = ['name' => 'Caméra pour Film', 'session_id' => $session, 'equipment_id' => $equipment, 'status' => 'reserved'];
        $reservation = $create('reservations', $reservationData);
        $assignment = $create('assignments', ['name' => 'Prise de vue', 'session_id' => $session, 'user_id' => $technician->id, 'role' => 'camera', 'status' => 'assigned']);
        $this->put(route('studio.update', ['reservations', $reservation]), array_replace($reservationData, ['status' => 'checked_out']))->assertSessionHasNoErrors()->assertRedirect();
        $create('assets', ['name' => 'Rush Film', 'project_id' => $project, 'session_id' => $session, 'type' => 'rush', 'version' => '1', 'file_url' => 'https://example.com/rush.mp4', 'backup_url' => 'https://example.com/backup.mp4', 'usage_rights' => 'Images originales autorisées', 'status' => 'approved']);
        $this->put(route('studio.update', ['reservations', $reservation]), array_replace($reservationData, ['status' => 'returned']))->assertSessionHasNoErrors()->assertRedirect();
        $this->put(route('studio.update', ['sessions', $session]), array_replace($sessionData, ['status' => 'completed']))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('std_assignments', ['id' => $assignment, 'status' => 'done']);
        $task = $create('tasks', ['name' => 'Montage final', 'project_id' => $project, 'assignee_id' => $technician->id, 'status' => 'done', 'priority' => 'high', 'task_type' => 'editing']);
        $create('deliverables', ['name' => 'Master Film', 'project_id' => $project, 'task_id' => $task, 'owner_id' => $technician->id, 'status' => 'accepted', 'version' => '1', 'format' => 'master', 'document_url' => 'https://example.com/master.mp4']);
        $this->put(route('studio.update', ['projects', $project]), array_replace($projectData, ['status' => 'delivered']))->assertSessionHasNoErrors()->assertRedirect();
        $quote = $create('quotes', ['reference' => 'ST-DEV-001', 'client_id' => $client, 'project_id' => $project, 'amount' => 500000, 'status' => 'accepted']);
        $this->get(route('studio.create', ['module' => 'invoices', 'quote_id' => $quote]))->assertOk()->assertSee('500000');
        $invoice = $create('invoices', ['reference' => 'ST-FAC-001', 'client_id' => $client, 'project_id' => $project, 'quote_id' => $quote, 'amount' => 500000, 'status' => 'issued']);
        $create('payments', ['name' => 'Règlement Film', 'invoice_id' => $invoice, 'amount' => 500000, 'payment_date' => today()->format('Y-m-d'), 'method' => 'bank']);
        $this->post(route('studio.store', 'payments'), ['name' => 'Dépassement', 'invoice_id' => $invoice, 'amount' => 1, 'payment_date' => today()->format('Y-m-d'), 'method' => 'cash'])->assertSessionHasErrors('amount');
        $create('expenses', ['name' => 'Transport', 'project_id' => $project, 'amount' => 50000, 'spent_on' => today()->format('Y-m-d'), 'category' => 'travel']);
        $this->get(route('studio.reports'))->assertOk()->assertSee('450 000,00')->assertSee('1/1');
        $this->get(route('studio.index', ['module' => 'invoices', 'unpaid' => 1]))->assertOk()->assertDontSee('ST-FAC-001');
        $this->get(route('studio.index', ['module' => 'deliverables', 'project_id' => $project]))->assertOk()->assertSee('Master Film');
        $this->delete(route('studio.destroy', ['projects', $project]))->assertSessionHasErrors('delete');
    }
}
