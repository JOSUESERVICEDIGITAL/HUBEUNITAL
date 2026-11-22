<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicServiceInboxTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::factory()->create([
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);
    }

    private function serviceRequest(array $overrides = []): int
    {
        return DB::table('public_service_requests')->insertGetId(array_merge([
            'chamber_slug' => 'secretariat',
            'service_key' => 'secretariat_saisie_de_documents_administratifs',
            'service_name' => 'Saisie de documents administratifs',
            'name' => 'Aline Doe',
            'email' => 'aline@example.com',
            'phone' => '+243 900 000 000',
            'organization' => 'Association Exemple',
            'details' => 'Nous souhaitons faire saisir un dossier administratif.',
            'status' => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_administrator_can_find_and_review_a_request_with_its_chamber_and_selected_service(): void
    {
        $requestId = $this->serviceRequest();

        $this->actingAs($this->administrator())
            ->get(route('public-services.index'))
            ->assertOk()
            ->assertSee('Demandes de services')
            ->assertSee('href="'.route('public-services.index').'"', false)
            ->assertSee('Aline Doe')
            ->assertSee('Secrétariat')
            ->assertSee('Saisie de documents administratifs')
            ->assertSee(route('public-services.show', $requestId), false);

        $this->get(route('public-services.show', $requestId))
            ->assertOk()
            ->assertSee('SR-000001')
            ->assertSee('Action choisie dans le formulaire')
            ->assertSee('Saisie de documents administratifs')
            ->assertSee('secretariat_saisie_de_documents_administratifs')
            ->assertSee('Nous souhaitons faire saisir un dossier administratif.')
            ->assertSee('aline@example.com');
    }

    public function test_administrator_can_filter_requests_and_update_follow_up_status(): void
    {
        $requestId = $this->serviceRequest();
        $this->serviceRequest([
            'chamber_slug' => 'marketing',
            'service_key' => array_key_first(config('public_services.chambers.marketing.services')),
            'service_name' => 'Marketing digital',
            'name' => 'Client Marketing',
        ]);

        $this->actingAs($this->administrator())
            ->get(route('public-services.index', ['chamber_slug' => 'secretariat', 'status' => 'new']))
            ->assertOk()
            ->assertSee('Aline Doe')
            ->assertDontSee('Client Marketing');

        $this->patch(route('public-services.status', $requestId), ['status' => 'contacted'])
            ->assertRedirect(route('public-services.show', $requestId))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_requests', [
            'id' => $requestId,
            'status' => 'contacted',
        ]);
    }

    public function test_non_administrators_cannot_access_the_service_inbox(): void
    {
        $user = User::factory()->create([
            'role' => 'author',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('public-services.index'))
            ->assertForbidden();
    }
}
