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

    public function test_comment_on_missing_article_returns_404(): void
    {
        $this->post('/comment/999999', ['name' => 'Visiteur', 'email' => 'visiteur@example.com', 'message' => 'Un commentaire.'])->assertNotFound();
        $this->assertDatabaseCount('comments', 0);
    }
}
