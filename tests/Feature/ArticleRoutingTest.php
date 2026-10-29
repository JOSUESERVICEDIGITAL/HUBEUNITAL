<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleRoutingTest extends TestCase
{
    use RefreshDatabase;

    private function article(): Article
    {
        $this->seed(ChamberSeeder::class);
        $user = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($user);

        return Article::create([
            'title' => 'La Chambre Juridique d’EUNITAL',
            'description' => 'Présentation de la chambre juridique.',
            'category_id' => Category::where('slug', 'juridique')->value('id'),
            'author_id' => $user->id,
            'image' => 'asset/juridique.jpg',
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
        ]);
    }

    public function test_edit_and_show_resolve_the_article_by_slug(): void
    {
        $article = $this->article();

        $this->get(route('activite.edit', $article))->assertOk()
            ->assertViewHas('article', fn ($bound) => $bound->is($article))
            ->assertSee(route('activite.update', $article), false)
            ->assertSee($article->description);
        $this->get(route('activite.show', $article))->assertOk()->assertSee($article->description);
        $this->get('/activite/article-inexistant/edit')->assertNotFound();
    }

    public function test_update_saves_the_bound_article_and_preserves_its_image(): void
    {
        $article = $this->article();

        $this->put(route('activite.update', $article), [
            'title' => $article->title,
            'description' => 'Présentation juridique mise à jour.',
            'category_id' => $article->category_id,
            'isActive' => 1, 'isComment' => 1, 'isSharable' => 1,
            'tags' => 'Juridique,EUNITAL',
        ])->assertRedirect(route('activite.index'));

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'description' => 'Présentation juridique mise à jour.',
            'image' => 'asset/juridique.jpg',
        ]);
        $this->assertCount(2, $article->fresh()->tags);
    }

    public function test_delete_removes_the_article_selected_by_slug(): void
    {
        $article = $this->article();

        $this->delete(route('activite.destroy', $article))->assertRedirect();
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }
}
