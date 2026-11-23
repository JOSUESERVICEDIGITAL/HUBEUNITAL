<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketingArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChamberSeeder::class);
        $category = Category::where('slug', 'marketing')->firstOrFail();
        $author = User::whereIn('role', ['superadmin', 'admin'])->first() ?? User::first();
        $author ??= User::firstOrCreate(['email' => 'redaction@eunital.com'], [
            'name' => 'Rédaction EUNITAL', 'password' => Str::random(64), 'role' => 'author',
        ]);
        $title = 'Chambre Marketing EUNITAL : de la stratégie aux résultats';
        $image = 'fronto/img/news-500x280-5.jpg';
        $previousImage = 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1600&q=85';
        $existingArticle = Article::where('slug', 'chambre-marketing-eunital-de-la-strategie-aux-resultats')
            ->orWhere('title', $title)->first();
        if ($existingArticle) {
            if (! $existingArticle->image || $existingArticle->image === $previousImage) {
                $existingArticle->update(['image' => $image]);
            }
            return;
        }
        Article::firstOrCreate(['slug' => 'chambre-marketing-eunital-de-la-strategie-aux-resultats'], [
            'title' => $title,
            'category_id' => $category->id, 'author_id' => $author->id,
            'image' => $image,
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
            'description' => "La chambre Marketing EUNITAL accompagne les marques dans la définition de leur positionnement, la préparation de leurs campagnes et le suivi de leur acquisition. Son objectif : relier les idées créatives à des objectifs clairs et à des résultats mesurables.\n\nUNE STRATÉGIE AVANT LA DIFFUSION\nLe travail commence par la compréhension du client, de sa proposition de valeur et de ses audiences. Les stratégies de marque et les briefs structurent les messages, les objectifs et les budgets.\n\nDES CAMPAGNES ET DES CONTENUS SUIVIS\nLes campagnes regroupent les contenus, les tâches et les canaux. Un calendrier éditorial permet de visualiser les dates prévues. Les responsables valident les rendus, leurs versions et les liens de publication.\n\nUN PIPELINE POUR L’ACQUISITION\nLes prospects sont suivis depuis le premier échange jusqu’à la qualification et la conversion en client. Les relances et les autorisations de contact sont documentées dans le CRM.\n\nDES INDICATEURS COMPRÉHENSIBLES\nLes relevés journaliers par canal permettent de suivre impressions, clics, prospects, conversions, dépenses média et revenus attribués. Les rapports calculent le CTR, le coût par prospect, le coût par conversion et le ROAS, tout en distinguant les dépenses de production.\n\nUN ESPACE PILOTÉ PAR SON ÉQUIPE\nLa chambre dispose de ses propres accès, de sa bibliothèque créative et de son historique. Le superadmin peut ouvrir ou fermer cet espace sans supprimer les dossiers.\n\nVous souhaitez structurer votre marque ou préparer une campagne ? Contactez EUNITAL pour échanger sur vos objectifs.\n\nIllustration : Unsplash.",
        ]);
    }
}
