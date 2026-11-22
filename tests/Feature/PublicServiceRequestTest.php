<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_public_service_request_is_saved_with_a_reference(): void
    {
        $this->postJson(route('public.services.request'), [
            'chamber_slug' => 'secretariat',
            'service_key' => 'secretariat_saisie_de_documents_administratifs',
            'name' => 'Aline Doe',
            'email' => 'aline@example.com',
            'phone' => '+243 900 000 000',
            'organization' => 'Association Exemple',
            'details' => 'Nous souhaitons créer un site vitrine.',
        ])->assertCreated()
            ->assertJsonPath('reference', 'SR-000001');

        $this->assertDatabaseHas('public_service_requests', [
            'chamber_slug' => 'secretariat',
            'service_key' => 'secretariat_saisie_de_documents_administratifs',
            'service_name' => 'Saisie de documents administratifs',
            'name' => 'Aline Doe',
            'email' => 'aline@example.com',
            'status' => 'new',
        ]);
    }

    public function test_an_unknown_public_service_is_rejected(): void
    {
        $this->postJson(route('public.services.request'), [
            'chamber_slug' => 'secretariat',
            'service_key' => 'unknown_service',
            'name' => 'Aline Doe',
            'email' => 'aline@example.com',
            'phone' => '+243 900 000 000',
            'details' => 'Nous souhaitons un service.',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('service_key');

        $this->assertDatabaseCount('public_service_requests', 0);
    }

    public function test_a_service_from_another_chamber_cannot_be_submitted(): void
    {
        $marketingServiceKey = array_key_first(config('public_services.chambers.marketing.services'));

        $this->postJson(route('public.services.request'), [
            'chamber_slug' => 'secretariat',
            'service_key' => $marketingServiceKey,
            'name' => 'Aline Doe',
            'email' => 'aline@example.com',
            'phone' => '+243 900 000 000',
            'details' => 'Une demande hors catalogue.',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('service_key');

        $this->assertDatabaseCount('public_service_requests', 0);
    }
}
