<?php

namespace Tests\Feature;

use App\Models\Settings;
use Database\Seeders\ChamberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicFrontendToastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChamberSeeder::class);
        Settings::create([
            'website_name' => 'EUNITAL',
            'about' => 'Le Hub digital.',
            'adress' => 'Kinshasa',
            'email' => 'contact@example.com',
            'phone' => '+243000000000',
        ]);
    }

    public function test_contact_submission_flashes_success_and_frontend_renders_izi_toast(): void
    {
        Mail::fake();

        $this->post(route('front.contact.send'), [
            'name' => 'Aline Doe',
            'email' => 'aline@example.com',
            'subject' => 'Demande de renseignements',
            'message' => 'Bonjour, je souhaite obtenir des renseignements.',
        ])->assertRedirect(route('front.contact'))
            ->assertSessionHas('success');

        $this->withSession(['success' => 'Message envoyé avec succès.'])
            ->get(route('category.activite', 'secretariat'))
            ->assertOk()
            ->assertSee('iziToast.success', false)
            ->assertSee('Message envoy', false);
    }

    public function test_newsletter_subscription_flashes_success_and_validation_errors_are_toasted(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), [
            'email' => 'abonne@example.com',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $this->get(route('category.activite', 'secretariat'))
            ->assertOk()
            ->assertSee('iziToast.success', false)
            ->assertSee('Merci pour votre inscription', false);

        $this->from(route('home'))->post(route('newsletter.subscribe'), [
            'email' => 'abonne@example.com',
        ])->assertRedirect(route('home'))
            ->assertSessionHasErrors('email');

        $this->get(route('category.activite', 'secretariat'))
            ->assertOk()
            ->assertSee('iziToast.error', false);
    }

    public function test_homepage_explains_eunital_and_groups_all_fourteen_chambers(): void
    {
        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSee('Le hub digital multi-métiers pour structurer, développer et sécuriser vos projets.')
            ->assertSee('entrepreneurs, entreprises')
            ->assertSee('Comment ça marche ?')
            ->assertSee('Choisissez une Chambre')
            ->assertSee('Choisissez un service')
            ->assertSee('Nos services pour…')
            ->assertSee('Envoyez votre demande')
            ->assertSee('Choisir cette Chambre')
            ->assertSee('Un agent EUNITAL vous contactera prochainement.')
            ->assertSee('Découvrir les Chambres')
            ->assertSee(route('category.activite', 'secretariat'), false)
            ->assertSee(route('front.contact'), false)
            ->assertSee('Tech &amp; numérique', false)
            ->assertSee('Image &amp; croissance', false)
            ->assertSee('Gestion &amp; cadre', false)
            ->assertSee('Formation &amp; stratégie', false);

        $this->assertSame(14, substr_count($response->getContent(), 'class="home-chamber-name"'));

        $this->get(route('category.activite', 'secretariat'))
            ->assertOk()
            ->assertSee('Nos services pour Secrétariat')
            ->assertSee('data-service-key="secretariat_saisie_de_documents_administratifs"', false)
            ->assertSee('Votre nom *')
            ->assertSee('Téléphone *')
            ->assertSee('Décrivez votre besoin *');
    }
}
