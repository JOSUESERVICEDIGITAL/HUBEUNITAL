<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudioArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChamberSeeder::class);
        $category = Category::where('slug', 'studio')->firstOrFail();
        $author = User::whereIn('role', ['superadmin', 'admin'])->first() ?? User::first();
        $author ??= User::firstOrCreate(['email' => 'redaction@eunital.com'], [
            'name' => 'Rédaction EUNITAL', 'password' => Str::random(64), 'role' => 'author',
        ]);
        $title = 'Chambre Studio EUNITAL : du brief au master final';
        $image = 'fronto/img/news-500x280-1.jpg';
        $previousImage = 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=1600&q=85';
        $existingArticle = Article::where('slug', 'chambre-studio-eunital-du-brief-au-master-final')
            ->orWhere('title', $title)->first();
        if ($existingArticle) {
            if (! $existingArticle->image || $existingArticle->image === $previousImage) {
                $existingArticle->update(['image' => $image]);
            }
            return;
        }
        Article::firstOrCreate(['slug' => 'chambre-studio-eunital-du-brief-au-master-final'], [
            'title' => $title,
            'category_id' => $category->id, 'author_id' => $author->id,
            'image' => $image,
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
            'description' => "La chambre Studio EUNITAL structure la création de contenus photo, vidéo et audio, depuis le premier brief jusqu’aux rendus finaux. Elle réunit préparation, captation, postproduction et suivi des livraisons dans un même espace de travail.\n\nPRÉPARER LA PRODUCTION\nChaque projet est rattaché à un client et à un brief versionné : objectifs, audience, scénario, spécifications techniques et diffusion prévue. Les devis et les budgets donnent un cadre clair aux prestations.\n\nORGANISER LA CAPTATION\nLe planning relie les séances à leurs lieux, à leur équipe et aux équipements réservés. Les contrôles de disponibilité évitent les chevauchements de plateau, de matériel et de collaborateurs. Les sorties et retours des équipements sont suivis jusqu’à la clôture de la séance.\n\nSUIVRE LES MÉDIAS ET LA POSTPRODUCTION\nRushes, photos, enregistrements, fichiers de montage et autorisations sont référencés dans la médiathèque avec leurs versions, liens de sauvegarde et droits d’utilisation. Le montage, le mixage, la retouche, l’étalonnage et les exports sont organisés en tâches.\n\nVALIDER DES RENDUS CLAIRS\nLes livrables disposent d’un responsable, d’une version, d’un lien documentaire et d’un statut de validation. Une production peut être marquée livrée lorsque ses rendus sont acceptés et son travail opérationnel clôturé.\n\nPILOTER L’ACTIVITÉ\nLes factures, encaissements et dépenses restent liés aux productions. Les tableaux de bord, rapports et historiques facilitent le suivi de l’activité. Le superadmin conserve le contrôle d’ouverture de la chambre.\n\nVous préparez un reportage, une campagne visuelle, une interview ou un podcast ? Contactez EUNITAL pour préciser votre projet.\n\nIllustration : Unsplash.",
        ]);
    }
}
