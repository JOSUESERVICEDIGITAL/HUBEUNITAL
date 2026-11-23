<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JuridiqueArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChamberSeeder::class);
        $category = Category::where('slug', 'juridique')->firstOrFail();
        $author = User::whereIn('role', ['superadmin', 'admin'])->first() ?? User::first();
        $author ??= User::firstOrCreate(['email' => 'redaction@eunital.com'], [
            'name' => 'Rédaction EUNITAL', 'password' => Str::random(64), 'role' => 'author',
        ]);
        $title = 'La Chambre Juridique d’EUNITAL';
        $image = 'fronto/img/news-500x280-5.jpg';
        $existingArticle = Article::where('slug', 'chambre-juridique-eunital')->orWhere('title', $title)->first();
        if ($existingArticle) {
            if (! $existingArticle->image) $existingArticle->update(['image' => $image]);
            return;
        }
        Article::firstOrCreate(['slug' => 'chambre-juridique-eunital'], [
            'title' => $title,
            'category_id' => $category->id, 'author_id' => $author->id,
            'image' => $image,
            'isActive' => true, 'isComment' => true, 'isSharable' => true,
            'description' => "La Chambre Juridique protège les intérêts d’EUNITAL et accompagne les entreprises et les particuliers clients du Hub.\n\nSES SIX MISSIONS\n1. Contrats commerciaux, prestations, partenariats, NDA, travail et sous-traitance : préparation documentaire, suivi des engagements et identification des risques.\n2. Création d’entreprise : dossiers administratifs, RCCM, modifications statutaires, dirigeants et orientation sur la forme juridique.\n3. Conformité : veille Burkina Faso et OHADA, données personnelles, activités commerciales et numériques, procédures internes et obligations réglementaires.\n4. Protection juridique et propriété intellectuelle : marques, logos, créations, démarches OAPI, droits d’auteur, licences et cessions, prévention des litiges et coordination avec les professionnels habilités.\n5. Relations institutionnelles : dossiers publics, autorisations, agréments, documentation douanière et démarches administratives.\n6. Accompagnement : information générale, sensibilisation, formations au droit des affaires et du numérique, assistance documentaire et orientation vers des avocats, notaires et autres professionnels habilités.\n\nORGANISATION\nPrésident-Fondateur Général : Josué Pawendtaoré OUEDRAOGO.\nFounder des Affaires juridiques et institutionnelles : orientation et supervision stratégique du pôle fondateur.\nChambre Juridique : responsable opérationnel, juristes, assistants administratifs et partenaires juridiques. Cette représentation ne remplace pas les liens hiérarchiques prévus par la direction générale et les statuts.\n\nEXEMPLE\nPour une société de commerce électronique, la chambre accompagne le dossier d’entreprise, les contrats, la marque et la conformité du site. La Chambre Développement crée la plateforme, la Chambre Graphisme son identité visuelle et la Chambre Marketing sa communication.\n\nL’assistance administrative et documentaire se distingue des consultations, de la représentation en justice et des actes réservés aux professionnels habilités. Les services sont organisés dans le respect des professions réglementées.",
        ]);
    }
}
