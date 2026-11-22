<?php

namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DevelopmentCrmTest extends TestCase
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

    public function test_development_crm_pages_render_and_project_crud_is_audited(): void
    {
        $superadmin = $this->user('superadmin');
        Chamber::where('slug', 'developpement')->update(['is_open' => true]);
        $this->actingAs($superadmin);

        $this->get(route('development.dashboard'))->assertOk();
        foreach (array_keys(config('development.modules')) as $module) {
            $this->get(route('development.index', $module))->assertOk();
            $this->get(route('development.create', $module))->assertOk();
        }

        $this->post(route('development.store', 'projects'), [
            'name' => 'Plateforme client',
            'product_type' => 'platform',
            'status' => 'active',
            'priority' => 'high',
            'description' => 'Développement complet',
        ])->assertRedirect();

        $project = DB::table('dev_projects')->first();
        $this->assertNotNull($project);
        $this->put(route('development.update', ['projects', $project->id]), [
            'name' => 'Plateforme client v2',
            'product_type' => 'platform',
            'status' => 'qa',
            'priority' => 'critical',
        ])->assertRedirect();
        $this->get(route('development.show', ['projects', $project->id]))->assertOk()->assertSee('Plateforme client v2');
        $this->assertDatabaseCount('chamber_audits', 2);
    }

    public function test_member_access_is_revocable_and_closure_blocks_writes(): void
    {
        Chamber::where('slug', 'developpement')->update(['is_open' => true]);
        $member = $this->user('author');
        DB::table('dev_team')->insert([
            'user_id' => $member->id,
            'role' => 'developer',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($member)->get(route('development.dashboard'))->assertOk();

        DB::table('dev_team')->where('user_id', $member->id)->update(['status' => 'inactive']);
        $this->get(route('development.dashboard'))->assertForbidden();

        Chamber::where('slug', 'developpement')->update(['is_open' => false]);
        $this->actingAs($this->user('superadmin'))->get(route('development.dashboard'))->assertForbidden();
    }

    public function test_task_rejects_sprint_from_another_project(): void
    {
        Chamber::where('slug', 'developpement')->update(['is_open' => true]);
        $this->actingAs($this->user('superadmin'));

        $one = DB::table('dev_projects')->insertGetId([
            'name' => 'Projet A', 'product_type' => 'web', 'status' => 'active', 'priority' => 'medium',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $two = DB::table('dev_projects')->insertGetId([
            'name' => 'Projet B', 'product_type' => 'mobile', 'status' => 'active', 'priority' => 'medium',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $sprint = DB::table('dev_sprints')->insertGetId([
            'name' => 'Sprint B', 'project_id' => $two, 'goal' => 'Livrer', 'status' => 'planned',
            'start_date' => today()->format('Y-m-d'), 'end_date' => today()->addWeek()->format('Y-m-d'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->post(route('development.store', 'tasks'), [
            'name' => 'Tâche incohérente',
            'project_id' => $one,
            'sprint_id' => $sprint,
            'kind' => 'feature',
            'priority' => 'medium',
            'status' => 'todo',
        ])->assertSessionHasErrors('sprint_id');
    }
}
