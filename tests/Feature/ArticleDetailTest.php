<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArticleDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_article_returns_404_instead_of_a_server_error(): void
    {
        $this->get('/detail/article-inexistant')->assertNotFound();
    }

    public function test_visits_increment_without_changing_the_article_url(): void
    {
        $this->seed(ChamberSeeder::class);
        \App\Models\Settings::create(['website_name' => 'EUNITAL', 'about' => 'Le Hub digital.']);
        $user = User::factory()->create();
        $article = Article::create([
            'title' => 'La Chambre Juridique d’EUNITAL', 'description' => 'Présentation juridique.',
            'category_id' => Category::where('slug', 'juridique')->value('id'), 'author_id' => $user->id,
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
        ]);
        // A previously published slug may differ from the current title's generated slug.
        DB::table('articles')->where('id', $article->id)->update(['slug' => 'chambre-juridique-eunital', 'views' => 7]);

        $this->get('/detail/chambre-juridique-eunital')->assertOk()
            ->assertViewHas('article', fn ($found) => $found->id === $article->id && $found->views === 8);
        $this->get('/detail/chambre-juridique-eunital')->assertOk();
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'slug' => 'chambre-juridique-eunital', 'views' => 9]);
    }

    public function test_public_service_dock_is_available_on_article_and_chamber_pages(): void
    {
        $this->seed(ChamberSeeder::class);
        \App\Models\Settings::create(['website_name' => 'EUNITAL', 'about' => 'Le Hub digital.']);
        $user = User::factory()->create();
        $article = Article::create([
            'title' => 'La Chambre Secrétariat d’EUNITAL', 'description' => 'Présentation du secrétariat.',
            'category_id' => Category::where('slug', 'secretariat')->value('id'), 'author_id' => $user->id,
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
        ]);

        $this->get('/detail/'.$article->slug)->assertOk()
            ->assertSee('public-service-trigger', false)
            ->assertSee('Nos services pour Secrétariat')
            ->assertSee('Chambre : Secrétariat')
            ->assertSee('Saisie de documents administratifs')
            ->assertDontSee('Campagnes Google Ads et Meta Ads');
        $this->get(route('category.activite', 'secretariat'))->assertOk()
            ->assertSee('public-service-trigger', false)
            ->assertSee('Chambre : Secrétariat')
            ->assertSee('Photocopie et duplication de documents')
            ->assertDontSee('Photographie professionnelle');
    }

    public function test_each_configured_chamber_only_displays_its_own_service_catalogue(): void
    {
        $this->seed(ChamberSeeder::class);
        \App\Models\Settings::create(['website_name' => 'EUNITAL', 'about' => 'Le Hub digital.']);

        foreach (config('public_services.chambers') as $slug => $chamber) {
            $response = $this->get(route('category.activite', $slug))->assertOk()
                ->assertSee('Nos services pour '.$chamber['name'])
                ->assertSee($chamber['mission'])
                ->assertSee('data-service-key="'.array_key_first($chamber['services']).'"', false);

            $this->assertSame(
                count($chamber['services']),
                substr_count($response->getContent(), 'class="public-service-option"')
            );
            foreach ($chamber['services'] as $service) {
                $response->assertSee($service['label']);
            }
        }
    }

    public function test_graphisme_catalogue_does_not_repeat_logo_creation_as_visual_identity_creation(): void
    {
        $this->seed(ChamberSeeder::class);
        \App\Models\Settings::create(['website_name' => 'EUNITAL', 'about' => 'Le Hub digital.']);

        $services = config('public_services.chambers.graphisme.services');
        $this->assertCount(18, $services);

        $response = $this->get(route('category.activite', 'graphisme'))->assertOk()
            ->assertSee('Création de logos')
            ->assertSee('Création de chartes graphiques');

        $response->assertDontSee('Création d’identités visuelles');
    }

    public function test_comment_on_missing_article_returns_404(): void
    {
        $this->post('/comment/999999', ['name' => 'Visiteur', 'email' => 'visiteur@example.com', 'message' => 'Un commentaire.'])->assertNotFound();
        $this->assertDatabaseCount('comments', 0);
    }
}
