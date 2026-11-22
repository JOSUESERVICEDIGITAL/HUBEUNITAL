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

class DgPartnershipsCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChamberSeeder::class);
        Chamber::where('slug', 'strategie')->update(['is_open' => true]);
    }

    private function user(string $role = 'superadmin'): User
    {
        return User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
    }

    public function test_all_crm_pages_render_and_dg_and_partner_images_are_saved(): void
    {
        Storage::fake('public');
        $this->actingAs($this->user());
        $this->get(route('dg.dashboard'))->assertOk();

        foreach (array_keys(config('dg.modules')) as $module) {
            $this->get(route('dg.index', $module))->assertOk();
            $this->get(route('dg.create', $module))->assertOk();
        }

        $this->post(route('dg.store', 'leadership'), [
            'full_name' => 'Aminata Ouedraogo',
            'title' => 'Directrice générale',
            'photo_path' => UploadedFile::fake()->image('dg.jpg'),
            'email' => 'dg@example.test',
            'mandate_starts_on' => today()->format('Y-m-d'),
            'status' => 'active',
            'vision' => 'Développer les partenariats.',
        ])->assertRedirect();
        $leader = DB::table('dg_leadership')->first();
        Storage::disk('public')->assertExists($leader->photo_path);

        $this->post(route('dg.store', 'partners'), [
            'name' => 'Université du Plateau',
            'logo_path' => UploadedFile::fake()->image('partner.png'),
            'organization_type' => 'university',
            'sector' => 'Éducation',
            'contact_name' => 'Moussa',
            'contact_title' => 'Directeur des partenariats',
            'email' => 'contact@example.test',
            'website' => 'https://example.test',
            'status' => 'active',
            'objectives' => 'Développer des formations communes.',
        ])->assertRedirect();
        $partner = DB::table('dg_partners')->first();
        Storage::disk('public')->assertExists($partner->logo_path);
        $this->get(route('dg.show', ['partners', $partner->id]))->assertOk()->assertSee('Université du Plateau')->assertSee('img');
        $this->get(route('dg.reports'))->assertOk()->assertSee('Université du Plateau');
        $this->assertDatabaseCount('chamber_audits', 2);
    }

    public function test_leadership_photo_and_partner_logo_are_required_and_validated(): void
    {
        $this->actingAs($this->user());
        $this->post(route('dg.store', 'leadership'), [
            'full_name' => 'DG Test',
            'title' => 'Directeur',
            'status' => 'active',
        ])->assertSessionHasErrors('photo_path');

        $this->post(route('dg.store', 'partners'), [
            'name' => 'Partenaire test',
            'organization_type' => 'company',
            'status' => 'prospect',
        ])->assertSessionHasErrors('logo_path');
    }

    public function test_strategy_chamber_is_renamed_without_changing_its_slug_or_open_state(): void
    {
        Chamber::where('slug', 'strategie')->update(['is_open' => false]);
        $this->seed(ChamberSeeder::class);
        $chamber = Chamber::where('slug', 'strategie')->firstOrFail();
        $this->assertSame('DG et Partenariats', $chamber->name);
        $this->assertFalse($chamber->is_open);
        $this->assertSame('strategie', $chamber->slug);
    }

    public function test_member_access_and_closed_chamber_are_enforced(): void
    {
        $member = $this->user('author');
        DB::table('dg_team')->insert([
            'user_id' => $member->id,
            'role' => 'partnerships',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($member)->get(route('dg.dashboard'))->assertOk();
        DB::table('dg_team')->where('user_id', $member->id)->update(['status' => 'inactive']);
        $this->get(route('dg.dashboard'))->assertForbidden();

        Chamber::where('slug', 'strategie')->update(['is_open' => false]);
        $this->actingAs($this->user())->get(route('dg.dashboard'))->assertForbidden();
    }
}
