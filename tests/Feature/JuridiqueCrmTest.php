<?php
namespace Tests\Feature;

use App\Models\Chamber;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JuridiqueCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChamberSeeder::class);
        Chamber::where('slug', 'juridique')->update(['is_open' => true]);
    }

    private function user(string $role = 'admin'): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }

    private function dossier(): array
    {
        return ['name' => 'Contrat Alpha', 'scope' => 'internal', 'status' => 'draft', 'requires_professional' => 'no', 'type' => 'nda', 'due_date' => '2020-01-01'];
    }

    public function test_access_requires_membership_and_open_chamber(): void
    {
        $member = $this->user('author');
        $this->actingAs($member)->get('/juridique')->assertForbidden();
        DB::table('jur_team')->insert(['user_id' => $member->id, 'position' => 'jurist', 'status' => 'active']);
        $this->get('/juridique')->assertOk();
        $this->post('/juridique/team', ['user_id' => $member->id, 'position' => 'manager', 'status' => 'active'])->assertForbidden();
        Chamber::where('slug', 'juridique')->update(['is_open' => false]);
        $this->get('/juridique/contracts/export')->assertForbidden();
        $this->post('/juridique/contracts', $this->dossier())->assertForbidden();
    }

    public function test_dossier_validation_audit_and_dependency_protection(): void
    {
        $this->actingAs($this->user());
        $this->post('/juridique/contracts', array_replace($this->dossier(), ['scope' => 'client']))->assertSessionHasErrors('client_id');
        $this->post('/juridique/contracts', array_replace($this->dossier(), ['requires_professional' => 'yes']))->assertSessionHasErrors('partner_id');
        $this->post('/juridique/contracts', $this->dossier())->assertRedirect();
        $id = DB::table('jur_contracts')->value('id');
        $this->assertDatabaseHas('chamber_audits', ['chamber_slug' => 'juridique', 'record_id' => $id, 'action' => 'create']);
        $this->put('/juridique/contracts/'.$id, array_replace($this->dossier(), ['status' => 'done']))->assertRedirect();
        $this->assertDatabaseHas('jur_contracts', ['id' => $id, 'status' => 'done']);
        $this->get('/juridique/contracts/'.$id)->assertOk();
        $this->get('/juridique/contracts/export')->assertOk();
        $this->get('/juridique/rapports')->assertOk();
        $this->get('/juridique/historique')->assertOk();
        $this->get('/juridique/unknown')->assertNotFound();
    }

    public function test_all_modules_render_and_records_preserve_links(): void
    {
        $this->actingAs($this->user());
        foreach (array_keys(config('juridique.modules')) as $module) {
            $this->get('/juridique/'.$module)->assertOk();
            $this->get('/juridique/'.$module.'/nouveau')->assertOk();
        }
        $this->post('/juridique/clients', ['name' => 'Alpha', 'status' => 'active'])->assertRedirect();
        $client = DB::table('jur_clients')->value('id');
        $this->post('/juridique/contracts', array_replace($this->dossier(), ['scope' => 'client', 'client_id' => $client]))->assertRedirect();
        $this->delete('/juridique/clients/'.$client)->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('jur_clients', ['id' => $client]);
        $this->get('/juridique/contracts?overdue=1')->assertOk()->assertSee('Contrat Alpha');
        $this->get('/juridique/contracts?status=done')->assertOk()->assertDontSee('Contrat Alpha');
    }

    public function test_article_seeder_is_repeatable(): void
    {
        $this->user();
        $this->seed(\Database\Seeders\JuridiqueArticleSeeder::class);
        $this->seed(\Database\Seeders\JuridiqueArticleSeeder::class);
        $this->assertSame(1, \App\Models\Article::where('slug', 'chambre-juridique-eunital')->count());
    }
}
