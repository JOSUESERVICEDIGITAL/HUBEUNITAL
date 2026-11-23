<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MissingChamberArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ChamberSeeder::class);

        $author = User::whereIn('role', ['superadmin', 'admin'])->first() ?? User::first();
        $author ??= User::firstOrCreate(['email' => 'redaction@eunital.com'], [
            'name' => 'Rédaction EUNITAL',
            'password' => Str::random(64),
            'role' => 'author',
        ]);

        foreach ($this->chambers() as $slug => $chamber) {
            $category = Category::where('slug', $slug)->firstOrFail();
            $articleSlugs = array_column($chamber['articles'], 'slug');
            $hasOtherPublishedArticle = Article::where('category_id', $category->id)
                ->where('isActive', true)
                ->whereNotIn('slug', $articleSlugs)
                ->exists();

            if ($hasOtherPublishedArticle) {
                continue;
            }

            foreach ($chamber['articles'] as $article) {
                if (! is_file(public_path($article['image']))) {
                    throw new \RuntimeException('Image locale introuvable : '.$article['image']);
                }

                $existingArticle = Article::where('slug', $article['slug'])
                    ->orWhere('title', $article['title'])->first();
                if ($existingArticle) {
                    if ($existingArticle->category_id == $category->id && ! $existingArticle->image) {
                        $existingArticle->update(['image' => $article['image']]);
                    }
                    continue;
                }

                Article::firstOrCreate(['slug' => $article['slug']], [
                    'title' => $article['title'],
                    'category_id' => $category->id,
                    'author_id' => $author->id,
                    'image' => $article['image'],
                    'isActive' => true,
                    'isComment' => true,
                    'isSharable' => true,
                    'description' => $article['description'].$this->requestInstructions($chamber['name']),
                ]);
            }
        }
    }

    private function requestInstructions(string $chamberName): string
    {
        return "\n\nCOMMENT DEMANDER CE SERVICE\n"
            .'Sur le site EUNITAL, cliquez sur le bouton flottant à gauche « Nos services pour '.$chamberName.' », '
            .'choisissez la prestation, puis renseignez votre nom, votre email, votre téléphone, votre organisation si nécessaire '
            .'et une description précise du besoin. Vous pouvez y indiquer le budget ou les délais souhaités. '
            .'Après l’envoi, vous recevez une confirmation et un agent de la chambre vous recontacte rapidement '
            .'pour préciser la demande et les prochaines étapes.';
    }

    private function chambers(): array
    {
        return [
            'innovation' => [
                'name' => 'Innovation',
                'articles' => [
                    [
                        'slug' => 'chambre-innovation-eunital-idees-et-solutions',
                        'title' => 'Chambre Innovation EUNITAL : transformer une idée en solution',
                        'image' => 'fronto/img/news-500x280-6.jpg',
                        'description' => "La Chambre Innovation accompagne les entreprises, institutions et porteurs de projets qui veulent étudier un besoin, concevoir une idée nouvelle ou tester une solution. Elle relie l’analyse de l’opportunité, la recherche de pistes, le prototypage et l’évaluation des résultats.\n\nSES SERVICES\nAccompagnement de porteurs d’idées et de startups, études d’opportunité, incubation, hackathons, recherche et développement, veille technologique, preuves de concept et expérimentation de nouvelles technologies.\n\nCE QUE VOUS OBTENEZ\nUn besoin mieux défini, des options comparées, un prototype ou une preuve de concept et des retours permettant de décider des prochaines étapes. La Chambre Innovation travaille avec les autres chambres lorsque le projet nécessite une étude technique, un développement logiciel, un design ou une stratégie de commercialisation.",
                    ],
                    [
                        'slug' => 'chambre-innovation-eunital-etude-prototypage',
                        'title' => 'Étude, prototypage et expérimentation avec la Chambre Innovation',
                        'image' => 'fronto/img/news-300x300-2.jpg',
                        'description' => "La Chambre Innovation organise une démarche d’expérimentation pour réduire l’incertitude avant un investissement important. Elle aide à vérifier si une solution répond réellement au besoin et si elle peut être mise en œuvre.\n\nLES ÉTAPES\nDécrire le problème et les personnes concernées, rechercher les solutions existantes, préciser les contraintes et les critères de réussite, réaliser un prototype adapté, recueillir des retours volontaires, puis présenter les résultats et recommandations.\n\nLes prestations peuvent concerner l’innovation numérique, l’amélioration d’un service, une nouvelle offre, la transformation digitale ou un projet de recherche et développement. Une expérimentation ne garantit pas la réussite commerciale : elle fournit des éléments concrets pour décider de poursuivre, d’ajuster ou d’arrêter le projet.",
                    ],
                ],
            ],
            'developpement' => [
                'name' => 'Développement',
                'articles' => [
                    [
                        'slug' => 'chambre-developpement-eunital-conception-logicielle',
                        'title' => 'Chambre Développement EUNITAL : des solutions numériques sur mesure',
                        'image' => 'fronto/img/news-500x280-5.jpg',
                        'description' => "La Chambre Développement conçoit, réalise et fait évoluer des logiciels et services numériques selon les besoins d’un client. Elle intervient sur les sites web, plateformes, applications mobiles, logiciels de gestion, API, tableaux de bord et solutions SaaS.\n\nUN SERVICE ADAPTÉ AU BESOIN\nLe cadrage précise les utilisateurs, les fonctionnalités prioritaires, les contraintes techniques, les échéances et les critères de validation. L’équipe propose ensuite une architecture et un périmètre de réalisation compréhensibles avant le démarrage du développement.\n\nLes services couvrent aussi l’intégration de paiements ou de réservations, l’automatisation des processus, les bases de données, l’hébergement, le déploiement et l’assistance technique.",
                    ],
                    [
                        'slug' => 'chambre-developpement-eunital-maintenance-et-evolution',
                        'title' => 'Développement, maintenance et évolution de vos outils numériques',
                        'image' => 'fronto/img/news-300x300-4.jpg',
                        'description' => "La Chambre Développement accompagne un produit numérique au-delà de sa première mise en ligne. Elle peut analyser un outil existant, corriger des dysfonctionnements, ajouter des fonctions, améliorer les performances ou préparer une migration.\n\nPOUR BIEN DÉMARRER\nIndiquez le résultat attendu, les utilisateurs concernés, les fonctions indispensables, les appareils ou plateformes visés, les systèmes déjà utilisés et les délais. Pour une évolution, décrivez le problème et joignez les informations non confidentielles qui aideront au diagnostic.\n\nChaque intervention est cadrée selon son périmètre. Les tests, la validation par le client, le déploiement et les modalités de maintenance sont précisés avant la livraison.",
                    ],
                ],
            ],
            'formation' => [
                'name' => 'Formation',
                'articles' => [
                    [
                        'slug' => 'chambre-formation-eunital-competences-professionnelles',
                        'title' => 'Chambre Formation EUNITAL : développer des compétences utiles',
                        'image' => 'fronto/img/news-700x435-1.jpg',
                        'description' => "La Chambre Formation conçoit et organise des apprentissages destinés aux particuliers, aux équipes et aux organisations. Les formations associent des objectifs précis, des contenus structurés et des exercices adaptés au niveau des participants.\n\nDOMAINES DE FORMATION\nInformatique et bureautique, programmation, développement web et mobile, cybersécurité, intelligence artificielle, graphisme, montage vidéo, marketing digital, gestion d’entreprise, finance, ressources humaines, gestion de projets et communication professionnelle.\n\nLes formats peuvent être individuels ou collectifs, en présentiel ou à distance, sous forme de parcours, d’ateliers ou de séminaires. Les objectifs, la durée, le niveau de départ et les modalités d’évaluation sont définis selon le besoin.",
                    ],
                    [
                        'slug' => 'chambre-formation-eunital-organiser-votre-parcours',
                        'title' => 'Choisir et organiser une formation adaptée à votre équipe',
                        'image' => 'fronto/img/news-500x280-3.jpg',
                        'description' => "La Chambre Formation aide à préciser les compétences à acquérir et à choisir un format réaliste. Elle peut préparer une formation en entreprise, un parcours ouvert aux apprenants, un atelier pratique ou une préparation à une certification professionnelle.\n\nINFORMATIONS À PRÉCISER\nLe thème souhaité, le nombre de participants, leur niveau, les résultats attendus, la période envisagée, le lieu ou le format à distance et les contraintes particulières. Ces éléments permettent d’étudier le programme, la durée et les modalités d’accompagnement.\n\nÀ l’issue du cadrage, l’équipe peut proposer un programme et les conditions de réalisation. Une attestation ou une évaluation dépend du parcours et des modalités convenues.",
                    ],
                ],
            ],
            'finance' => [
                'name' => 'Finance',
                'articles' => [
                    [
                        'slug' => 'chambre-finance-eunital-pilotage-financier',
                        'title' => 'Chambre Finance EUNITAL : clarifier et piloter vos chiffres',
                        'image' => 'fronto/img/news-500x280-6.jpg',
                        'description' => "La Chambre Finance accompagne les entrepreneurs et organisations dans la préparation et le suivi de leurs informations financières. Son rôle est de rendre les hypothèses, les besoins et les indicateurs plus lisibles pour faciliter la prise de décision.\n\nPRESTATIONS\nÉlaboration de budgets prévisionnels, plans financiers, prévisions de trésorerie, analyses de coûts et de marges, études de rentabilité, tableaux de bord et suivi budgétaire de projets.\n\nUN CADRAGE TRANSPARENT\nL’analyse s’appuie sur les informations communiquées par le client : activité, charges, revenus, besoins et hypothèses. Les résultats sont des estimations dépendantes de ces données et ne constituent pas une garantie de financement ou de performance.",
                    ],
                    [
                        'slug' => 'chambre-finance-eunital-dossier-de-financement',
                        'title' => 'Préparer un dossier financier et étudier un besoin de financement',
                        'image' => 'fronto/img/news-300x300-2.jpg',
                        'description' => "La Chambre Finance aide à structurer un besoin de financement et à préparer les éléments financiers d’un projet. Elle peut contribuer à un business plan, à un dossier de crédit ou d’investissement et à la présentation des prévisions.\n\nPOUR ÉTUDIER LE DOSSIER\nDécrivez le projet, son état d’avancement, le montant recherché, l’usage prévu des fonds, les ressources déjà disponibles, les échéances et les pièces existantes. L’équipe pourra préciser les informations nécessaires et les livrables possibles.\n\nLa décision d’accorder un prêt ou un investissement appartient toujours à l’organisme financier ou à l’investisseur concerné. La Chambre Finance accompagne la préparation et l’analyse, sans promettre l’obtention des fonds.",
                    ],
                ],
            ],
            'cybersecurite' => [
                'name' => 'Cybersécurité',
                'articles' => [
                    [
                        'slug' => 'chambre-cybersecurite-eunital-protection-systemes',
                        'title' => 'Chambre Cybersécurité EUNITAL : protéger vos systèmes et vos données',
                        'image' => 'fronto/img/news-500x280-5.jpg',
                        'description' => "La Chambre Cybersécurité aide les organisations à réduire les risques qui touchent leurs comptes, leurs applications, leurs réseaux et leurs données. Elle privilégie la prévention, le diagnostic et des recommandations compréhensibles par les responsables concernés.\n\nDOMAINES D’INTERVENTION\nAudit de sécurité, diagnostic de vulnérabilités, sécurisation de sites, applications et serveurs, configuration de protections réseau, sauvegardes, politiques de sécurité et sensibilisation des équipes.\n\nTout test technique doit porter sur un périmètre appartenant au demandeur ou pour lequel il dispose d’une autorisation explicite. Le périmètre et les modalités sont définis avant l’intervention.",
                    ],
                    [
                        'slug' => 'chambre-cybersecurite-eunital-prevention-et-incident',
                        'title' => 'Prévention et réponse aux incidents avec la Chambre Cybersécurité',
                        'image' => 'fronto/img/news-300x300-4.jpg',
                        'description' => "La Chambre Cybersécurité peut aider à préparer une organisation avant un incident et à structurer les premières actions après un événement suspect. Les services incluent la sensibilisation, l’analyse du contexte, les recommandations de protection et l’orientation vers les mesures correctives adaptées.\n\nEN CAS DE SUSPICION\nDécrivez les faits observés, le moment où ils ont commencé, les systèmes concernés et les mesures déjà prises. Ne transmettez pas de mots de passe, de codes secrets ou de données personnelles sensibles dans le formulaire public.\n\nL’intervention est cadrée selon les accès et autorisations disponibles. L’équipe convient avec le demandeur des prochaines étapes et ne peut garantir qu’aucun incident ne se produira.",
                    ],
                ],
            ],
            'strategie' => [
                'name' => 'DG et Partenariats',
                'articles' => [
                    [
                        'slug' => 'chambre-partenariats-eunital-relations-strategiques',
                        'title' => 'DG et Partenariats EUNITAL : construire des collaborations utiles',
                        'image' => 'fronto/img/news-500x280-6.jpg',
                        'description' => "La chambre DG et Partenariats développe les relations institutionnelles, professionnelles et économiques d’EUNITAL. Elle facilite les mises en relation et prépare des collaborations alignées sur les objectifs, les responsabilités et les intérêts des parties.\n\nSES SERVICES\nRecherche de partenaires techniques ou financiers, rencontres B2B et B2G, représentation institutionnelle, missions économiques, développement de réseaux professionnels, dossiers de partenariat et accompagnement à l’internationalisation.\n\nChaque collaboration est étudiée selon son objet, les interlocuteurs concernés, les contributions attendues et les étapes de validation. Une mise en relation ne garantit pas la conclusion d’un partenariat.",
                    ],
                    [
                        'slug' => 'chambre-partenariats-eunital-dossiers-et-rencontres',
                        'title' => 'Dossiers de partenariat, rencontres professionnelles et représentation',
                        'image' => 'fronto/img/news-300x300-2.jpg',
                        'description' => "La chambre DG et Partenariats peut aider à préparer une approche structurée d’un partenaire, d’une institution, d’un sponsor ou d’un investisseur. Elle contribue à clarifier la proposition, les objectifs communs, les apports de chaque partie et les suites souhaitées.\n\nPOUR PRÉPARER UNE DEMANDE\nPrésentez votre organisation, le partenaire visé si vous le connaissez, l’objet de la collaboration, les résultats attendus, le calendrier et les documents déjà disponibles. Pour une rencontre, indiquez les participants et les sujets à traiter.\n\nL’équipe étudie la demande et vous contacte pour préciser le format de l’accompagnement, les disponibilités et les validations nécessaires.",
                    ],
                ],
            ],
            'secretariat' => [
                'name' => 'Secrétariat',
                'articles' => [
                    [
                        'slug' => 'chambre-secretariat-eunital-services-administratifs',
                        'title' => 'Chambre Secrétariat EUNITAL : vos documents administratifs bien préparés',
                        'image' => 'fronto/img/news-500x280-5.jpg',
                        'description' => "La Chambre Secrétariat fournit des services administratifs, bureautiques et documentaires aux particuliers, entrepreneurs et organisations. Elle aide à produire des documents clairs, correctement présentés et adaptés à l’usage demandé.\n\nPRESTATIONS DISPONIBLES\nSaisie et correction de documents, rédaction de correspondances, mise en page Word et PDF, création de CV et lettres de motivation, constitution de dossiers, assistance à des démarches administratives en ligne et transcription audio en texte.\n\nPour les documents confidentiels ou contenant des données personnelles, transmettez uniquement les éléments nécessaires et convenez avec l’équipe du mode de transfert approprié.",
                    ],
                    [
                        'slug' => 'chambre-secretariat-eunital-reprographie-et-numerisation',
                        'title' => 'Numérisation, impression et mise en forme avec la Chambre Secrétariat',
                        'image' => 'fronto/img/news-300x300-4.jpg',
                        'description' => "La Chambre Secrétariat organise la préparation matérielle et numérique de vos documents. Ses services comprennent la numérisation, la photocopie, l’impression couleur ou noir et blanc, la plastification, la reliure et la mise en forme de mémoires ou rapports.\n\nPRÉCISER VOTRE BESOIN\nIndiquez le type de document, le nombre de pages ou d’exemplaires, le format, la couleur, le délai souhaité et la finition attendue. Pour une rédaction ou une correction, expliquez le public visé et le ton recherché.\n\nL’équipe confirme la faisabilité, les éléments à fournir et les modalités de réalisation avant de commencer.",
                    ],
                ],
            ],
        ];
    }
}
