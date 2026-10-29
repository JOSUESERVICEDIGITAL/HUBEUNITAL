<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommercialSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RhSeeder::class, CommercialPermissionSeeder::class]);
        $author = User::whereIn('role', ['superadmin', 'admin'])->first() ?? User::first();
        $author ??= User::firstOrCreate(['email' => 'redaction@eunital.com'], ['name' => 'Rédaction EUNITAL', 'password' => Str::random(64), 'role' => 'author']);
        $title = 'La Chambre Commerciale d’EUNITAL : ventes et intelligence terrain';
        if (Article::where('slug', 'chambre-commerciale-eunital')->orWhere('title', $title)->exists()) return;
        Article::firstOrCreate(['slug' => 'chambre-commerciale-eunital'], [
            'title' => $title,
            'category_id' => Category::where('slug', 'commerciale')->firstOrFail()->id, 'author_id' => $author->id,
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
            'description' => "La Chambre Commerciale développe la prospection, les ventes, les négociations, les relations clients et le chiffre d’affaires d’EUNITAL — HUB DIGITAL. Elle accueille aussi une branche Intelligence terrain et Détection des besoins.\n\nDES RESPONSABILITÉS COMPLÉMENTAIRES\nLa RH recrute les agents, tient leurs dossiers et contrats de travail, gère leurs horaires, absences, congés et éléments de rémunération. La Commerciale attribue les prospects, zones, missions et objectifs, prépare les devis avec les chambres techniques et suit les commandes. Juridique vérifie les engagements. Finance émet les factures et enregistre les encaissements. Les commissions calculées sur les ventes nécessitent les validations RH et Finance.\n\nDÉTECTER LES PROBLÈMES RÉELS\nLes agents de veille terrain observent ouvertement les difficultés dans les marchés, écoles, entreprises, services et quartiers. Ils écoutent les personnes qui participent volontairement, documentent le secteur, la localité approximative, la fréquence, les conséquences et les besoins. Ils ne procèdent ni à une infiltration, ni à une usurpation d’identité, ni à une collecte clandestine d’informations confidentielles.\n\nUNE BANQUE DES PROBLÈMES ET BESOINS\nLes signalements, rapports d’observation, enquêtes et résultats agrégés sont accessibles selon les permissions. L’Innovation regroupe et qualifie les besoins récurrents. L’Ingénierie étudie la faisabilité et propose un projet. Les chambres techniques réalisent, testent et déploient la solution. L’agent peut suivre son signalement à chaque étape.\n\nUN EXEMPLE\nÀ Ziniaré, un agent relève avec des participants volontaires que le suivi des absences scolaires sur papier retarde l’information aux parents. Le besoin est documenté, rapproché de signalements similaires et étudié avant une proposition de solution numérique. L’expérimentation recueille les retours des utilisateurs ; la Commerciale pourra ensuite présenter la solution aux établissements intéressés.\n\nORGANISATION\nLe responsable commercial dirige les opérations quotidiennes de vente. Le Founder des Partenariats et Relations stratégiques conduit les alliances fondatrices et institutionnelles selon l’organisation définie par la direction, sans être automatiquement le supérieur opérationnel des commerciaux.\n\nLà où la société rencontre un problème, EUNITAL recherche une solution.",
        ]);
    }
}
