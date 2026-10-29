<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Chamber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RhSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([ChamberSeeder::class, RhPermissionSeeder::class]);
        foreach (Chamber::all() as $chamber) {
            if (!DB::table('rh_departments')->where('chamber_id', $chamber->id)->exists()) {
                DB::table('rh_departments')->insert(['name' => $chamber->name, 'chamber_id' => $chamber->id, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        $author = User::whereIn('role', ['superadmin', 'admin'])->first() ?? User::first();
        $author ??= User::firstOrCreate(['email' => 'redaction@eunital.com'], ['name' => 'Rédaction EUNITAL', 'password' => Str::random(64), 'role' => 'author']);
        $title = 'La Chambre RH d’EUNITAL : accompagner chaque collaborateur';
        // Editing an article can regenerate its slug; preserve an existing matching title.
        if (Article::where('slug', 'chambre-rh-eunital')->orWhere('title', $title)->exists()) return;
        Article::firstOrCreate(['slug' => 'chambre-rh-eunital'], [
            'title' => $title,
            'category_id' => Category::where('slug', 'rh')->firstOrFail()->id, 'author_id' => $author->id,
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
            'description' => "La Chambre des Ressources Humaines est le centre de gestion de l’ensemble du personnel d’EUNITAL — HUB DIGITAL : commerciaux, développeurs, ingénieurs, graphistes, formateurs, secrétaires et autres collaborateurs. Elle suit leur parcours de la candidature au départ.\n\nPERSONNEL ET ORGANISATION\nDossiers employés, matricules, départements, chambres, postes, affectations, statuts et contrats de travail : embauches, renouvellements, avenants et fins de contrat.\n\nTEMPS ET ASSIDUITÉ\nPlannings, horaires, pointages d’arrivée et de départ, retards, heures supplémentaires, absences justifiées ou injustifiées, autorisations, justificatifs, demandes de congés et soldes.\n\nRECRUTEMENT ET CARRIÈRE\nOffres d’emploi, candidatures, entretiens, sélection, intégration, évaluations, objectifs individuels, compétences, formations internes, promotions et mutations.\n\nADMINISTRATION ET SUIVI\nSuivi administratif des commerciaux, primes, indemnités, éléments variables à transmettre à Finance, discipline, avertissements, incidents, conditions de travail, sécurité, conflits internes, documents, attestations, départs et archivage.\n\nLES COMMERCIAUX SONT AUSSI DES COLLABORATEURS\nLa chambre métier pilote la prospection, la négociation, le portefeuille et le chiffre d’affaires. La RH gère le recrutement, les contrats, les horaires, les absences, les congés, les évaluations et les éléments de rémunération. Lorsqu’un commercial visite dix établissements scolaires, les résultats relèvent du suivi commercial ; les horaires, autorisations et le dossier professionnel relèvent de la RH.\n\nAUTORISATIONS ET CONFIDENTIALITÉ\nLes accès distinguent responsable RH, assistant RH, Finance et employé. Un collaborateur consulte ses propres présences, congés et documents autorisés, sans accéder aux dossiers disciplinaires ou aux éléments de paie des autres. Les documents confidentiels sont protégés.\n\nUNE CHAMBRE TRANSVERSALE\nLa RH accompagne les douze chambres existantes et les futures chambres sans se substituer à leurs responsables métiers. La gestion du personnel reste centralisée, organisée et traçable à mesure que l’entreprise grandit.",
        ]);
    }
}
