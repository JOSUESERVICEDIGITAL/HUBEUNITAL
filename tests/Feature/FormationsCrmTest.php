<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormationsCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChamberSeeder::class);
        Chamber::where('slug', 'formation')->update(['is_open' => true]);
    }

    private function user(string $role = 'superadmin'): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }

    public function test_training_content_supports_private_file_uploads_and_external_links(): void
    {
        Storage::fake('formations_private');
        $this->actingAs($this->user());
        $program = DB::table('frm_programs')->insertGetId([
            'name' => 'Formation test', 'format' => 'bootcamp', 'status' => 'active',
            'duration_hours' => 20, 'price' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $contentData = [
            'name' => 'Support PDF', 'program_id' => $program, 'kind' => 'document',
            'order' => 1, 'status' => 'draft', 'duration_minutes' => 30,
        ];

        $this->get(route('formations.create', 'content'))->assertOk()->assertSee('content_file');
        $this->post(route('formations.store', 'content'), array_merge($contentData, [
            'content_file' => UploadedFile::fake()->create('cours.pdf', 12, 'application/pdf'),
        ]))->assertRedirect();

        $content = DB::table('frm_content')->where('name', 'Support PDF')->first();
        $this->assertStringStartsWith('content/', $content->document_url);
        Storage::disk('formations_private')->assertExists($content->document_url);
        $this->get(route('formations.content.download', $content->id))->assertOk();

        $this->put(route('formations.update', ['content', $content->id]), array_merge($contentData, [
            'document_url' => '',
        ]))->assertRedirect();
        $this->assertSame($content->document_url, DB::table('frm_content')->find($content->id)->document_url);
        Storage::disk('formations_private')->assertExists($content->document_url);

        $this->put(route('formations.update', ['content', $content->id]), array_merge($contentData, [
            'content_file' => UploadedFile::fake()->create('cours-modifie.pdf', 14, 'application/pdf'),
        ]))->assertRedirect();
        $updatedPath = DB::table('frm_content')->find($content->id)->document_url;
        $this->assertNotSame($content->document_url, $updatedPath);
        Storage::disk('formations_private')->assertMissing($content->document_url);
        Storage::disk('formations_private')->assertExists($updatedPath);

        $this->post(route('formations.store', 'content'), array_merge($contentData, [
            'name' => 'Lien externe',
            'document_url' => 'https://example.com/cours',
        ]))->assertRedirect();
        $this->assertSame('https://example.com/cours', DB::table('frm_content')->where('name', 'Lien externe')->value('document_url'));

        $this->post(route('formations.store', 'content'), array_merge($contentData, [
            'name' => 'Fichier refusé',
            'content_file' => UploadedFile::fake()->create('script.html', 1, 'text/html'),
        ]))->assertSessionHasErrors('content_file');
    }

    public function test_all_pages_render_and_full_training_lifecycle_is_audited(): void
    {
        $this->actingAs($this->user());
        $this->get(route('formations.dashboard'))->assertOk();
        foreach (array_keys(config('formations.modules')) as $module) {
            $this->get(route('formations.index', $module))->assertOk();
            $this->get(route('formations.create', $module))->assertOk();
        }

        $this->post(route('formations.store', 'programs'), [
            'name' => 'Développement web',
            'format' => 'bootcamp',
            'level' => 'beginner',
            'status' => 'active',
            'duration_hours' => 480,
            'price' => 150000,
        ])->assertRedirect();
        $program = DB::table('frm_programs')->first();

        $this->post(route('formations.store', 'cohorts'), [
            'name' => 'Promotion 2026',
            'program_id' => $program->id,
            'status' => 'active',
            'starts_on' => today()->format('Y-m-d'),
            'ends_on' => today()->addMonths(6)->format('Y-m-d'),
            'location' => 'Ziniaré',
            'capacity' => 30,
        ])->assertRedirect();
        $cohort = DB::table('frm_cohorts')->first();

        $this->post(route('formations.store', 'learners'), [
            'name' => 'Awa Traoré',
            'phone' => '70000000',
            'city' => 'Ziniaré',
            'status' => 'active',
        ])->assertRedirect();
        $learner = DB::table('frm_learners')->first();

        $this->post(route('formations.store', 'enrollments'), [
            'learner_id' => $learner->id,
            'cohort_id' => $cohort->id,
            'status' => 'completed',
            'enrolled_on' => today()->format('Y-m-d'),
            'fees_amount' => 150000,
            'fees_paid' => 150000,
            'attendance_rate' => 95,
            'average_score' => 88,
        ])->assertRedirect();

        $this->post(route('formations.store', 'certificates'), [
            'reference' => 'FRM-2026-001',
            'learner_id' => $learner->id,
            'cohort_id' => $cohort->id,
            'status' => 'issued',
            'issued_on' => today()->format('Y-m-d'),
        ])->assertRedirect();

        $this->get(route('formations.reports'))->assertOk()->assertSee('Promotion 2026');
        $this->assertDatabaseCount('frm_certificates', 1);
        $this->assertDatabaseCount('chamber_audits', 5);
    }

    public function test_capacity_attendance_and_certificate_rules_are_enforced(): void
    {
        $this->actingAs($this->user());
        $program = DB::table('frm_programs')->insertGetId([
            'name' => 'Dataviz', 'format' => 'flex', 'status' => 'active',
            'duration_hours' => 40, 'price' => 50000, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $cohort = DB::table('frm_cohorts')->insertGetId([
            'name' => 'Petite promotion', 'program_id' => $program, 'status' => 'active',
            'starts_on' => today()->format('Y-m-d'), 'capacity' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $learnerOne = DB::table('frm_learners')->insertGetId([
            'name' => 'A', 'phone' => '1', 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $learnerTwo = DB::table('frm_learners')->insertGetId([
            'name' => 'B', 'phone' => '2', 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('frm_enrollments')->insert([
            'learner_id' => $learnerOne, 'cohort_id' => $cohort, 'status' => 'active',
            'enrolled_on' => today()->format('Y-m-d'), 'fees_amount' => 50000, 'fees_paid' => 50000,
            'attendance_rate' => 100, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->post(route('formations.store', 'enrollments'), [
            'learner_id' => $learnerTwo, 'cohort_id' => $cohort, 'status' => 'active',
            'enrolled_on' => today()->format('Y-m-d'), 'fees_amount' => 50000, 'fees_paid' => 50000,
            'attendance_rate' => 100,
        ])->assertSessionHasErrors('cohort_id');

        $this->post(route('formations.store', 'certificates'), [
            'reference' => 'FRM-BLOCKED', 'learner_id' => $learnerOne, 'cohort_id' => $cohort,
            'status' => 'issued', 'issued_on' => today()->format('Y-m-d'),
        ])->assertSessionHasErrors('status');
    }

    public function test_closure_and_member_access_are_enforced(): void
    {
        $member = $this->user('author');
        DB::table('frm_team')->insert([
            'user_id' => $member->id, 'role' => 'trainer', 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($member)->get(route('formations.dashboard'))->assertOk();
        DB::table('frm_team')->where('user_id', $member->id)->update(['status' => 'inactive']);
        $this->get(route('formations.dashboard'))->assertForbidden();
        Chamber::where('slug', 'formation')->update(['is_open' => false]);
        $this->actingAs($this->user())->get(route('formations.dashboard'))->assertForbidden();
    }
}
