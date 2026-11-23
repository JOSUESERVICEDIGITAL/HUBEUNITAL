<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GraphismeArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChamberSeeder::class);
        $category = Category::where('slug', 'graphisme')->firstOrFail();
        $author = User::whereIn('role', ['superadmin', 'admin'])->first() ?? User::first();
        $author ??= User::firstOrCreate(['email' => 'redaction@eunital.com'], [
            'name' => 'Rédaction EUNITAL', 'password' => Str::random(64), 'role' => 'author',
        ]);
        $title = 'Chambre Graphisme EUNITAL : du brief à l’identité visuelle';
        $image = 'fronto/img/news-300x300-2.jpg';
        $previousImage = 'https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=1600&q=85';
        $existingArticle = Article::where('slug', 'chambre-graphisme-eunital-du-brief-a-l-identite-visuelle')
            ->orWhere('title', $title)->first();
        if ($existingArticle) {
            if (! $existingArticle->image || $existingArticle->image === $previousImage) {
                $existingArticle->update(['image' => $image]);
            }
            return;
        }
        Article::firstOrCreate(['slug' => 'chambre-graphisme-eunital-du-brief-a-l-identite-visuelle'], [
            'title' => $title,
            'category_id' => $category->id, 'author_id' => $author->id,
            'image' => $image,
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
            'description' => "La chambre Graphisme EUNITAL accompagne la création d’identités visuelles, d’interfaces, de supports imprimés et de contenus graphiques. Son organisation relie le besoin du client aux choix créatifs, aux corrections et aux fichiers finaux.\n\nCADRER LE BESOIN\nChaque projet commence par un brief précisant objectifs, audience, contraintes et références. Les chartes documentent les palettes, typographies et règles d’usage. La bibliothèque rassemble les ressources et leurs droits d’utilisation déclarés.\n\nCRÉER ET COMPARER LES VERSIONS\nLes maquettes sont suivies par projet, support, responsable et échéance. Chaque version dispose d’un fichier source, d’un aperçu et de notes de modification. La galerie permet de retrouver les propositions sans confondre leurs versions.\n\nCENTRALISER LES CORRECTIONS\nLes retours internes et clients sont rattachés à une maquette et, si nécessaire, à une version précise. Leur zone concernée, leur réponse et leur traitement restent documentés. Les responsables valident les versions et clôturent les retours client.\n\nLIVRER DES FICHIERS IDENTIFIÉS\nLes livrables sont liés à une version source approuvée, avec leur format et leur lien de livraison. Une version déjà approuvée ne doit pas être écrasée : les évolutions donnent lieu à une nouvelle version.\n\nPILOTER L’ACTIVITÉ CRÉATIVE\nLe planning, les tâches et les KPI facilitent le suivi quotidien. Les devis, factures, encaissements et dépenses complètent le suivi de chaque projet. Le superadmin conserve le contrôle d’ouverture de la chambre.\n\nVous souhaitez créer une identité de marque ou préparer vos supports visuels ? Contactez EUNITAL pour cadrer votre projet graphique.\n\nIllustration : Unsplash.",
        ]);
    }
}
