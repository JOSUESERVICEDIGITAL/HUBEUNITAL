<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChamberCategorySeeder extends Seeder
{
    public function run(): void
    {
        $chambers = [
            'Ingénierie' => 'Conception et ingénierie de solutions technologiques adaptées aux besoins des organisations.',
            'Marketing' => 'Marketing digital, communication et développement de la visibilité des marques.',
            'Studio' => 'Production audiovisuelle, photographie et création de contenus multimédias.',
            'Graphisme' => 'Design graphique, identité visuelle et création de supports de communication.',
            'Juridique' => 'Accompagnement juridique et contractuel des projets et des entreprises.',
            'RH' => 'Gestion des ressources humaines, recrutement et développement des talents.',
            'Commerciale' => 'Prospection, ventes, relation client et intelligence terrain pour détecter les besoins à transformer en projets.',
            'Innovation' => 'Recherche, expérimentation et accompagnement de projets innovants.',
            'Développement' => 'Développement de logiciels, de sites web et d’applications numériques.',
            'Formation' => 'Formation aux métiers du numérique et renforcement des compétences.',
            'Finance' => 'Gestion financière, préparation budgétaire et accompagnement au financement des projets.',
            'Cybersécurité' => 'Sécurisation des systèmes d’information et sensibilisation à la protection des données.',
            'DG et Partenariats' => 'Pilotage stratégique d’EUNITAL, coordination des chambres, relations institutionnelles et développement des partenariats.',
            'Secrétariat' => 'Services bureautiques, infographie, reprographie, calligraphie, marquage textile et broderie.',
        ];

        foreach ($chambers as $name => $description) {
            $slug = $name === 'DG et Partenariats' ? 'strategie' : Str::slug($name);
            // Include soft-deleted records to keep reruns from creating duplicates.
            // Existing categories retain their content and publication status.
            $category = Category::withTrashed()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'isActive' => true],
            );
            if ($slug === 'strategie' && ($category->name !== $name || $category->description !== $description)) {
                $category->update(['name' => $name, 'description' => $description]);
            }
        }
    }
}
