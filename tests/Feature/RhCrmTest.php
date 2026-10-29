<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Chamber;
use App\Models\RhAccessAccount;
use App\Models\User;
use Database\Seeders\RhSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RhCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RhSeeder::class);
        Chamber::where('slug', 'rh')->update(['is_open' => true]);
        Storage::fake('rh_private');
    }

    private function user(string $profile = 'rh-responsable', string $legacy = 'author'): User
    {
        $user = User::factory()->create(['role' => $legacy, 'email_verified_at' => now()]);
        $account = RhAccessAccount::create(['user_id' => $user->id, 'is_active' => true]);
        $account->assignRole($profile);
        return $user;
    }

    private function record(string $module, array $data): int
    {
        return DB::table('rh_'.$module)->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
    }

    private function employee(?User $user = null, string $name = 'Agent Alpha'): int
    {
        return $this->record('employees', ['name' => $name, 'registration' => 'EMP-'.DB::table('rh_employees')->count(), 'user_id' => $user?->id, 'department_id' => DB::table('rh_departments')->value('id'), 'position' => 'Commercial', 'profession' => 'sales', 'hired_on' => '2025-01-01', 'status' => 'active']);
    }

    private function leave(int $employee, string $status = 'pending'): array
    {
        return ['employee_id' => $employee, 'type' => 'annual', 'starts_on' => '2026-11-02', 'ends_on' => '2026-11-06', 'days' => 5, 'status' => $status, 'notes' => 'Congé test'];
    }

    public function test_all_24_entries_and_management_forms_render(): void
    {
        $this->actingAs($this->user('rh-responsable', 'superadmin'));
        $this->assertCount(22, config('rh.modules'));
        $this->get('/rh')->assertOk()->assertSee('Tous les employés');
        foreach (array_keys(config('rh.modules')) as $module) {
            $this->get('/rh/'.$module)->assertOk();
            $this->get('/rh/'.$module.'/nouveau')->assertOk();
        }
        $this->get('/rh/rapports')->assertOk();
        $this->get('/rh/historique')->assertOk();
        $this->get('/rh/acces')->assertOk();
        $this->get('/chambres/rh')->assertOk()->assertSee('Ouvrir la Chambre RH');
    }

    public function test_general_admin_has_no_implicit_hr_access_and_chamber_closure_blocks_every_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin)->get('/rh')->assertForbidden();
        $manager = $this->user();
        $this->actingAs($manager)->get('/rh')->assertOk();
        Chamber::where('slug', 'rh')->update(['is_open' => false]);
        $this->get('/rh')->assertForbidden();
        $this->get('/rh/payroll/export')->assertForbidden();
        $this->post('/rh/leaves', [])->assertForbidden();
        $this->get('/rh/documents/1/piece')->assertForbidden();
    }

    public function test_employee_cannot_read_other_records_or_private_documents_even_by_id_or_filter(): void
    {
        $user = $this->user('rh-employe');
        $own = $this->employee($user, 'Agent Personnel');
        $other = $this->employee(null, 'Agent Secret');
        $mine = $this->record('documents', ['employee_id' => $own, 'name' => 'Attestation visible', 'date' => '2026-01-01', 'visibility' => 'employee']);
        $secret = $this->record('documents', ['employee_id' => $other, 'name' => 'Document confidentiel collègue', 'date' => '2026-01-01', 'visibility' => 'employee']);
        $private = $this->record('documents', ['employee_id' => $own, 'name' => 'Note RH privée', 'date' => '2026-01-01', 'visibility' => 'hr']);
        $this->actingAs($user)->get('/rh/documents')->assertOk()->assertSee('Attestation visible')->assertDontSee('Document confidentiel collègue')->assertDontSee('Note RH privée')->assertDontSee('Agent Secret');
        $this->get('/rh/documents/'.$mine)->assertOk();
        $this->get('/rh/documents/'.$secret)->assertNotFound();
        $this->get('/rh/documents/'.$private)->assertNotFound();
        $this->get('/rh/documents/'.$secret.'/piece')->assertNotFound();
        $this->get('/rh/documents?employee_id='.$other)->assertOk()->assertDontSee('Document confidentiel collègue');
        foreach (['employees', 'files', 'payroll', 'discipline', 'applications'] as $module) $this->get('/rh/'.$module)->assertForbidden();
        $this->get('/rh/documents/export')->assertForbidden();
        $this->get('/rh/historique')->assertForbidden();
        $this->post('/rh/acces', [])->assertForbidden();
    }

    public function test_self_leave_submission_cannot_approve_or_impersonate(): void
    {
        $user = $this->user('rh-employe');
        $own = $this->employee($user);
        $other = $this->employee();
        $this->actingAs($user)->post('/rh/leaves', $this->leave($other))->assertForbidden();
        $this->post('/rh/leaves', $this->leave($own, 'approved'))->assertForbidden();
        $this->post('/rh/leaves', $this->leave($own))->assertRedirect();
        $id = DB::table('rh_leaves')->value('id');
        $this->get('/rh/leaves/'.$id.'/modifier')->assertOk();
        $this->put('/rh/leaves/'.$id, $this->leave($own, 'approved'))->assertForbidden();
        $this->assertDatabaseHas('rh_leaves', ['id' => $id, 'status' => 'pending']);
    }

    public function test_leave_approval_is_idempotent_refunds_on_cancel_and_rejects_overspending(): void
    {
        $employee = $this->employee();
        $balance = $this->record('balances', ['employee_id' => $employee, 'year' => 2026, 'type' => 'annual', 'entitlement' => 8]);
        $this->actingAs($this->user())->post('/rh/leaves', $this->leave($employee, 'approved'))->assertRedirect();
        $id = DB::table('rh_leaves')->value('id');
        $this->assertDatabaseHas('rh_balances', ['id' => $balance, 'used' => 5]);
        $this->put('/rh/leaves/'.$id, $this->leave($employee, 'approved'))->assertRedirect();
        $this->assertDatabaseHas('rh_balances', ['id' => $balance, 'used' => 5]);
        $this->post('/rh/leaves', array_replace($this->leave($employee, 'approved'), ['starts_on' => '2026-12-01', 'ends_on' => '2026-12-05']))->assertSessionHasErrors('days');
        $this->post('/rh/leaves', $this->leave($employee, 'approved'))->assertSessionHasErrors('starts_on');
        $this->put('/rh/leaves/'.$id, $this->leave($employee, 'cancelled'))->assertRedirect();
        $this->assertDatabaseHas('rh_balances', ['id' => $balance, 'used' => 0]);
        $this->put('/rh/leaves/'.$id, $this->leave($employee, 'cancelled'))->assertSessionHasErrors('status');
        $this->assertDatabaseHas('rh_balances', ['id' => $balance, 'used' => 0]);
        $this->assertDatabaseHas('chamber_audits', ['chamber_slug' => 'rh', 'module' => 'balances', 'action' => 'leave_balance']);
    }

    public function test_manager_cannot_decide_own_leave(): void
    {
        $manager = $this->user();
        $employee = $this->employee($manager);
        $this->actingAs($manager)->post('/rh/leaves', $this->leave($employee, 'approved'))->assertSessionHasErrors('status');
    }

    public function test_pointage_computes_times_and_rejects_mismatched_schedule(): void
    {
        $employee = $this->employee();
        $other = $this->employee();
        $schedule = $this->record('schedules', ['employee_id' => $employee, 'name' => 'Vacation du matin', 'starts_at' => '2026-10-29 08:00:00', 'ends_at' => '2026-10-29 17:00:00', 'break_minutes' => 60]);
        $data = ['employee_id' => $employee, 'schedule_id' => $schedule, 'arrived_at' => '2026-10-29T08:15', 'left_at' => '2026-10-29T18:00', 'break_minutes' => 60];
        $this->actingAs($this->user())->post('/rh/attendance', array_replace($data, ['employee_id' => $other]))->assertSessionHasErrors('schedule_id');
        $this->post('/rh/attendance', $data)->assertRedirect();
        $this->assertDatabaseHas('rh_attendance', ['schedule_id' => $schedule, 'late_minutes' => 15, 'worked_minutes' => 525, 'overtime_minutes' => 45]);
        $this->post('/rh/attendance', $data)->assertSessionHasErrors('schedule_id');
        $id = DB::table('rh_attendance')->value('id');
        $this->get('/rh/attendance/'.$id)->assertOk()->assertSee('525');
    }

    public function test_finance_sees_only_validated_payroll_and_cannot_edit_or_read_hr_dossiers(): void
    {
        $employee = $this->employee();
        $draft = $this->record('payroll', ['employee_id' => $employee, 'name' => 'Prime secrète brouillon', 'period' => '2026-10', 'type' => 'bonus', 'amount' => 12000, 'status' => 'draft']);
        $approved = $this->record('payroll', ['employee_id' => $employee, 'name' => 'Prime validée', 'period' => '2026-10', 'type' => 'bonus', 'amount' => 15000, 'status' => 'approved']);
        $this->actingAs($this->user('rh-finance'))->get('/rh/payroll')->assertOk()->assertSee('Prime validée')->assertDontSee('Prime secrète brouillon');
        $this->get('/rh/payroll/'.$draft)->assertNotFound();
        $this->post('/rh/payroll', [])->assertForbidden();
        $this->put('/rh/payroll/'.$approved, [])->assertForbidden();
        $this->get('/rh/employees')->assertForbidden();
        $this->get('/rh/discipline')->assertForbidden();
        $csv = $this->get('/rh/payroll/export')->assertOk()->streamedContent();
        $this->assertStringContainsString('Prime validée', $csv);
        $this->assertStringNotContainsString('Prime secrète brouillon', $csv);
        $this->post('/rh/paie/'.$draft.'/transmettre')->assertNotFound();
        $this->post('/rh/paie/'.$approved.'/transmettre')->assertRedirect();
        $this->assertDatabaseHas('rh_payroll', ['id' => $approved, 'status' => 'transmitted']);
        $this->actingAs($this->user())->put('/rh/payroll/'.$approved, ['employee_id' => $employee, 'name' => 'Modifié', 'period' => '2026-10', 'type' => 'bonus', 'amount' => 1, 'status' => 'approved'])->assertSessionHasErrors('status');
    }

    public function test_assistant_cannot_read_sensitive_modules_or_grant_rights(): void
    {
        $assistant = $this->user('rh-assistant');
        $this->actingAs($assistant)->get('/rh/employees')->assertOk();
        foreach (['payroll', 'discipline'] as $module) {
            $this->get('/rh/'.$module)->assertForbidden();
            $this->get('/rh/'.$module.'/export')->assertForbidden();
            $this->post('/rh/'.$module, [])->assertForbidden();
        }
        $this->get('/rh/rapports')->assertOk()->assertDontSee('Primes et éléments de paie');
        $this->get('/rh/acces')->assertForbidden();
        $this->post('/rh/acces', ['user_id' => $assistant->id, 'role' => 'rh-responsable', 'is_active' => 1])->assertForbidden();
    }

    public function test_private_upload_and_download_permissions(): void
    {
        $user = $this->user('rh-employe');
        $employee = $this->employee($user);
        $manager = $this->user();
        $this->actingAs($manager)->post('/rh/documents', ['employee_id' => $employee, 'name' => 'Attestation', 'date' => '2026-10-29', 'visibility' => 'employee', 'attachment' => UploadedFile::fake()->create('attestation.pdf', 10, 'application/pdf')])->assertRedirect();
        $record = DB::table('rh_documents')->first();
        Storage::disk('rh_private')->assertExists($record->attachment);
        $this->actingAs($user)->get('/rh/documents/'.$record->id.'/piece')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $other = $this->user('rh-employe');
        $this->employee($other);
        $this->actingAs($other)->get('/rh/documents/'.$record->id.'/piece')->assertNotFound();
        $this->actingAs($manager)->post('/rh/documents', ['employee_id' => $employee, 'name' => 'Script', 'date' => '2026-10-29', 'visibility' => 'hr', 'attachment' => UploadedFile::fake()->create('script.html', 1, 'text/html')])->assertSessionHasErrors('attachment');
    }

    public function test_hiring_creates_one_employee_and_onboarding_task(): void
    {
        $department = DB::table('rh_departments')->value('id');
        $job = $this->record('jobs', ['name' => 'Développeur', 'department_id' => $department, 'status' => 'open', 'description' => 'Équipe numérique']);
        $candidate = $this->record('applications', ['job_id' => $job, 'name' => 'Recrue Alpha', 'email' => 'recrue@example.com', 'status' => 'selected']);
        $this->actingAs($this->user())->post('/rh/candidatures/'.$candidate.'/integrer', ['registration' => 'EMP-NEW', 'hired_on' => '2026-10-29'])->assertRedirect();
        $id = DB::table('rh_applications')->where('id', $candidate)->value('employee_id');
        $this->assertDatabaseHas('rh_employees', ['id' => $id, 'registration' => 'EMP-NEW', 'status' => 'onboarding']);
        $this->assertDatabaseHas('rh_onboarding', ['employee_id' => $id, 'status' => 'planned']);
        $this->post('/rh/candidatures/'.$candidate.'/integrer', ['registration' => 'EMP-NEW-2', 'hired_on' => '2026-10-29'])->assertSessionHasErrors('candidate');
        $this->assertSame(1, DB::table('rh_employees')->count());
    }

    public function test_departure_archives_employee_and_revokes_hr_access(): void
    {
        $user = $this->user('rh-employe');
        $employee = $this->employee($user);
        $contract = $this->record('contracts', ['employee_id' => $employee, 'name' => 'CDI Alpha', 'type' => 'permanent', 'starts_on' => '2025-01-01', 'status' => 'active']);
        $this->actingAs($this->user())->post('/rh/departures', ['employee_id' => $employee, 'date' => '2026-10-01', 'type' => 'resignation', 'status' => 'done'])->assertRedirect();
        $this->assertDatabaseHas('rh_employees', ['id' => $employee, 'status' => 'departed']);
        $this->assertDatabaseHas('rh_access_accounts', ['user_id' => $user->id, 'is_active' => false]);
        $this->assertDatabaseHas('rh_contracts', ['id' => $contract, 'status' => 'ended', 'ends_on' => '2026-10-01']);
        $this->actingAs($user)->get('/rh')->assertForbidden();
        $this->assertDatabaseHas('chamber_audits', ['chamber_slug' => 'rh', 'module' => 'employees', 'action' => 'departure']);
    }

    public function test_superadmin_grants_spatie_access_and_seed_is_repeatable(): void
    {
        $root = $this->user('rh-responsable', 'superadmin');
        $target = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($root)->post('/rh/acces', ['user_id' => $target->id, 'role' => 'rh-assistant', 'is_active' => 1])->assertRedirect();
        $this->assertTrue(RhAccessAccount::where('user_id', $target->id)->first()->hasRole('rh-assistant'));
        $this->actingAs($target)->get('/rh/employees')->assertOk();
        $this->seed(RhSeeder::class);
        $this->assertSame(Chamber::count(), DB::table('rh_departments')->count());
        $this->assertSame(1, Article::where('slug', 'chambre-rh-eunital')->count());
    }
}
