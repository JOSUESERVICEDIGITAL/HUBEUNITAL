<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EngineeringArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChamberSeeder::class);
        $category = Category::where('slug', 'ingenierie')->firstOrFail();
        $author = User::whereIn('role', ['superadmin', 'admin'])->first() ?? User::first();
        $author ??= User::firstOrCreate(['email' => 'redaction@eunital.com'], [
            'name' => 'Rédaction EUNITAL', 'password' => Str::random(64), 'role' => 'author',
        ]);
        Article::firstOrCreate(['slug' => 'chambre-ingenierie-eunital-de-l-idee-au-livrable'], [
            'title' => 'Chambre Ingénierie EUNITAL : de l’idée au livrable',
            'category_id' => $category->id,
            'author_id' => $author->id,
            'image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1600&q=85',
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
            'description' => "La chambre Ingénierie d’EUNITAL accompagne la transformation des besoins en solutions concrètes : architecture technique, études de faisabilité, prototypes et dossiers d’ingénierie.\n\nUN PARCOURS STRUCTURÉ\nChaque besoin commence par un échange et un cadrage. Le suivi des clients, des contacts et des opportunités permet de préparer une proposition claire, puis de structurer le projet autour d’objectifs, de responsabilités et d’échéances.\n\nDES RENDUS LISIBLES\nLes tâches et les livrables sont suivis par projet. Chaque rendu dispose d’un responsable, d’une version, d’un lien documentaire et d’un statut de validation : brouillon, à valider, accepté ou corrections demandées. Cette organisation facilite les revues et la traçabilité des décisions.\n\nUN PILOTAGE COMMERCIAL ET FINANCIER\nLe CRM relie devis, factures et encaissements aux dossiers clients. Les dépenses par projet et les rapports donnent une lecture des montants facturés, encaissés et restant à percevoir.\n\nUNE CHAMBRE SOUS CONTRÔLE\nLes membres autorisés accèdent aux outils de gestion. Le superadmin peut ouvrir ou fermer l’espace opérationnel sans effacer son historique.\n\nVous avez une idée, un besoin technique ou un projet à structurer ? Contactez EUNITAL pour échanger avec la chambre Ingénierie.\n\nIllustration : Unsplash.",
        ]);
    }
}
