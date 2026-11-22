<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProspectingFormTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::factory()->create([
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);
    }

    private function logoRequest(): int
    {
        return DB::table('public_service_requests')->insertGetId([
            'chamber_slug' => 'graphisme',
            'service_key' => 'graphisme_creation_de_logos',
            'service_name' => 'Création de logos',
            'name' => 'Client Graphisme',
            'email' => 'client@example.com',
            'phone' => '+243 900 000 000',
            'details' => 'Nous avons besoin de créer un logo.',
            'status' => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function logoAnswers(): array
    {
        return [
            'nom' => 'Entreprise Exemple',
            'secteur' => 'Technologie',
            'services' => 'Services numériques',
            'cible' => 'Entreprises',
            'message' => 'Une marque moderne',
            'email' => 'contact@exemple.com',
            'type' => 'Logo combiné (texte + icône)',
            'style' => 'Moderne',
            'vision' => 'Incarner une entreprise innovante.',
            'supports' => ['Sites web', 'Réseaux sociaux'],
            'formats' => ['PNG', 'SVG'],
            'versions' => ['Couleur', 'Fond transparent'],
            'personnalite' => ['Innovante', 'Professionnelle'],
        ];
    }

    private function savedLogoProspect(int $createdBy): int
    {
        return DB::table('public_service_prospects')->insertGetId([
            'public_service_request_id' => null,
            'chamber_slug' => 'graphisme',
            'service_key' => 'graphisme_creation_de_logos',
            'service_name' => 'Création de logos',
            'answers' => json_encode(['nom' => 'Entreprise Exemple'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'created_by' => $createdBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function requiredAnswers(string $chamber, string $service): array
    {
        $answers = [];
        foreach (config('prospecting_forms.'.$chamber.'.'.$service.'.sections') as $section) {
            foreach ($section['fields'] as $field) {
                if (!($field['required'] ?? false)) {
                    continue;
                }

                $answers[$field['name']] = match ($field['type']) {
                    'checkboxes' => [$field['options'][0]],
                    'radio', 'select' => $field['options'][0],
                    'email' => 'contact@example.com',
                    'date' => '2026-12-01',
                    default => 'Réponse de test',
                };
            }
        }

        return $answers;
    }

    public function test_agent_can_prepare_logo_brief_from_public_request_reference(): void
    {
        $requestId = $this->logoRequest();

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'reference',
                'reference' => 'SR-'.str_pad((string) $requestId, 6, '0', STR_PAD_LEFT),
            ])
            ->assertOk()
            ->assertSee('Graphisme')
            ->assertSee('Client Graphisme')
            ->assertSee('Questionnaire — Brief de création de logo')
            ->assertSee('Message clé à transmettre')
            ->assertSee('Formats souhaités')
            ->assertSee('name="reference" value="SR-000001"', false);
    }

    public function test_agent_can_select_logo_service_manually_and_save_answers(): void
    {
        $admin = $this->administrator();

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'graphisme',
                'service_key' => 'graphisme_creation_de_logos',
            ])
            ->assertOk()
            ->assertSee('Prospection créée sans demande publique associée.')
            ->assertSee('Questionnaire — Brief de création de logo');

        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'graphisme',
            'service_key' => 'graphisme_creation_de_logos',
            'answers' => $this->logoAnswers(),
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'public_service_request_id' => null,
            'chamber_slug' => 'graphisme',
            'service_key' => 'graphisme_creation_de_logos',
            'service_name' => 'Création de logos',
            'created_by' => $admin->id,
        ]);

        $prospect = DB::table('public_service_prospects')->first();
        $answers = json_decode($prospect->answers, true);
        $this->assertSame(['Sites web', 'Réseaux sociaux'], $answers['supports']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Entreprise Exemple')
            ->assertSee('Sites web, Réseaux sociaux')
            ->assertSee('Graphisme');
    }

    public function test_agent_can_save_logo_brief_linked_to_the_exact_public_request(): void
    {
        $requestId = $this->logoRequest();

        $this->actingAs($this->administrator())
            ->post(route('prospecting-forms.store'), [
                'reference' => 'SR-000001',
                'chamber_slug' => 'graphisme',
                'service_key' => 'graphisme_creation_de_logos',
                'answers' => $this->logoAnswers(),
            ])->assertRedirect(route('prospecting-forms.index'));

        $this->assertDatabaseHas('public_service_prospects', [
            'public_service_request_id' => $requestId,
            'chamber_slug' => 'graphisme',
            'service_key' => 'graphisme_creation_de_logos',
        ]);
    }

    public function test_agent_can_open_and_save_the_graphic_charter_prospecting_form(): void
    {
        $serviceKey = 'graphisme_creation_de_chartes_graphiques';
        $answers = $this->requiredAnswers('graphisme', $serviceKey);
        $this->assertSame(
            ['nom', 'secteur', 'presentation', 'cible', 'valeurs', 'responsable', 'email', 'telephone', 'identite_existante', 'objectifs', 'confirmation'],
            array_keys($answers)
        );

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'graphisme',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Questionnaire — Brief de création de charte graphique')
            ->assertSee('12. Validation et remarques finales')
            ->assertSee('Zone de protection et taille minimale')
            ->assertSee('Téléphone / WhatsApp')
            ->assertSee('Date limite souhaitée');

        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'graphisme',
            'service_key' => $serviceKey,
            'answers' => $answers,
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'graphisme',
            'service_key' => $serviceKey,
            'service_name' => 'Création de chartes graphiques',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Réponse de test')
            ->assertSee('Création de chartes graphiques');
    }

    public function test_agent_can_complete_and_save_the_publicity_poster_brief(): void
    {
        $serviceKey = 'graphisme_conception_daffiches_publicitaires';
        $answers = $this->requiredAnswers('graphisme', $serviceKey);

        $this->assertSame(
            ['organisation', 'responsable', 'email', 'telephone', 'campagne', 'type_campagne', 'description', 'objectif', 'message', 'public', 'titre', 'date_livraison'],
            array_keys($answers)
        );

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'graphisme',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Questionnaire — Brief de conception d’affiche publicitaire')
            ->assertSee('1. Informations sur le client')
            ->assertSee('10. Validation et remarques finales')
            ->assertSee('Date de livraison finale')
            ->assertSee('Supports et canaux de diffusion');

        $invalidAnswers = $answers;
        $invalidAnswers['date_livraison'] = 'date invalide';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'graphisme',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.date_livraison');

        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'graphisme',
            'service_key' => $serviceKey,
            'answers' => $answers,
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'graphisme',
            'service_key' => $serviceKey,
            'service_name' => 'Conception d’affiches publicitaires',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame('2026-12-01', $storedAnswers['date_livraison']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Réponse de test')
            ->assertSee('Conception d’affiches publicitaires');
    }

    public function test_agent_can_complete_and_save_the_flyer_brief(): void
    {
        $serviceKey = 'graphisme_conception_de_flyers';
        $answers = $this->requiredAnswers('graphisme', $serviceKey);

        $this->assertSame(
            ['organisation', 'responsable', 'email', 'telephone', 'nom_projet', 'type_flyer', 'description', 'objectif', 'public', 'message', 'titre', 'date_livraison'],
            array_keys($answers)
        );

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'graphisme',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Questionnaire — Brief de conception de flyer')
            ->assertSee('1. Informations sur le client')
            ->assertSee('10. Validation et remarques finales')
            ->assertSee('Format souhaité')
            ->assertSee('Nombre de faces');

        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'graphisme',
            'service_key' => $serviceKey,
            'answers' => $answers,
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'graphisme',
            'service_key' => $serviceKey,
            'service_name' => 'Conception de flyers',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Réponse de test')
            ->assertSee('Conception de flyers');
    }

    public function test_all_graphisme_services_have_a_complete_savable_prospecting_form(): void
    {
        $admin = $this->administrator();
        $catalogue = config('public_services.chambers.graphisme.services');
        $forms = config('prospecting_forms.graphisme');

        $this->assertSame([], array_values(array_diff(array_keys($catalogue), array_keys($forms))));

        foreach ($catalogue as $serviceKey => $service) {
            $this->assertNotEmpty($forms[$serviceKey]['sections'] ?? []);

            $this->actingAs($admin)
                ->followingRedirects()
                ->post(route('prospecting-forms.resolve'), [
                    'mode' => 'manual',
                    'chamber_slug' => 'graphisme',
                    'service_key' => $serviceKey,
                ])
                ->assertOk()
                ->assertSee($forms[$serviceKey]['title']);

            $this->post(route('prospecting-forms.store'), [
                'chamber_slug' => 'graphisme',
                'service_key' => $serviceKey,
                'answers' => $this->requiredAnswers('graphisme', $serviceKey),
            ])->assertRedirect(route('prospecting-forms.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('public_service_prospects', [
                'chamber_slug' => 'graphisme',
                'service_key' => $serviceKey,
                'service_name' => $service['label'],
                'created_by' => $admin->id,
            ]);
        }
    }

    public function test_all_secretariat_services_have_unique_labels_and_complete_savable_forms(): void
    {
        $admin = $this->administrator();
        $catalogue = config('public_services.chambers.secretariat.services');
        $forms = config('prospecting_forms.secretariat');
        $normalizedLabels = array_map(
            static fn (array $service): string => \Illuminate\Support\Str::slug($service['label']),
            array_values($catalogue)
        );

        $this->assertSame(count($normalizedLabels), count(array_unique($normalizedLabels)));
        $this->assertSame([], array_values(array_diff(array_keys($catalogue), array_keys($forms))));

        foreach ($catalogue as $serviceKey => $service) {
            $this->assertNotEmpty($forms[$serviceKey]['sections'] ?? []);

            $this->actingAs($admin)
                ->followingRedirects()
                ->post(route('prospecting-forms.resolve'), [
                    'mode' => 'manual',
                    'chamber_slug' => 'secretariat',
                    'service_key' => $serviceKey,
                ])
                ->assertOk()
                ->assertSee($forms[$serviceKey]['title'])
                ->assertSee($forms[$serviceKey]['description']);

            $this->post(route('prospecting-forms.store'), [
                'chamber_slug' => 'secretariat',
                'service_key' => $serviceKey,
                'answers' => $this->requiredAnswers('secretariat', $serviceKey),
            ])->assertRedirect(route('prospecting-forms.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('public_service_prospects', [
                'chamber_slug' => 'secretariat',
                'service_key' => $serviceKey,
                'service_name' => $service['label'],
                'created_by' => $admin->id,
            ]);
        }
    }

    public function test_all_studio_services_have_no_public_catalogue_duplicates_and_savable_forms(): void
    {
        $admin = $this->administrator();
        $catalogues = config('public_services.chambers');
        $catalogue = $catalogues['studio']['services'];
        $forms = config('prospecting_forms.studio');
        $otherLabels = [];

        foreach ($catalogues as $chamberSlug => $chamber) {
            if ($chamberSlug === 'studio') {
                continue;
            }
            foreach ($chamber['services'] as $service) {
                $otherLabels[] = \Illuminate\Support\Str::slug($service['label']);
            }
        }

        $studioLabels = array_map(
            static fn (array $service): string => \Illuminate\Support\Str::slug($service['label']),
            array_values($catalogue)
        );
        $this->assertSame(count($studioLabels), count(array_unique($studioLabels)));
        $this->assertSame([], array_values(array_intersect($studioLabels, $otherLabels)));
        $this->assertSame([], array_values(array_diff(array_keys($catalogue), array_keys($forms))));

        foreach ($catalogue as $serviceKey => $service) {
            $this->assertNotEmpty($forms[$serviceKey]['sections'] ?? []);

            $this->actingAs($admin)
                ->followingRedirects()
                ->post(route('prospecting-forms.resolve'), [
                    'mode' => 'manual',
                    'chamber_slug' => 'studio',
                    'service_key' => $serviceKey,
                ])
                ->assertOk()
                ->assertSee($forms[$serviceKey]['title'])
                ->assertSee($forms[$serviceKey]['description']);

            $this->post(route('prospecting-forms.store'), [
                'chamber_slug' => 'studio',
                'service_key' => $serviceKey,
                'answers' => $this->requiredAnswers('studio', $serviceKey),
            ])->assertRedirect(route('prospecting-forms.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('public_service_prospects', [
                'chamber_slug' => 'studio',
                'service_key' => $serviceKey,
                'service_name' => $service['label'],
                'created_by' => $admin->id,
            ]);
        }
    }

    public function test_all_development_services_have_complete_savable_prospecting_forms(): void
    {
        $admin = $this->administrator();
        $catalogue = config('public_services.chambers.developpement.services');
        $forms = config('prospecting_forms.developpement');

        $this->assertSame([], array_values(array_diff(array_keys($catalogue), array_keys($forms))));
        $this->assertSame([], array_values(array_diff(array_keys($forms), array_keys($catalogue))));

        foreach ($catalogue as $serviceKey => $service) {
            $this->assertNotEmpty($forms[$serviceKey]['sections'] ?? []);

            $this->actingAs($admin)
                ->followingRedirects()
                ->post(route('prospecting-forms.resolve'), [
                    'mode' => 'manual',
                    'chamber_slug' => 'developpement',
                    'service_key' => $serviceKey,
                ])
                ->assertOk()
                ->assertSee($forms[$serviceKey]['title'])
                ->assertSee($forms[$serviceKey]['description']);

            $this->post(route('prospecting-forms.store'), [
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
                'answers' => $this->requiredAnswers('developpement', $serviceKey),
            ])->assertRedirect(route('prospecting-forms.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('public_service_prospects', [
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
                'service_name' => $service['label'],
                'created_by' => $admin->id,
            ]);
        }
    }

    public function test_support_informatique_form_matches_the_client_brief_and_saves_validated_answers(): void
    {
        $serviceKey = 'developpement_assistance_technique_et_support_informatique';
        $answers = $this->requiredAnswers('developpement', $serviceKey);
        $this->assertSame(
            ['client', 'email', 'phone', 'support_type', 'need', 'urgency', 'consent'],
            array_keys($answers)
        );

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Formulaire de prospection — Assistance technique et support informatique')
            ->assertSee('01. Informations sur le client')
            ->assertSee('09. Validation et remarques')
            ->assertSee('Équipements et environnement informatique')
            ->assertSee('Critique — activité arrêtée')
            ->assertSee('Ne communiquez aucun mot de passe ni code confidentiel')
            ->assertSee('name="answers[website]"', false)
            ->assertSee('name="answers[consent][]"', false);

        $invalidAnswers = $answers;
        $invalidAnswers['website'] = 'adresse invalide';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.website');

        $answers = array_merge($answers, [
            'client' => 'Entreprise Exemple',
            'contact' => 'Responsable IT',
            'email' => 'support@example.com',
            'phone' => '+22670000000',
            'client_type' => 'Entreprise',
            'support_type' => 'Diagnostic et dépannage',
            'need' => 'Les postes de travail ne se connectent plus au réseau depuis ce matin.',
            'equipment' => ['Ordinateurs fixes', 'Réseau / Wi-Fi'],
            'device_count' => '12',
            'urgency' => 'Élevé — forte perturbation',
            'mode' => 'À distance',
            'consent' => ['yes'],
        ]);
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $answers,
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'service_name' => 'Assistance technique et support informatique',
        ]);

        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame(['Ordinateurs fixes', 'Réseau / Wi-Fi'], $storedAnswers['equipment']);
        $this->assertSame(['yes'], $storedAnswers['consent']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Entreprise Exemple')
            ->assertSee('Ordinateurs fixes, Réseau / Wi-Fi')
            ->assertSee('Assistance technique et support informatique');
    }

    public function test_saas_form_matches_the_provided_twelve_section_brief_and_saves_answers(): void
    {
        $serviceKey = 'developpement_creation_de_solutions_saas';
        $questionnaire = config('prospecting_forms.developpement.'.$serviceKey);
        $answers = $this->requiredAnswers('developpement', $serviceKey);
        $fields = collect($questionnaire['sections'])->flatMap(fn ($section) => $section['fields']);

        $this->assertCount(12, $questionnaire['sections']);
        $this->assertSame(
            ['client', 'email', 'phone', 'sector', 'idea', 'mvp', 'consent'],
            array_keys($answers)
        );
        $this->assertSame($fields->count(), $fields->pluck('name')->unique()->count());

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Formulaire de prospection — Création de solutions SaaS')
            ->assertSee('12. Validation et informations complémentaires')
            ->assertSee('Nombre estimé d’utilisateurs au lancement')
            ->assertSee('Budget mensuel envisagé pour l’exploitation')
            ->assertSee('secret d’API')
            ->assertSee('name="answers[consent][]"', false);

        $invalidAnswers = $answers;
        $invalidAnswers['website'] = 'url invalide';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.website');

        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => array_merge($answers, [
                'client' => 'EUNITAL SaaS',
                'email' => 'saas@example.com',
                'phone' => '+22670000000',
                'sector' => 'Éducation',
                'idea' => 'Une plateforme SaaS de gestion pour les établissements.',
                'mvp' => 'Comptes, rôles, gestion des inscriptions et tableau de bord.',
                'audience' => ['Établissements éducatifs'],
                'features' => ['Création de compte / connexion', 'Abonnements'],
                'business_model' => 'Abonnement mensuel',
                'budget' => '5 000 à 10 000 €',
                'monthly_budget' => '50 à 150 €',
                'security' => ['Chiffrement des données', 'Sauvegardes automatiques'],
                'support' => ['Formation administrateurs', 'Documentation'],
                'consent' => ['yes'],
            ]),
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'service_name' => 'Création de solutions SaaS',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame('5 000 à 10 000 €', $storedAnswers['budget']);
        $this->assertSame('50 à 150 €', $storedAnswers['monthly_budget']);
        $this->assertSame(['yes'], $storedAnswers['consent']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('EUNITAL SaaS')
            ->assertSee('5 000 à 10 000 €')
            ->assertSee('50 à 150 €');
    }

    public function test_ai_integration_form_matches_the_provided_brief_and_saves_answers(): void
    {
        $serviceKey = 'developpement_integration_de_solutions_dintelligence_artificielle';
        $questionnaire = config('prospecting_forms.developpement.'.$serviceKey);
        $answers = $this->requiredAnswers('developpement', $serviceKey);
        $fields = collect($questionnaire['sections'])->flatMap(fn ($section) => $section['fields']);

        $this->assertCount(12, $questionnaire['sections']);
        $this->assertSame(
            ['client', 'email', 'phone', 'sector', 'goal', 'consent'],
            array_keys($answers)
        );
        $this->assertSame($fields->count(), $fields->pluck('name')->unique()->count());

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Formulaire de prospection — Intégration de solutions d’intelligence artificielle')
            ->assertSee('12. Validation et informations complémentaires')
            ->assertSee('Données et connaissances disponibles')
            ->assertSee('Contrôle humain, qualité et limites')
            ->assertSee('Ne joignez pas de données personnelles')
            ->assertSee('name="answers[consent][]"', false);

        $invalidAnswers = $answers;
        $invalidAnswers['website'] = 'url invalide';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.website');

        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => array_merge($answers, [
                'client' => 'Entreprise IA Exemple',
                'email' => 'ia@example.com',
                'phone' => '+22670000000',
                'website' => 'https://example.com',
                'sector' => 'Services financiers',
                'goal' => 'Réduire le temps de traitement des demandes clients.',
                'use_cases' => ['Service client / chatbot', 'Traitement de documents'],
                'data_sources' => ['Documents PDF / Word', 'CRM / ERP'],
                'ai_types' => ['Recherche documentaire / RAG', 'Je souhaite être conseillé'],
                'security' => ['Anonymisation / pseudonymisation', 'Validation humaine obligatoire'],
                'support' => ['Formation des équipes', 'Suivi des performances'],
                'budget' => '3 000 à 7 000 €',
                'monthly_budget' => '50 à 200 €',
                'consent' => ['yes'],
            ]),
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'service_name' => 'Intégration de solutions d’intelligence artificielle',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame(['Service client / chatbot', 'Traitement de documents'], $storedAnswers['use_cases']);
        $this->assertSame('50 à 200 €', $storedAnswers['monthly_budget']);
        $this->assertSame(['yes'], $storedAnswers['consent']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Entreprise IA Exemple')
            ->assertSee('Service client / chatbot, Traitement de documents')
            ->assertSee('3 000 à 7 000 €')
            ->assertSee('50 à 200 €');
    }

    public function test_database_management_brief_matches_the_provided_twelve_section_form(): void
    {
        $serviceKey = 'developpement_gestion_de_bases_de_donnees';
        $questionnaire = config('prospecting_forms.developpement.'.$serviceKey);
        $answers = $this->requiredAnswers('developpement', $serviceKey);
        $fields = collect($questionnaire['sections'])->flatMap(fn ($section) => $section['fields']);

        $this->assertCount(12, $questionnaire['sections']);
        $this->assertSame(
            ['organisation', 'representant', 'telephone', 'email', 'besoin_principal', 'description_besoin', 'objectif'],
            array_keys($answers)
        );
        $this->assertSame($fields->count(), $fields->pluck('name')->unique()->count());

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Formulaire de brief — Création et gestion de bases de données')
            ->assertSee('10. Informations complémentaires et suivi commercial')
            ->assertSee('11. Observations techniques — réservé à EUNITAL')
            ->assertSee('12. Validation du brief')
            ->assertSee('Plus de 500 000')
            ->assertSee('Nom du prospecteur EUNITAL');

        $invalidAnswers = $answers;
        $invalidAnswers['volume_donnees'] = 'Volume inventé';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.volume_donnees');

        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => array_merge($answers, [
                'organisation' => 'Entreprise Exemple',
                'representant' => 'Aminata Exemple',
                'telephone' => '+22670000000',
                'email' => 'aminata@example.com',
                'besoin_principal' => ['Création d’une nouvelle base de données', 'Sécurisation d’une base de données'],
                'description_besoin' => 'Centraliser les données clients actuellement réparties sur plusieurs fichiers.',
                'objectif' => 'Disposer d’une base fiable avec accès par profil.',
                'types_donnees' => ['Informations sur les clients et prospects', 'Ventes et commandes'],
                'etat_donnees' => ['Oui, sous Excel', 'Oui, dans plusieurs fichiers'],
                'volume_donnees' => 'Entre 500 et 5 000',
                'fonctionnalites' => ['Rechercher rapidement des informations', 'Effectuer des sauvegardes automatiques'],
                'profils' => ['Administrateur', 'Service commercial'],
                'budget' => '300 000 à 500 000 FCFA',
                'date_entretien' => '2026-11-22',
                'statut_prospect' => 'Besoin identifié',
                'type_base_envisage' => 'Base relationnelle',
                'decision_technique' => 'Projet réalisable sous certaines conditions',
                'nom_representant_client' => 'Aminata Exemple',
            ]),
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'service_name' => 'Gestion de bases de données',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame(['Création d’une nouvelle base de données', 'Sécurisation d’une base de données'], $storedAnswers['besoin_principal']);
        $this->assertSame('Besoin identifié', $storedAnswers['statut_prospect']);
        $this->assertSame('Projet réalisable sous certaines conditions', $storedAnswers['decision_technique']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Entreprise Exemple')
            ->assertSee('Besoin identifié')
            ->assertSee('Base relationnelle')
            ->assertSee('Gestion de bases de données');
    }

    public function test_hosting_and_deployment_brief_matches_the_twelve_section_form_and_saves_answers(): void
    {
        $serviceKey = 'developpement_hebergement_et_deploiement_dapplications';
        $questionnaire = config('prospecting_forms.developpement.'.$serviceKey);
        $answers = $this->requiredAnswers('developpement', $serviceKey);
        $fields = collect($questionnaire['sections'])->flatMap(fn ($section) => $section['fields']);

        $this->assertCount(12, $questionnaire['sections']);
        $this->assertSame(
            ['nom', 'email', 'telephone', 'projet', 'type_app', 'accord'],
            array_keys($answers)
        );
        $this->assertSame($fields->count(), $fields->pluck('name')->unique()->count());

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Brief — Hébergement et déploiement d’applications')
            ->assertSee('12. Documents, accès et remarques')
            ->assertSee('Sauvegardes attendues')
            ->assertSee('Ne saisissez pas de mots de passe')
            ->assertSee('name="answers[accord][]"', false);

        $invalidAnswers = $answers;
        $invalidAnswers['site'] = 'url invalide';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.site');

        $withoutConsent = $answers;
        unset($withoutConsent['accord']);
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $withoutConsent,
        ])->assertSessionHasErrors('answers.accord');

        $answers = array_merge($answers, [
            'nom' => 'Aminata Exemple',
            'email' => 'aminata@example.com',
            'telephone' => '+22670000000',
            'projet' => 'Portail client',
            'type_app' => 'Application web',
            'site' => 'https://example.com',
            'url_actuelle' => 'https://app.example.com',
            'objectifs' => ['Mise en ligne initiale', 'Automatisation du déploiement'],
            'bdd' => 'PostgreSQL',
            'date_mise_en_ligne' => '2026-12-01',
            'documents' => ['Documentation technique', 'Dépôt de code'],
            'accord' => ['on'],
        ]);
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $answers,
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'service_name' => 'Hébergement et déploiement d’applications',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame(['Mise en ligne initiale', 'Automatisation du déploiement'], $storedAnswers['objectifs']);
        $this->assertSame(['on'], $storedAnswers['accord']);
        $this->assertSame('https://app.example.com', $storedAnswers['url_actuelle']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Aminata Exemple')
            ->assertSee('Application web')
            ->assertSee('Mise en ligne initiale, Automatisation du déploiement')
            ->assertSee('Hébergement et déploiement d’applications');
    }

    public function test_software_maintenance_brief_matches_the_twelve_section_form_and_saves_answers(): void
    {
        $serviceKey = 'developpement_maintenance_et_evolution_de_logiciels';
        $questionnaire = config('prospecting_forms.developpement.'.$serviceKey);
        $answers = $this->requiredAnswers('developpement', $serviceKey);
        $fields = collect($questionnaire['sections'])->flatMap(fn ($section) => $section['fields']);

        $this->assertCount(12, $questionnaire['sections']);
        $this->assertSame(
            ['nom', 'email', 'telephone', 'logiciel', 'type', 'accord'],
            array_keys($answers)
        );
        $this->assertSame($fields->count(), $fields->pluck('name')->unique()->count());

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Brief — Maintenance et évolution de logiciels')
            ->assertSee('12. Documents et remarques complémentaires')
            ->assertSee('Quelles sont les trois améliorations les plus importantes ?')
            ->assertSee('Ne transmettez aucun mot de passe')
            ->assertSee('name="answers[accord][]"', false);

        $invalidAnswers = $answers;
        $invalidAnswers['site'] = 'url invalide';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.site');

        $withoutConsent = $answers;
        unset($withoutConsent['accord']);
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $withoutConsent,
        ])->assertSessionHasErrors('answers.accord');

        $answers = array_merge($answers, [
            'nom' => 'Aminata Exemple',
            'email' => 'aminata@example.com',
            'telephone' => '+22670000000',
            'logiciel' => 'Portail client',
            'type' => 'Application web',
            'site' => 'https://example.com',
            'problemes' => ['Bugs et erreurs récurrentes', 'Lenteurs / problèmes de performances'],
            'maintenance' => ['Maintenance corrective', 'Sécurité et correctifs'],
            'nouvelles_fonctionnalites' => 'Ajouter un tableau de bord de suivi.',
            'date_demarrage' => '2026-12-01',
            'documents' => ['Documentation technique', 'Liste des anomalies'],
            'accord' => ['on'],
        ]);
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $answers,
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'service_name' => 'Maintenance et évolution de logiciels',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame(['Bugs et erreurs récurrentes', 'Lenteurs / problèmes de performances'], $storedAnswers['problemes']);
        $this->assertSame(['Maintenance corrective', 'Sécurité et correctifs'], $storedAnswers['maintenance']);
        $this->assertSame(['on'], $storedAnswers['accord']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Aminata Exemple')
            ->assertSee('Application web')
            ->assertSee('Bugs et erreurs récurrentes, Lenteurs / problèmes de performances')
            ->assertSee('Maintenance et évolution de logiciels');
    }

    public function test_automation_processes_brief_matches_the_twelve_section_form_and_saves_answers(): void
    {
        $serviceKey = 'developpement_automatisation_des_processus_dentreprise';
        $questionnaire = config('prospecting_forms.developpement.'.$serviceKey);
        $answers = $this->requiredAnswers('developpement', $serviceKey);
        $fields = collect($questionnaire['sections'])->flatMap(fn ($section) => $section['fields']);

        $this->assertCount(12, $questionnaire['sections']);
        $this->assertSame($fields->count(), $fields->pluck('name')->unique()->count());
        $this->assertSame(['nom', 'entreprise', 'email', 'telephone', 'accord'], array_keys($answers));

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Brief — Automatisation des processus d’entreprise')
            ->assertSee('12. Planning, budget et validation')
            ->assertSee('Processus à automatiser')
            ->assertSee('Règles métier')
            ->assertSee('Enregistrer le brouillon')
            ->assertSee('Charger le brouillon')
            ->assertSee('Effacer')
            ->assertSee('name="answers[accord][]"', false);

        $invalidAnswers = $answers;
        $invalidAnswers['email'] = 'adresse invalide';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.email');

        $withoutConsent = $answers;
        unset($withoutConsent['accord']);
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $withoutConsent,
        ])->assertSessionHasErrors('answers.accord');

        $answers = array_merge($answers, [
            'nom' => 'Aminata Exemple',
            'entreprise' => 'Entreprise Exemple',
            'email' => 'aminata@example.com',
            'telephone' => '+22670000000',
            'objectifs' => ['Réduire les tâches manuelles et répétitives', 'Gagner du temps et améliorer la productivité'],
            'processus' => ['Gestion commerciale et CRM', 'Prospection et suivi des prospects'],
            'frequence' => 'Quotidienne',
            'declencheur' => 'Soumission d’un formulaire',
            'outils' => ['CRM', 'Excel / Google Sheets / tableurs'],
            'types_automatisation' => ['Workflows et circuits de validation', 'Connexion entre applications'],
            'autonomie' => 'Automatique avec validation des étapes sensibles',
            'indicateur' => 'Temps de traitement',
            'date_demarrage' => '2026-12-01',
            'budget' => '1 500 à 3 000 €',
            'accord' => ['on'],
        ]);
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $answers,
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'service_name' => 'Automatisation des processus d’entreprise',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame(['CRM', 'Excel / Google Sheets / tableurs'], $storedAnswers['outils']);
        $this->assertSame(['on'], $storedAnswers['accord']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Entreprise Exemple')
            ->assertSee('CRM, Excel / Google Sheets / tableurs')
            ->assertSee('Automatisation des processus d’entreprise');
    }

    public function test_custom_software_development_brief_matches_the_thirteen_section_form_and_saves_answers(): void
    {
        $serviceKey = 'developpement_developpement_de_logiciels_sur_mesure';
        $questionnaire = config('prospecting_forms.developpement.'.$serviceKey);
        $answers = $this->requiredAnswers('developpement', $serviceKey);
        $fields = collect($questionnaire['sections'])->flatMap(fn ($section) => $section['fields']);

        $this->assertCount(13, $questionnaire['sections']);
        $this->assertSame($fields->count(), $fields->pluck('name')->unique()->count());
        $this->assertSame(
            ['organisation', 'representant', 'telephone', 'email', 'besoin_principal', 'probleme_actuel', 'processus_actuel', 'nom_projet', 'type_logiciel', 'description_logiciel', 'fonctionnalites_logiciel'],
            array_keys($answers)
        );

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Brief — Développement de logiciels sur mesure')
            ->assertSee('13. Validation du brief')
            ->assertSee('12. Analyse technique — réservé à EUNITAL')
            ->assertSee('Quelles sont les fonctionnalités prioritaires ?')
            ->assertSee('Ne transmettez aucun mot de passe');

        $invalidAnswers = $answers;
        $invalidAnswers['email'] = 'adresse invalide';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.email');

        $answers = array_merge($answers, [
            'organisation' => 'Entreprise Exemple',
            'representant' => 'Aminata Exemple',
            'fonction_representant' => 'Directrice des opérations',
            'secteur' => 'Services numériques',
            'telephone' => '+22670000000',
            'email' => 'aminata@example.com',
            'localisation' => 'Ouagadougou, Burkina Faso',
            'besoin_principal' => ['Créer un nouveau logiciel métier', 'Automatiser des processus de travail'],
            'probleme_actuel' => 'Le suivi est actuellement réalisé sur plusieurs feuilles de calcul.',
            'processus_actuel' => 'Les équipes saisissent les demandes manuellement.',
            'objectifs_projet' => ['Centraliser les informations', 'Réduire les tâches manuelles'],
            'nom_projet' => 'Portail de gestion des demandes',
            'type_logiciel' => ['Application web', 'Application mobile Android'],
            'description_logiciel' => 'Un portail sécurisé pour gérer les demandes clients.',
            'fonctionnalites_logiciel' => 'Création, affectation, suivi et reporting des demandes.',
            'nombre_utilisateurs' => '21 à 50 personnes',
            'systemes_a_connecter' => ['CRM et plateforme de vente'],
            'dispositifs_securite' => ['Gestion des permissions', 'Sauvegardes automatiques'],
            'budget_previsionnel' => '500 000 à 1 000 000 FCFA',
            'statut_prospect' => 'Besoin identifié',
            'decision_technique' => 'Projet réalisable sous certaines conditions',
            'date_validation' => '2026-11-22',
        ]);
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $answers,
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'service_name' => 'Développement de logiciels sur mesure',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame(['Application web', 'Application mobile Android'], $storedAnswers['type_logiciel']);
        $this->assertSame('Besoin identifié', $storedAnswers['statut_prospect']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Entreprise Exemple')
            ->assertSee('Portail de gestion des demandes')
            ->assertSee('Créer un nouveau logiciel métier, Automatiser des processus de travail')
            ->assertSee('Projet réalisable sous certaines conditions');
    }

    public function test_it_solutions_integration_brief_matches_the_thirteen_section_form_and_saves_answers(): void
    {
        $serviceKey = 'developpement_integration_de_solutions_informatiques';
        $questionnaire = config('prospecting_forms.developpement.'.$serviceKey);
        $answers = $this->requiredAnswers('developpement', $serviceKey);
        $fields = collect($questionnaire['sections'])->flatMap(fn ($section) => $section['fields']);

        $this->assertCount(13, $questionnaire['sections']);
        $this->assertSame(
            ['entreprise', 'representant', 'telephone', 'email', 'besoin_principal'],
            array_keys($answers)
        );
        $this->assertSame($fields->count(), $fields->pluck('name')->unique()->count());

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'developpement',
                'service_key' => $serviceKey,
            ])
            ->assertOk()
            ->assertSee('Brief — Intégration de solutions informatiques')
            ->assertSee('13. Validation du brief')
            ->assertSee('12. Analyse technique — réservé à EUNITAL')
            ->assertSee('Quels types de solutions souhaitez-vous intégrer ?')
            ->assertSee('Nom du prospecteur');

        $invalidAnswers = $answers;
        $invalidAnswers['email'] = 'adresse invalide';
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $invalidAnswers,
        ])->assertSessionHasErrors('answers.email');

        $answers = array_merge($answers, [
            'entreprise' => 'Entreprise Exemple',
            'representant' => 'Aminata Exemple',
            'fonction' => 'Directrice des opérations',
            'telephone' => '+22670000000',
            'email' => 'aminata@example.com',
            'besoin_principal' => ['Connexion de plusieurs logiciels entre eux', 'Automatisation des processus de travail'],
            'objectifs_integration' => ['Améliorer la productivité', 'Centraliser les informations'],
            'types_solutions' => ['ERP (gestion intégrée de l’entreprise)', 'CRM (gestion de la relation client)'],
            'solution_identifiee' => 'Oui',
            'systemes_exploitation' => ['Windows', 'Linux'],
            'systemes_a_connecter' => ['CRM et plateforme de vente', 'ERP et base de données'],
            'activites_a_automatiser' => ['Gestion des commandes', 'Synchronisation des données'],
            'dispositifs_securite' => ['Authentification à plusieurs facteurs', 'Sauvegardes automatiques'],
            'date_limite' => '2026-12-01',
            'statut_prospect' => 'Besoin identifié',
            'decision_technique' => 'Projet réalisable sous certaines conditions',
            'date_validation' => '2026-11-22',
        ]);
        $this->post(route('prospecting-forms.store'), [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'answers' => $answers,
        ])->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('public_service_prospects', [
            'chamber_slug' => 'developpement',
            'service_key' => $serviceKey,
            'service_name' => 'Intégration de solutions informatiques',
        ]);
        $prospect = DB::table('public_service_prospects')->where('service_key', $serviceKey)->first();
        $storedAnswers = json_decode($prospect->answers, true);
        $this->assertSame(['CRM et plateforme de vente', 'ERP et base de données'], $storedAnswers['systemes_a_connecter']);
        $this->assertSame(['Authentification à plusieurs facteurs', 'Sauvegardes automatiques'], $storedAnswers['dispositifs_securite']);
        $this->assertSame('Besoin identifié', $storedAnswers['statut_prospect']);

        $this->get(route('prospecting-forms.show', $prospect->id))
            ->assertOk()
            ->assertSee('Entreprise Exemple')
            ->assertSee('Connexion de plusieurs logiciels entre eux, Automatisation des processus de travail')
            ->assertSee('Projet réalisable sous certaines conditions')
            ->assertSee('Intégration de solutions informatiques');
    }

    public function test_unconfigured_service_displays_a_clear_notice_instead_of_an_unrelated_questionnaire(): void
    {
        $marketingServiceKey = array_key_first(config('public_services.chambers.marketing.services'));

        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'marketing',
                'service_key' => $marketingServiceKey,
            ])
            ->assertOk()
            ->assertSee('Le formulaire de prospection de cette prestation n’est pas encore configuré.')
            ->assertDontSee('Questionnaire — Brief de création de logo');
    }

    public function test_validation_error_keeps_the_agent_on_the_selected_questionnaire(): void
    {
        $this->actingAs($this->administrator())
            ->followingRedirects()
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'manual',
                'chamber_slug' => 'graphisme',
                'service_key' => 'graphisme_creation_de_logos',
            ])
            ->assertOk();

        $this->from(route('prospecting-forms.index'))
            ->post(route('prospecting-forms.store'), [
                'chamber_slug' => 'graphisme',
                'service_key' => 'graphisme_creation_de_logos',
                'answers' => ['nom' => 'Entreprise incomplète'],
            ])
            ->assertRedirect(route('prospecting-forms.index'))
            ->assertSessionHasErrors(['answers.secteur', 'answers.email']);

        $this->get(route('prospecting-forms.index'))
            ->assertOk()
            ->assertSee('Questionnaire — Brief de création de logo')
            ->assertSee('Entreprise incomplète');
    }

    public function test_reference_cannot_be_used_to_open_or_save_the_wrong_service_form(): void
    {
        $this->logoRequest();

        $this->actingAs($this->administrator())
            ->post(route('prospecting-forms.resolve'), [
                'mode' => 'reference',
                'reference' => 'SR-999999',
            ])->assertSessionHasErrors('reference');

        $this->post(route('prospecting-forms.store'), [
            'reference' => 'SR-000001',
            'chamber_slug' => 'graphisme',
            'service_key' => 'graphisme_creation_de_flyers',
            'answers' => $this->logoAnswers(),
        ])->assertSessionHasErrors('reference');

        $this->assertDatabaseCount('public_service_prospects', 0);
    }

    public function test_admin_can_download_an_archived_pdf_and_see_which_agent_generated_it(): void
    {
        Storage::fake('prospecting_private');
        $agent = $this->administrator();
        $prospectId = $this->savedLogoProspect($agent->id);

        $response = $this->actingAs($agent)->get(route('prospecting-forms.pdf', $prospectId));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $download = DB::table('prospect_pdf_downloads')->first();
        $this->assertNotNull($download);
        $this->assertSame($agent->id, (int) $download->downloaded_by);
        $this->assertStringStartsWith('%PDF', Storage::disk('prospecting_private')->get($download->file_path));

        $this->get(route('prospecting-forms.pdf-archive', $download->id))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->get(route('prospecting-forms.index'))
            ->assertOk()
            ->assertSee('Formulaires soumis')
            ->assertSee('Dossiers PDF téléchargés')
            ->assertSee($agent->name)
            ->assertSee($download->file_name)
            ->assertSee(route('chambers.index'))
            ->assertSee(route('public-services.index'))
            ->assertSee('href="#service-selector"', false)
            ->assertSee('href="#submitted-prospects"', false)
            ->assertSee('href="#pdf-archives"', false);

        $this->assertDatabaseCount('prospect_pdf_downloads', 1);
    }
}
