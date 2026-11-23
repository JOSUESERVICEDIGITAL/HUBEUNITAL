<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\ChamberSeeder;
use Database\Seeders\EngineeringArticleSeeder;
use Database\Seeders\MissingChamberArticlesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MissingChamberArticlesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_chambers_get_two_visible_articles_with_existing_local_images(): void
    {
        $chambers = ['innovation', 'developpement', 'formation', 'finance', 'cybersecurite', 'strategie', 'secretariat'];
        $this->seed(MissingChamberArticlesSeeder::class);

        foreach ($chambers as $slug) {
            $category = Category::where('slug', $slug)->firstOrFail();
            $articles = Article::where('category_id', $category->id)->where('isActive', true)->get();

            $this->assertCount(2, $articles, "La chambre {$slug} doit présenter deux articles actifs.");
            foreach ($articles as $article) {
                $this->assertFileExists(public_path($article->image));
                $this->assertSame(asset($article->image), $article->imageUrl());
                $this->assertStringContainsString('COMMENT DEMANDER CE SERVICE', $article->description);
            }

            $response = $this->get(route('category.activite', $slug))->assertOk();
            foreach ($articles as $article) {
                $response->assertSee($article->title)->assertSee($article->imageUrl(), false);
            }
        }

        $this->seed(MissingChamberArticlesSeeder::class);
        $this->assertSame(14, Article::whereIn('category_id', Category::whereIn('slug', $chambers)->select('id'))->count());
    }

    public function test_article_seeder_preserves_a_matching_title_after_its_slug_was_edited(): void
    {
        $this->seed(ChamberSeeder::class);
        $category = Category::where('slug', 'ingenierie')->firstOrFail();
        $author = User::factory()->create(['role' => 'admin']);
        DB::table('articles')->insert([
            'title' => 'Chambre Ingénierie EUNITAL : de l’idée au livrable',
            'slug' => 'titre-personnalise-par-la-redaction',
            'image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1600&q=85',
            'description' => 'Article déjà modifié par la rédaction.',
            'isActive' => true,
            'isComment' => true,
            'isSharable' => true,
            'category_id' => $category->id,
            'author_id' => $author->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seed(EngineeringArticleSeeder::class);

        $this->assertSame(1, Article::where('category_id', $category->id)->count());
        $article = Article::where('category_id', $category->id)->firstOrFail();
        $this->assertSame('titre-personnalise-par-la-redaction', $article->slug);
        $this->assertSame('fronto/img/news-300x300-4.jpg', $article->image);
    }
}
