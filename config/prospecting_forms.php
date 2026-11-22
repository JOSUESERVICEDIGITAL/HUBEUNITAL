<?php

$buildGraphicBrief = static function (string $service, array $sections, array $deliverableOptions): array {
    return [
        'title' => 'Questionnaire — Brief de '.$service,
        'description' => 'Recueil des besoins, contraintes et livrables pour la prestation de '.$service.'.',
        'sections' => array_merge([
            [
                'title' => '1. Informations sur le client',
                'fields' => [
                    ['name' => 'organisation', 'label' => 'Nom de l’entreprise / organisation', 'type' => 'text', 'required' => true],
                    ['name' => 'responsable', 'label' => 'Nom du responsable', 'type' => 'text', 'required' => true],
                    ['name' => 'fonction', 'label' => 'Fonction', 'type' => 'text'],
                    ['name' => 'secteur', 'label' => 'Secteur d’activité', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'telephone', 'label' => 'Téléphone / WhatsApp', 'type' => 'tel', 'required' => true],
                    ['name' => 'localisation', 'label' => 'Ville et pays', 'type' => 'text'],
                    ['name' => 'presence_web', 'label' => 'Site web / réseaux sociaux', 'type' => 'text'],
                ],
            ],
        ], $sections, [
            [
                'title' => 'Identité visuelle et direction artistique',
                'fields' => [
                    ['name' => 'logo', 'label' => 'Disposez-vous d’un logo ?', 'type' => 'select', 'options' => ['Oui, fichier disponible', 'Oui, à récupérer sur le web', 'Non', 'Logo à créer séparément']],
                    ['name' => 'charte', 'label' => 'Disposez-vous d’une charte graphique ?', 'type' => 'select', 'options' => ['Oui, à respecter', 'Non', 'Partiellement', 'À définir']],
                    ['name' => 'couleurs', 'label' => 'Couleurs à respecter ou à privilégier', 'type' => 'text'],
                    ['name' => 'style', 'label' => 'Style souhaité', 'type' => 'select', 'options' => ['Moderne', 'Minimaliste', 'Élégant / premium', 'Audacieux / impactant', 'Institutionnel', 'Artistique / illustré', 'Naturel', 'À proposer par EUNITAL']],
                    ['name' => 'ambiance', 'label' => 'Ambiance ou émotion à transmettre', 'type' => 'textarea', 'full' => true],
                    ['name' => 'references', 'label' => 'Liens vers des références ou inspirations', 'type' => 'textarea', 'full' => true],
                    ['name' => 'a_eviter', 'label' => 'Éléments, styles ou couleurs à éviter', 'type' => 'textarea', 'full' => true],
                ],
            ],
            [
                'title' => 'Formats, fichiers et diffusion',
                'fields' => [
                    ['name' => 'livrables', 'label' => 'Livrables souhaités', 'type' => 'checkboxes', 'options' => $deliverableOptions, 'full' => true],
                    ['name' => 'formats', 'label' => 'Formats de fichiers souhaités', 'type' => 'checkboxes', 'options' => ['PDF', 'PNG', 'JPG / JPEG', 'SVG', 'Fichier source modifiable', 'À recommander par EUNITAL'], 'full' => true],
                    ['name' => 'supports', 'label' => 'Supports et canaux d’utilisation', 'type' => 'checkboxes', 'options' => ['Impression papier', 'Site web', 'Réseaux sociaux', 'WhatsApp', 'Email / newsletter', 'Affichage extérieur', 'Emballage / produit', 'Autre'], 'full' => true],
                    ['name' => 'fichiers', 'label' => 'Liens de téléchargement ou précisions sur les fichiers fournis', 'type' => 'textarea', 'full' => true],
                ],
            ],
            [
                'title' => 'Planning, budget et validation',
                'fields' => [
                    ['name' => 'date_presentation', 'label' => 'Date de première présentation souhaitée', 'type' => 'date'],
                    ['name' => 'date_livraison', 'label' => 'Date de livraison finale souhaitée', 'type' => 'date', 'required' => true],
                    ['name' => 'budget', 'label' => 'Budget estimatif', 'type' => 'text', 'placeholder' => 'Montant et devise'],
                    ['name' => 'propositions', 'label' => 'Nombre de propositions souhaitées', 'type' => 'select', 'options' => ['À recommander par EUNITAL', '1 proposition', '2 propositions', '3 propositions']],
                    ['name' => 'retouches', 'label' => 'Nombre de séries de modifications attendues', 'type' => 'select', 'options' => ['À définir dans le devis', '1 série', '2 séries', '3 séries']],
                    ['name' => 'decideur', 'label' => 'Nom du décideur / validateur', 'type' => 'text'],
                    ['name' => 'contraintes', 'label' => 'Contraintes techniques, légales ou commerciales', 'type' => 'textarea', 'full' => true],
                    ['name' => 'remarques', 'label' => 'Informations complémentaires / demandes particulières', 'type' => 'textarea', 'full' => true],
                ],
            ],
        ]),
    ];
};

$additionalGraphicForms = [
    'graphisme_creation_de_cartes_de_visite' => $buildGraphicBrief('création de cartes de visite', [
        ['title' => 'Projet et informations à afficher', 'fields' => [
            ['name' => 'quantite_profils', 'label' => 'Nombre de personnes / versions à réaliser', 'type' => 'text', 'required' => true],
            ['name' => 'identites', 'label' => 'Noms, fonctions et coordonnées de chaque personne', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'informations', 'label' => 'Autres informations à afficher (adresse, site, réseaux, QR code)', 'type' => 'textarea', 'full' => true],
            ['name' => 'style_carte', 'label' => 'Style de carte souhaité', 'type' => 'select', 'options' => ['Classique', 'Moderne', 'Minimaliste', 'Premium', 'Créatif', 'À proposer par EUNITAL']],
        ]],
        ['title' => 'Format et impression', 'fields' => [
            ['name' => 'format_carte', 'label' => 'Format souhaité', 'type' => 'select', 'options' => ['Standard 85 × 55 mm', 'Standard 90 × 50 mm', 'Format personnalisé', 'À recommander par EUNITAL']],
            ['name' => 'faces_carte', 'label' => 'Impression', 'type' => 'select', 'options' => ['Recto', 'Recto-verso', 'À recommander par EUNITAL']],
            ['name' => 'finition_carte', 'label' => 'Papier et finition souhaités', 'type' => 'text', 'placeholder' => 'Mat, brillant, pelliculage, coins arrondis...'],
            ['name' => 'quantite_cartes', 'label' => 'Quantité d’impression', 'type' => 'text'],
            ['name' => 'impression_carte', 'label' => 'Impression à prévoir par EUNITAL ?', 'type' => 'select', 'options' => ['Oui', 'Non, design uniquement', 'À discuter']],
        ]],
    ], ['Carte recto', 'Carte recto-verso', 'Déclinaison par collaborateur', 'Version numérique']),
    'graphisme_conception_de_brochures_et_depliants' => $buildGraphicBrief('conception de brochures et dépliants', [
        ['title' => 'Contenu éditorial et structure', 'fields' => [
            ['name' => 'sujet_brochure', 'label' => 'Sujet et objectif du document', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'public_brochure', 'label' => 'Public visé', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'contenu_brochure', 'label' => 'Textes, rubriques et informations à intégrer', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'pages_brochure', 'label' => 'Nombre de pages ou volets envisagé', 'type' => 'text'],
            ['name' => 'images_brochure', 'label' => 'Photos, illustrations, tableaux ou graphiques à intégrer', 'type' => 'textarea', 'full' => true],
            ['name' => 'redaction_brochure', 'label' => 'Rédaction ou correction des textes nécessaire ?', 'type' => 'select', 'options' => ['Textes définitifs fournis', 'Correction / relecture nécessaire', 'Rédaction à prévoir', 'À discuter']],
        ]],
        ['title' => 'Format de fabrication', 'fields' => [
            ['name' => 'type_brochure', 'label' => 'Type de document', 'type' => 'select', 'options' => ['Dépliant 2 volets', 'Dépliant 3 volets', 'Brochure agrafée', 'Brochure reliée', 'Autre', 'À recommander par EUNITAL']],
            ['name' => 'format_brochure', 'label' => 'Format fermé / ouvert souhaité', 'type' => 'text'],
            ['name' => 'papier_brochure', 'label' => 'Papier et finition souhaités', 'type' => 'text'],
            ['name' => 'quantite_brochure', 'label' => 'Quantité à imprimer', 'type' => 'text'],
            ['name' => 'impression_brochure', 'label' => 'Impression à prévoir par EUNITAL ?', 'type' => 'select', 'options' => ['Oui', 'Non, design uniquement', 'À discuter']],
        ]],
    ], ['PDF pour impression', 'PDF interactif / écran', 'Fichiers source', 'Version multilingue']),
    'graphisme_conception_de_catalogues' => $buildGraphicBrief('conception de catalogues', [
        ['title' => 'Produits, rubriques et organisation', 'fields' => [
            ['name' => 'objectif_catalogue', 'label' => 'Objectif et public du catalogue', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'categories_catalogue', 'label' => 'Catégories, rubriques ou ordre de présentation', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'produits_catalogue', 'label' => 'Liste des produits / services et informations associées', 'type' => 'textarea', 'required' => true, 'full' => true, 'placeholder' => 'Références, descriptions, prix, variantes...'],
            ['name' => 'volume_catalogue', 'label' => 'Nombre approximatif de produits et de pages', 'type' => 'text'],
            ['name' => 'photos_catalogue', 'label' => 'État des photos produits et besoin de retouche', 'type' => 'textarea', 'full' => true],
            ['name' => 'tarifs_catalogue', 'label' => 'Les prix doivent-ils figurer dans le catalogue ?', 'type' => 'select', 'options' => ['Oui, prix fixes', 'Oui, prix susceptibles de changer', 'Non', 'À préciser']],
        ]],
        ['title' => 'Production du catalogue', 'fields' => [
            ['name' => 'format_catalogue', 'label' => 'Format / orientation', 'type' => 'text'],
            ['name' => 'papier_catalogue', 'label' => 'Papier et finition souhaités', 'type' => 'text'],
            ['name' => 'quantite_catalogue', 'label' => 'Quantité d’impression envisagée', 'type' => 'text'],
            ['name' => 'mise_a_jour_catalogue', 'label' => 'Fréquence de mise à jour prévue', 'type' => 'select', 'options' => ['Ponctuelle', 'Mensuelle', 'Trimestrielle', 'Annuelle', 'À définir']],
            ['name' => 'impression_catalogue', 'label' => 'Impression à prévoir par EUNITAL ?', 'type' => 'select', 'options' => ['Oui', 'Non, design uniquement', 'À discuter']],
        ]],
    ], ['Catalogue imprimable', 'Catalogue PDF pour écran', 'Catalogue interactif', 'Fichiers source']),
    'graphisme_creation_de_couvertures_de_livres' => $buildGraphicBrief('création de couvertures de livres', [
        ['title' => 'Informations éditoriales', 'fields' => [
            ['name' => 'titre_livre', 'label' => 'Titre exact du livre', 'type' => 'text', 'required' => true],
            ['name' => 'auteur_livre', 'label' => 'Nom de l’auteur / autrice', 'type' => 'text', 'required' => true],
            ['name' => 'genre_livre', 'label' => 'Genre et sujet du livre', 'type' => 'text', 'required' => true],
            ['name' => 'resume_livre', 'label' => 'Résumé ou synopsis', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'public_livre', 'label' => 'Lecteurs visés', 'type' => 'textarea', 'full' => true],
            ['name' => 'text_couverture', 'label' => 'Texte de quatrième de couverture, citation ou biographie', 'type' => 'textarea', 'full' => true],
        ]],
        ['title' => 'Contraintes éditoriales et impression', 'fields' => [
            ['name' => 'type_couverture', 'label' => 'Éléments à concevoir', 'type' => 'checkboxes', 'options' => ['Première de couverture', 'Dos / tranche', 'Quatrième de couverture', 'Version ebook'], 'full' => true],
            ['name' => 'format_livre', 'label' => 'Format du livre', 'type' => 'text', 'placeholder' => 'Ex. : 15 × 21 cm'],
            ['name' => 'pages_livre', 'label' => 'Nombre de pages (nécessaire pour calculer le dos)', 'type' => 'text'],
            ['name' => 'imprimeur_livre', 'label' => 'Gabarit ou spécifications de l’imprimeur disponibles ?', 'type' => 'select', 'options' => ['Oui, à transmettre', 'Non', 'À demander à l’imprimeur']],
            ['name' => 'isbn_livre', 'label' => 'ISBN, code-barres et logos éditeur disponibles ?', 'type' => 'select', 'options' => ['Oui', 'Partiellement', 'Non', 'Non applicable']],
        ]],
    ], ['Couverture complète avec dos', 'Première de couverture seule', 'Version ebook', 'Fichier prêt à imprimer', 'Fichiers source']),
    'graphisme_conception_de_banderoles_et_baches' => $buildGraphicBrief('conception de banderoles et bâches', [
        ['title' => 'Campagne et contenu', 'fields' => [
            ['name' => 'objet_bache', 'label' => 'Objet, événement ou offre à mettre en avant', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'titre_bache', 'label' => 'Titre ou message principal exact', 'type' => 'text', 'required' => true],
            ['name' => 'public_bache', 'label' => 'Public et lieu d’exposition', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'infos_bache', 'label' => 'Textes, coordonnées, logos et mentions à intégrer', 'type' => 'textarea', 'full' => true],
            ['name' => 'visibilite_bache', 'label' => 'Distance de lecture / visibilité souhaitée', 'type' => 'text'],
        ]],
        ['title' => 'Dimensions et fabrication', 'fields' => [
            ['name' => 'dimensions_bache', 'label' => 'Dimensions exactes (largeur × hauteur)', 'type' => 'text', 'required' => true],
            ['name' => 'support_bache', 'label' => 'Type de support', 'type' => 'select', 'options' => ['Bâche PVC', 'Bâche microperforée', 'Banderole textile', 'Autre', 'À recommander par EUNITAL']],
            ['name' => 'finition_bache', 'label' => 'Finitions nécessaires', 'type' => 'checkboxes', 'options' => ['Œillets', 'Ourlets', 'Fourreaux', 'Renforts', 'Aucune / à définir'], 'full' => true],
            ['name' => 'quantite_bache', 'label' => 'Quantité', 'type' => 'text'],
            ['name' => 'pose_bache', 'label' => 'Installation / pose à prévoir ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
        ]],
    ], ['Fichier prêt à imprimer', 'Déclinaisons de dimensions', 'Maquette de présentation', 'Fichiers source']),
    'graphisme_creation_de_visuels_pour_les_reseaux_sociaux' => $buildGraphicBrief('création de visuels pour les réseaux sociaux', [
        ['title' => 'Objectifs éditoriaux et calendrier', 'fields' => [
            ['name' => 'objectif_social', 'label' => 'Objectif des visuels', 'type' => 'checkboxes', 'required' => true, 'options' => ['Promouvoir une offre', 'Présenter un produit / service', 'Informer / sensibiliser', 'Développer la communauté', 'Annoncer un événement', 'Renforcer la marque'], 'full' => true],
            ['name' => 'reseaux', 'label' => 'Réseaux sociaux concernés', 'type' => 'checkboxes', 'required' => true, 'options' => ['Facebook', 'Instagram', 'TikTok', 'LinkedIn', 'X', 'YouTube', 'WhatsApp', 'Autre'], 'full' => true],
            ['name' => 'volume_social', 'label' => 'Nombre de visuels ou publications à créer', 'type' => 'text', 'required' => true],
            ['name' => 'sujets_social', 'label' => 'Thèmes, textes et appels à l’action', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'calendrier_social', 'label' => 'Dates ou fréquence de publication', 'type' => 'textarea', 'full' => true],
            ['name' => 'cible_social', 'label' => 'Public cible et ton de communication', 'type' => 'textarea', 'full' => true],
        ]],
        ['title' => 'Formats et éléments fournis', 'fields' => [
            ['name' => 'formats_social', 'label' => 'Formats à décliner', 'type' => 'checkboxes', 'options' => ['Post carré', 'Portrait / fil', 'Story / statut', 'Couverture / bannière', 'Carrousel', 'Miniature vidéo'], 'full' => true],
            ['name' => 'medias_social', 'label' => 'Photos, vidéos ou contenus fournis', 'type' => 'textarea', 'full' => true],
            ['name' => 'animation_social', 'label' => 'Animation ou motion design nécessaire ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
        ]],
    ], ['Visuels statiques', 'Déclinaisons multi-réseaux', 'Carrousel', 'Stories', 'Visuels animés', 'Modèles réutilisables']),
    'graphisme_creation_de_presentations_professionnelles' => $buildGraphicBrief('création de présentations professionnelles', [
        ['title' => 'Objectif et contenu de la présentation', 'fields' => [
            ['name' => 'objectif_presentation', 'label' => 'Objectif et contexte de présentation', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'audience_presentation', 'label' => 'Public / audience', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'sujet_presentation', 'label' => 'Sujet, plan et messages clés', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'volume_presentation', 'label' => 'Nombre de diapositives souhaité', 'type' => 'text'],
            ['name' => 'contenus_presentation', 'label' => 'Textes, données, tableaux ou graphiques à intégrer', 'type' => 'textarea', 'full' => true],
            ['name' => 'prise_parole', 'label' => 'Présentation avec prise de parole ou document autonome ?', 'type' => 'select', 'options' => ['Support de présentation orale', 'Document autonome à lire', 'Les deux']],
        ]],
        ['title' => 'Support et fonctionnalités', 'fields' => [
            ['name' => 'logiciel_presentation', 'label' => 'Logiciel ou format attendu', 'type' => 'select', 'options' => ['PowerPoint', 'Google Slides', 'Keynote', 'PDF', 'À recommander par EUNITAL']],
            ['name' => 'ratio_presentation', 'label' => 'Format de diapositive', 'type' => 'select', 'options' => ['16:9', '4:3', 'Autre', 'À définir']],
            ['name' => 'animation_presentation', 'label' => 'Animations ou transitions souhaitées ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Discrètes', 'À discuter']],
            ['name' => 'modele_presentation', 'label' => 'Créer aussi un modèle réutilisable ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
        ]],
    ], ['Présentation modifiable', 'Export PDF', 'Modèle réutilisable', 'Notes du présentateur']),
    'graphisme_illustration_numerique' => $buildGraphicBrief('illustration numérique', [
        ['title' => 'Concept et intention de l’illustration', 'fields' => [
            ['name' => 'sujet_illustration', 'label' => 'Sujet ou scène à illustrer', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'intention_illustration', 'label' => 'Message, histoire ou émotion à transmettre', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'style_illustration', 'label' => 'Technique ou style recherché', 'type' => 'select', 'options' => ['Vectoriel', 'Dessin numérique', 'Peinture numérique', 'Bande dessinée', 'Personnage / mascotte', 'Infographie illustrée', 'Autre', 'À proposer par EUNITAL']],
            ['name' => 'elements_illustration', 'label' => 'Éléments, personnages ou environnement à représenter', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'references_illustration', 'label' => 'Références et inspirations', 'type' => 'textarea', 'full' => true],
        ]],
        ['title' => 'Usage et spécifications techniques', 'fields' => [
            ['name' => 'usage_illustration', 'label' => 'Usage prévu et supports de publication', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'dimensions_illustration', 'label' => 'Dimensions, orientation et résolution souhaitées', 'type' => 'text'],
            ['name' => 'couleur_illustration', 'label' => 'Couleur ou noir et blanc ?', 'type' => 'select', 'options' => ['Couleur', 'Noir et blanc', 'Les deux']],
            ['name' => 'droits_illustration', 'label' => 'Usage commercial, durée ou territoire particulier', 'type' => 'textarea', 'full' => true],
        ]],
    ], ['Illustration finale', 'Variantes de couleurs', 'Fichier vectoriel', 'Fichier source', 'Déclinaisons de formats']),
    'graphisme_dessin_et_retouche_photographique' => $buildGraphicBrief('dessin et retouche photographique', [
        ['title' => 'Images et traitement demandé', 'fields' => [
            ['name' => 'volume_photo', 'label' => 'Nombre de photos ou images à traiter', 'type' => 'text', 'required' => true],
            ['name' => 'traitement_photo', 'label' => 'Type de traitement souhaité', 'type' => 'checkboxes', 'required' => true, 'options' => ['Correction lumière / couleur', 'Détourage / fond', 'Suppression d’objet', 'Retouche portrait', 'Restauration', 'Photomontage / composition', 'Dessin à partir d’une photo', 'Autre'], 'full' => true],
            ['name' => 'description_photo', 'label' => 'Décrivez précisément les retouches ou dessins souhaités', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'sources_photo', 'label' => 'Qualité, format et mode de transmission des originaux', 'type' => 'textarea', 'full' => true],
            ['name' => 'avant_apres', 'label' => 'Niveau de retouche souhaité', 'type' => 'select', 'options' => ['Naturel et discret', 'Correction visible', 'Créatif / artistique', 'À proposer par EUNITAL']],
        ]],
        ['title' => 'Usages et livrables photo', 'fields' => [
            ['name' => 'usage_photo', 'label' => 'Usage prévu des images', 'type' => 'checkboxes', 'options' => ['Réseaux sociaux', 'Site web', 'Impression', 'Publicité', 'Usage personnel', 'Autre'], 'full' => true],
            ['name' => 'format_photo', 'label' => 'Dimensions, résolution et format de sortie', 'type' => 'text'],
            ['name' => 'fond_photo', 'label' => 'Arrière-plan ou éléments à préserver / supprimer', 'type' => 'textarea', 'full' => true],
        ]],
    ], ['Images retouchées', 'Fichiers haute résolution', 'Déclinaisons web', 'Fichiers source']),
    'graphisme_conception_de_maquettes_publicitaires' => $buildGraphicBrief('conception de maquettes publicitaires', [
        ['title' => 'Campagne et dispositif publicitaire', 'fields' => [
            ['name' => 'campagne_maquette', 'label' => 'Nom et objectif de la campagne', 'type' => 'text', 'required' => true],
            ['name' => 'offre_maquette', 'label' => 'Produit, service ou offre à promouvoir', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'cible_maquette', 'label' => 'Public cible et zone de diffusion', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'message_maquette', 'label' => 'Message principal, arguments et appel à l’action', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'supports_maquette', 'label' => 'Supports publicitaires concernés', 'type' => 'checkboxes', 'required' => true, 'options' => ['Affiche', 'Flyer', 'Panneau / bâche', 'Presse', 'Réseaux sociaux', 'Web / bannière', 'Autre'], 'full' => true],
        ]],
        ['title' => 'Déclinaisons, contraintes et validation', 'fields' => [
            ['name' => 'dimensions_maquette', 'label' => 'Dimensions, formats et orientations', 'type' => 'textarea', 'full' => true],
            ['name' => 'variantes_maquette', 'label' => 'Nombre de variantes ou déclinaisons', 'type' => 'text'],
            ['name' => 'visuels_maquette', 'label' => 'Photos, textes, logos ou éléments fournis', 'type' => 'textarea', 'full' => true],
            ['name' => 'regles_maquette', 'label' => 'Contraintes publicitaires ou mentions obligatoires', 'type' => 'textarea', 'full' => true],
        ]],
    ], ['Maquette principale', 'Déclinaisons multi-formats', 'Visuels numériques', 'Fichiers prêts à imprimer', 'Fichiers source']),
    'graphisme_calligraphie_et_lettrage_decoratif' => $buildGraphicBrief('calligraphie et lettrage décoratif', [
        ['title' => 'Texte et intention', 'fields' => [
            ['name' => 'texte_calligraphie', 'label' => 'Texte exact à calligraphier / lettrer', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'langue_calligraphie', 'label' => 'Langue, alphabet et sens de lecture', 'type' => 'text', 'required' => true],
            ['name' => 'usage_calligraphie', 'label' => 'Usage et contexte du lettrage', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'style_calligraphie', 'label' => 'Style recherché', 'type' => 'select', 'options' => ['Classique', 'Contemporain', 'Manuscrit', 'Traditionnel', 'Ornemental', 'Gothique', 'À proposer par EUNITAL']],
            ['name' => 'ornements_calligraphie', 'label' => 'Ornements, symboles ou éléments à intégrer', 'type' => 'textarea', 'full' => true],
        ]],
        ['title' => 'Support et réalisation', 'fields' => [
            ['name' => 'support_calligraphie', 'label' => 'Support final', 'type' => 'select', 'options' => ['Fichier numérique', 'Impression', 'Panneau / enseigne', 'Invitation / papeterie', 'Textile', 'Autre']],
            ['name' => 'dimensions_calligraphie', 'label' => 'Dimensions et emplacement', 'type' => 'text'],
            ['name' => 'couleurs_calligraphie', 'label' => 'Couleurs et matériaux souhaités', 'type' => 'text'],
            ['name' => 'style_a_eviter_calligraphie', 'label' => 'Styles ou symboles à éviter', 'type' => 'textarea', 'full' => true],
        ]],
    ], ['Lettrage vectoriel', 'Composition prête à imprimer', 'Fichier transparent', 'Déclinaisons couleur', 'Fichiers source']),
    'graphisme_impression_et_personnalisation_textile_t_shirts_polos_casquettes_et_uniformes' => $buildGraphicBrief('impression et personnalisation textile', [
        ['title' => 'Articles textiles et quantités', 'fields' => [
            ['name' => 'articles_textile', 'label' => 'Articles à personnaliser', 'type' => 'checkboxes', 'required' => true, 'options' => ['T-shirts', 'Polos', 'Casquettes', 'Chemises', 'Uniformes', 'Sweats / vestes', 'Autre'], 'full' => true],
            ['name' => 'quantites_textile', 'label' => 'Quantités par article, taille et couleur', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'tailles_textile', 'label' => 'Tailles et répartition', 'type' => 'textarea', 'full' => true],
            ['name' => 'textile_fourni', 'label' => 'Qui fournit les vêtements ?', 'type' => 'select', 'options' => ['Le client fournit les textiles', 'EUNITAL fournit les textiles', 'À discuter']],
            ['name' => 'qualite_textile', 'label' => 'Matière, couleur et qualité souhaitées', 'type' => 'text'],
        ]],
        ['title' => 'Marquage et emplacement', 'fields' => [
            ['name' => 'marquage_textile', 'label' => 'Éléments à imprimer / personnaliser', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'emplacement_textile', 'label' => 'Emplacement(s) du marquage', 'type' => 'checkboxes', 'options' => ['Poitrine gauche', 'Poitrine centrale', 'Dos', 'Manche', 'Face avant casquette', 'Autre'], 'full' => true],
            ['name' => 'dimensions_marquage', 'label' => 'Dimensions approximatives du marquage', 'type' => 'text'],
            ['name' => 'maquette_textile', 'label' => 'Maquette de validation avant production souhaitée ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
        ]],
    ], ['Maquette de placement', 'Échantillon de validation', 'Production textile', 'Fichier prêt à imprimer']),
    'graphisme_flocage_transfert_dtf_et_serigraphie' => $buildGraphicBrief('flocage, transfert DTF et sérigraphie', [
        ['title' => 'Commande et procédé souhaité', 'fields' => [
            ['name' => 'support_impression', 'label' => 'Support à personnaliser', 'type' => 'checkboxes', 'required' => true, 'options' => ['T-shirt', 'Polo', 'Casquette', 'Uniforme', 'Sac / accessoire', 'Autre'], 'full' => true],
            ['name' => 'quantite_impression', 'label' => 'Quantité par support, taille et couleur', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'technique_impression', 'label' => 'Technique souhaitée', 'type' => 'select', 'required' => true, 'options' => ['Flocage', 'Transfert DTF', 'Sérigraphie', 'À recommander par EUNITAL']],
            ['name' => 'visuel_impression', 'label' => 'Visuel, texte ou logo à reproduire', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'couleurs_impression', 'label' => 'Nombre et couleurs d’impression', 'type' => 'text'],
        ]],
        ['title' => 'Placement, matière et production', 'fields' => [
            ['name' => 'emplacement_impression', 'label' => 'Emplacement et dimensions du marquage', 'type' => 'textarea', 'full' => true],
            ['name' => 'matiere_impression', 'label' => 'Matière et couleur du support', 'type' => 'text'],
            ['name' => 'support_fourni_impression', 'label' => 'Les supports sont-ils fournis par le client ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Partiellement', 'À discuter']],
            ['name' => 'essai_impression', 'label' => 'Échantillon ou essai avant production souhaité ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
        ]],
    ], ['Conseil sur la technique', 'Maquette de placement', 'Échantillon', 'Production complète', 'Fichier de production']),
    'graphisme_conception_graphique_pour_emballages_et_etiquettes' => $buildGraphicBrief('conception graphique pour emballages et étiquettes', [
        ['title' => 'Produit et informations réglementaires', 'fields' => [
            ['name' => 'produit_emballage', 'label' => 'Nom et description du produit', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'marque_emballage', 'label' => 'Marque et public cible', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'informations_emballage', 'label' => 'Textes, ingrédients, composition, poids / volume et mentions à afficher', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'codes_emballage', 'label' => 'Code-barres, QR code, logos ou certifications', 'type' => 'textarea', 'full' => true],
            ['name' => 'reglementation_emballage', 'label' => 'Exigences réglementaires et pays de commercialisation', 'type' => 'textarea', 'full' => true],
        ]],
        ['title' => 'Structure et fabrication', 'fields' => [
            ['name' => 'type_emballage', 'label' => 'Type d’emballage / étiquette', 'type' => 'select', 'options' => ['Étiquette adhésive', 'Boîte / étui', 'Sachet / pochette', 'Bouteille / pot', 'Emballage souple', 'Autre']],
            ['name' => 'gabarit_emballage', 'label' => 'Dimensions et gabarit technique disponibles ?', 'type' => 'select', 'options' => ['Oui, à transmettre', 'Non, à créer', 'À demander au fabricant']],
            ['name' => 'materiaux_emballage', 'label' => 'Matériau, forme et finition souhaités', 'type' => 'textarea', 'full' => true],
            ['name' => 'impression_emballage', 'label' => 'Procédé d’impression ou contraintes du fabricant', 'type' => 'text'],
            ['name' => 'variantes_emballage', 'label' => 'Nombre de références / variantes à décliner', 'type' => 'text'],
        ]],
    ], ['Étiquette prête à imprimer', 'Maquette 3D de présentation', 'Déclinaison par référence', 'Fichier technique / source']),
];

$buildSecretariatBrief = static function (string $service, string $description, array $sections, array $deliverables): array {
    return [
        'title' => 'Questionnaire — Brief : '.$service,
        'description' => $description,
        'sections' => array_merge([
            [
                'title' => '1. Informations du client',
                'fields' => [
                    ['name' => 'organisation', 'label' => 'Nom de l’entreprise / organisation ou du particulier', 'type' => 'text', 'required' => true],
                    ['name' => 'responsable', 'label' => 'Nom du demandeur', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Adresse email', 'type' => 'email', 'required' => true],
                    ['name' => 'telephone', 'label' => 'Téléphone / WhatsApp', 'type' => 'tel', 'required' => true],
                ],
            ],
        ], $sections, [
            [
                'title' => 'Délais, livrables et consignes',
                'fields' => [
                    ['name' => 'date_souhaitee', 'label' => 'Date souhaitée de livraison / retrait', 'type' => 'date', 'required' => true],
                    ['name' => 'livrables', 'label' => 'Format ou livrables attendus', 'type' => 'checkboxes', 'options' => $deliverables, 'full' => true],
                    ['name' => 'budget', 'label' => 'Budget estimatif, si connu', 'type' => 'text', 'placeholder' => 'Montant et devise'],
                    ['name' => 'consignes', 'label' => 'Consignes, contraintes ou informations complémentaires', 'type' => 'textarea', 'full' => true],
                ],
            ],
        ]),
    ];
};

$additionalSecretariatForms = [
    'secretariat_saisie_de_documents_administratifs' => $buildSecretariatBrief(
        'saisie de documents administratifs',
        'Précisez le type de document, son contenu, les consignes de présentation et le format souhaité.',
        [
            ['title' => 'Document à saisir', 'fields' => [
                ['name' => 'type_document', 'label' => 'Type de document administratif', 'type' => 'select', 'required' => true, 'options' => ['Formulaire', 'Demande / requête', 'Rapport', 'Procès-verbal', 'Note / compte rendu', 'Courrier', 'Autre']],
                ['name' => 'objet_document', 'label' => 'Objet et destinataire du document', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'volume_document', 'label' => 'Nombre approximatif de pages', 'type' => 'text'],
                ['name' => 'source_document', 'label' => 'Le contenu est-il manuscrit, dicté ou déjà numérisé ?', 'type' => 'select', 'options' => ['Manuscrit', 'Notes / brouillon', 'Fichier numérique', 'Dictée', 'À préciser']],
                ['name' => 'regles_document', 'label' => 'Modèle, en-tête ou règles administratives à respecter', 'type' => 'textarea', 'full' => true],
                ['name' => 'confidentialite_document', 'label' => 'Le document contient-il des informations confidentielles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
            ]],
        ],
        ['Fichier Word modifiable', 'PDF final', 'Impression papier', 'Saisie et mise en page']
    ),
    'secretariat_redaction_de_lettres_demandes_et_correspondances' => $buildSecretariatBrief(
        'rédaction de lettres, demandes et correspondances',
        'Donnez le contexte, le destinataire et le résultat attendu afin de préparer une correspondance adaptée.',
        [
            ['title' => 'Contexte et destinataire', 'fields' => [
                ['name' => 'type_correspondance', 'label' => 'Type de correspondance', 'type' => 'select', 'required' => true, 'options' => ['Lettre administrative', 'Demande / requête', 'Lettre de réclamation', 'Lettre de réponse', 'Courrier professionnel', 'Email formel', 'Autre']],
                ['name' => 'destinataire', 'label' => 'Nom, titre et organisme du destinataire', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'objet_correspondance', 'label' => 'Objet précis de la lettre', 'type' => 'text', 'required' => true],
                ['name' => 'contexte_correspondance', 'label' => 'Expliquez les faits et le contexte dans l’ordre chronologique', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'demande_correspondance', 'label' => 'Quelle réponse ou action attendez-vous du destinataire ?', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'ton_correspondance', 'label' => 'Ton souhaité', 'type' => 'select', 'options' => ['Très formel', 'Professionnel et cordial', 'Ferme et factuel', 'Conciliant', 'À recommander']],
            ]],
            ['title' => 'Éléments à intégrer', 'fields' => [
                ['name' => 'faits_correspondance', 'label' => 'Dates, références, pièces jointes ou informations à citer', 'type' => 'textarea', 'full' => true],
                ['name' => 'signature_correspondance', 'label' => 'Nom, fonction et coordonnées à utiliser pour la signature', 'type' => 'textarea', 'full' => true],
                ['name' => 'modele_correspondance', 'label' => 'Disposez-vous d’un modèle ou d’un papier à en-tête ?', 'type' => 'select', 'options' => ['Oui, je le fournirai', 'Non', 'À définir']],
            ]],
        ],
        ['Lettre rédigée', 'Fichier Word modifiable', 'PDF prêt à envoyer', 'Version email']
    ),
    'secretariat_saisie_de_memoires_rapports_et_travaux_academiques' => $buildSecretariatBrief(
        'saisie de mémoires, rapports et travaux académiques',
        'Brief dédié aux travaux académiques : structure, normes de l’établissement, tableaux et éléments scientifiques.',
        [
            ['title' => 'Travail académique', 'fields' => [
                ['name' => 'type_travail', 'label' => 'Type de travail', 'type' => 'select', 'required' => true, 'options' => ['Mémoire', 'Rapport de stage', 'Thèse', 'Travail de fin de cycle', 'Devoir / exposé', 'Article scientifique', 'Autre']],
                ['name' => 'titre_travail', 'label' => 'Titre ou sujet du travail', 'type' => 'text', 'required' => true],
                ['name' => 'etablissement_travail', 'label' => 'Établissement, faculté et niveau d’études', 'type' => 'text'],
                ['name' => 'volume_travail', 'label' => 'Nombre approximatif de pages et état d’avancement', 'type' => 'text'],
                ['name' => 'source_travail', 'label' => 'Format des documents à saisir', 'type' => 'select', 'options' => ['Manuscrit lisible', 'Notes / brouillon', 'Fichiers partiels', 'Enregistrement dicté', 'Autre']],
                ['name' => 'elements_travail', 'label' => 'Tableaux, équations, graphiques, annexes ou notes de bas de page à traiter', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Normes et organisation', 'fields' => [
                ['name' => 'normes_travail', 'label' => 'Consignes de l’établissement (police, marges, interligne, pagination)', 'type' => 'textarea', 'full' => true],
                ['name' => 'plan_travail', 'label' => 'Plan, chapitres et ordre des sections à respecter', 'type' => 'textarea', 'full' => true],
                ['name' => 'references_travail', 'label' => 'Style bibliographique ou consignes de citation', 'type' => 'text', 'placeholder' => 'APA, Chicago, ISO 690, consignes de l’établissement...'],
                ['name' => 'relecture_travail', 'label' => 'Faut-il aussi corriger l’orthographe ou le contenu ?', 'type' => 'select', 'options' => ['Saisie uniquement', 'Correction orthographique', 'Mise en forme uniquement', 'À discuter']],
            ]],
        ],
        ['Document Word structuré', 'PDF final', 'Table des matières automatique', 'Impression / reliure sur demande']
    ),
    'secretariat_mise_en_page_de_documents_word_et_pdf' => $buildSecretariatBrief(
        'mise en page de documents Word et PDF',
        'Décrivez le document existant et le rendu attendu pour obtenir une mise en page lisible et cohérente.',
        [
            ['title' => 'Document et rendu attendu', 'fields' => [
                ['name' => 'type_mise_en_page', 'label' => 'Type de document à mettre en page', 'type' => 'select', 'required' => true, 'options' => ['Rapport', 'Dossier professionnel', 'Manuel / guide', 'Présentation textuelle', 'Formulaire', 'Document administratif', 'Autre']],
                ['name' => 'volume_mise_en_page', 'label' => 'Nombre de pages et format actuel', 'type' => 'text'],
                ['name' => 'etat_mise_en_page', 'label' => 'État du fichier fourni', 'type' => 'select', 'options' => ['Word modifiable', 'PDF à convertir', 'Fichiers mélangés', 'Contenu sans mise en forme']],
                ['name' => 'besoin_mise_en_page', 'label' => 'Modifications de présentation souhaitées', 'type' => 'checkboxes', 'options' => ['Styles de titres', 'Marges et pagination', 'Sommaire automatique', 'En-tête / pied de page', 'Tableaux / images', 'Uniformisation des polices', 'Conversion Word / PDF'], 'full' => true],
                ['name' => 'modele_mise_en_page', 'label' => 'Modèle ou charte à respecter', 'type' => 'textarea', 'full' => true],
                ['name' => 'correction_mise_en_page', 'label' => 'La correction du texte est-elle incluse dans la demande ?', 'type' => 'select', 'options' => ['Non, mise en page seulement', 'Oui, correction orthographique', 'À discuter']],
            ]],
        ],
        ['Document Word mis en page', 'PDF prêt à partager', 'Sommaire automatique', 'Fichier source modifiable']
    ),
    'secretariat_impression_noir_et_blanc_et_couleur' => $buildSecretariatBrief(
        'impression noir et blanc et couleur',
        'Indiquez les fichiers, le volume, les réglages et la finition souhaités pour chiffrer et préparer l’impression.',
        [
            ['title' => 'Fichiers et paramètres d’impression', 'fields' => [
                ['name' => 'fichiers_impression', 'label' => 'Fichiers à imprimer et nombre de fichiers', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'pages_impression', 'label' => 'Pages à imprimer et nombre d’exemplaires par fichier', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'couleur_impression', 'label' => 'Mode d’impression', 'type' => 'select', 'required' => true, 'options' => ['Noir et blanc', 'Couleur', 'Mixte (préciser les pages)', 'À vérifier sur le fichier']],
                ['name' => 'papier_impression', 'label' => 'Format et type de papier', 'type' => 'select', 'options' => ['A4 standard', 'A3', 'Autre format', 'Papier spécial / épais', 'À confirmer']],
                ['name' => 'recto_verso_impression', 'label' => 'Impression recto-verso ?', 'type' => 'select', 'options' => ['Recto', 'Recto-verso', 'Selon le document']],
                ['name' => 'finition_impression', 'label' => 'Tri, agrafage ou autres finitions', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        ['Impression noir et blanc', 'Impression couleur', 'Recto-verso', 'Tri et agrafage']
    ),
    'secretariat_photocopie_et_duplication_de_documents' => $buildSecretariatBrief(
        'photocopie et duplication de documents',
        'Précisez les documents originaux, le nombre de copies et les réglages de reproduction nécessaires.',
        [
            ['title' => 'Originaux et nombre de copies', 'fields' => [
                ['name' => 'type_photocopie', 'label' => 'Type de documents à photocopier', 'type' => 'text', 'required' => true],
                ['name' => 'volume_photocopie', 'label' => 'Nombre de pages de l’original et nombre de jeux', 'type' => 'text', 'required' => true],
                ['name' => 'reglages_photocopie', 'label' => 'Réglages souhaités', 'type' => 'checkboxes', 'options' => ['Noir et blanc', 'Couleur', 'Recto-verso', 'Agrandissement / réduction', 'Assemblage par jeu', 'Agrafage'], 'full' => true],
                ['name' => 'etat_photocopie', 'label' => 'État des originaux (lisibilité, couleur, format)', 'type' => 'textarea', 'full' => true],
                ['name' => 'confidentialite_photocopie', 'label' => 'Documents contenant des informations personnelles ou confidentielles ?', 'type' => 'select', 'options' => ['Oui', 'Non']],
            ]],
        ],
        ['Copies noir et blanc', 'Copies couleur', 'Jeux assemblés', 'Agrafage']
    ),
    'secretariat_numerisation_scan_de_documents' => $buildSecretariatBrief(
        'numérisation (scan) de documents',
        'Précisez le volume, la qualité, les formats numériques et le mode de remise attendu pour les documents numérisés.',
        [
            ['title' => 'Documents à numériser', 'fields' => [
                ['name' => 'type_scan', 'label' => 'Type et état des documents', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'volume_scan', 'label' => 'Nombre approximatif de pages / documents', 'type' => 'text', 'required' => true],
                ['name' => 'couleur_scan', 'label' => 'Mode de numérisation', 'type' => 'select', 'options' => ['Noir et blanc', 'Niveaux de gris', 'Couleur', 'Selon l’original']],
                ['name' => 'resolution_scan', 'label' => 'Qualité / résolution souhaitée', 'type' => 'select', 'options' => ['Lecture écran', 'Impression standard', 'Haute résolution', 'À recommander']],
                ['name' => 'ocr_scan', 'label' => 'Souhaitez-vous un PDF avec texte recherchable (OCR) ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
                ['name' => 'organisation_scan', 'label' => 'Organisation des fichiers (un fichier par page, document ou dossier)', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Remise et confidentialité', 'fields' => [
                ['name' => 'confidentialite_scan', 'label' => 'Les documents comportent-ils des données sensibles ?', 'type' => 'select', 'options' => ['Oui', 'Non']],
                ['name' => 'transfert_scan', 'label' => 'Mode de remise souhaité', 'type' => 'select', 'options' => ['Email', 'Support USB du client', 'Lien de téléchargement sécurisé', 'Autre / à discuter']],
            ]],
        ],
        ['PDF', 'Images JPG / PNG', 'PDF avec OCR', 'Fichiers classés par dossier']
    ),
    'secretariat_plastification_de_documents' => $buildSecretariatBrief(
        'plastification de documents',
        'Renseignez le format, la quantité et le type de plastification afin de préparer une finition appropriée.',
        [
            ['title' => 'Documents et finition', 'fields' => [
                ['name' => 'type_plastification', 'label' => 'Type de documents à plastifier', 'type' => 'text', 'required' => true],
                ['name' => 'format_plastification', 'label' => 'Format des documents', 'type' => 'select', 'required' => true, 'options' => ['Carte / badge', 'A5', 'A4', 'A3', 'Formats variés', 'Autre']],
                ['name' => 'quantite_plastification', 'label' => 'Quantité par format', 'type' => 'text', 'required' => true],
                ['name' => 'finition_plastification', 'label' => 'Finition souhaitée', 'type' => 'select', 'options' => ['Brillante', 'Mate', 'Épaisseur standard', 'Renforcée / épaisse', 'À conseiller']],
                ['name' => 'decoupe_plastification', 'label' => 'Découpe ou coins arrondis nécessaires ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
            ]],
        ],
        ['Plastification brillante', 'Plastification mate', 'Découpe aux dimensions', 'Coins arrondis']
    ),
    'secretariat_reliure_de_memoires_et_rapports' => $buildSecretariatBrief(
        'reliure de mémoires et rapports',
        'Décrivez les documents finalisés, le type de reliure, les exemplaires et la couverture souhaités.',
        [
            ['title' => 'Document et type de reliure', 'fields' => [
                ['name' => 'type_reliure', 'label' => 'Type de document à relier', 'type' => 'select', 'required' => true, 'options' => ['Mémoire', 'Rapport de stage', 'Thèse', 'Dossier professionnel', 'Autre']],
                ['name' => 'pages_reliure', 'label' => 'Nombre de pages par exemplaire', 'type' => 'text', 'required' => true],
                ['name' => 'exemplaires_reliure', 'label' => 'Nombre d’exemplaires', 'type' => 'text', 'required' => true],
                ['name' => 'type_finition_reliure', 'label' => 'Type de reliure souhaité', 'type' => 'select', 'options' => ['Spirale', 'Thermocollée', 'Dos carré collé', 'À recommander']],
                ['name' => 'couverture_reliure', 'label' => 'Couleur et finition de la couverture', 'type' => 'text'],
                ['name' => 'impression_reliure', 'label' => 'L’impression du document est-elle également nécessaire ?', 'type' => 'select', 'options' => ['Oui', 'Non, document déjà imprimé', 'Partiellement']],
                ['name' => 'document_final_reliure', 'label' => 'Le fichier est-il finalisé et prêt à imprimer ?', 'type' => 'select', 'options' => ['Oui', 'Non, mise en page à terminer', 'À vérifier']],
            ]],
        ],
        ['Reliure spirale', 'Reliure thermocollée', 'Couverture personnalisée', 'Impression et reliure']
    ),
    'secretariat_creation_et_mise_en_forme_de_cv' => $buildSecretariatBrief(
        'création et mise en forme de CV',
        'Présentez votre parcours et le poste visé pour construire un CV clair, professionnel et adapté à votre candidature.',
        [
            ['title' => 'Profil et objectif professionnel', 'fields' => [
                ['name' => 'poste_cv', 'label' => 'Métier ou poste recherché', 'type' => 'text', 'required' => true],
                ['name' => 'experience_cv', 'label' => 'Expériences professionnelles (poste, employeur, dates, missions)', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'formation_cv', 'label' => 'Formations, diplômes et années', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'competences_cv', 'label' => 'Compétences, langues et outils maîtrisés', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'realisations_cv', 'label' => 'Réalisations, stages, certifications ou projets pertinents', 'type' => 'textarea', 'full' => true],
                ['name' => 'photo_cv', 'label' => 'Souhaitez-vous inclure une photo ?', 'type' => 'select', 'options' => ['Oui, je fournirai la photo', 'Non', 'À conseiller selon le poste']],
            ]],
            ['title' => 'Style et pièces de référence', 'fields' => [
                ['name' => 'style_cv', 'label' => 'Style de CV souhaité', 'type' => 'select', 'options' => ['Classique et sobre', 'Moderne', 'Créatif', 'Académique', 'À proposer']],
                ['name' => 'cv_existant', 'label' => 'Avez-vous un CV existant à actualiser ?', 'type' => 'select', 'options' => ['Oui, je le fournirai', 'Non']],
                ['name' => 'offre_cv', 'label' => 'Lien ou description d’une offre d’emploi ciblée', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        ['CV Word modifiable', 'CV PDF prêt à envoyer', 'Version adaptée à une offre', 'Lettre de motivation en option']
    ),
    'secretariat_redaction_de_lettres_de_motivation' => $buildSecretariatBrief(
        'rédaction de lettres de motivation',
        'Partagez l’offre, votre parcours et vos motivations pour rédiger une lettre personnalisée et crédible.',
        [
            ['title' => 'Candidature et poste visé', 'fields' => [
                ['name' => 'poste_motivation', 'label' => 'Intitulé du poste ou de la formation visé(e)', 'type' => 'text', 'required' => true],
                ['name' => 'organisation_motivation', 'label' => 'Nom de l’entreprise, organisme ou établissement', 'type' => 'text', 'required' => true],
                ['name' => 'offre_motivation', 'label' => 'Texte ou lien vers l’offre / l’annonce', 'type' => 'textarea', 'full' => true],
                ['name' => 'parcours_motivation', 'label' => 'Expériences et compétences à valoriser', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'raisons_motivation', 'label' => 'Pourquoi souhaitez-vous rejoindre cet organisme ?', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'realisations_motivation', 'label' => 'Exemples ou réalisations démontrant votre valeur', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Ton et modalités', 'fields' => [
                ['name' => 'ton_motivation', 'label' => 'Ton souhaité', 'type' => 'select', 'options' => ['Formel et professionnel', 'Direct et convaincant', 'Chaleureux', 'Académique', 'À recommander']],
                ['name' => 'langue_motivation', 'label' => 'Langue de rédaction', 'type' => 'select', 'options' => ['Français', 'Anglais', 'Autre']],
                ['name' => 'cv_motivation', 'label' => 'CV ou informations complémentaires à prendre en compte', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        ['Lettre Word modifiable', 'Lettre PDF prête à envoyer', 'Version personnalisée à l’offre']
    ),
    'secretariat_constitution_et_classement_de_dossiers_administratifs' => $buildSecretariatBrief(
        'constitution et classement de dossiers administratifs',
        'Précisez la démarche concernée, les pièces à réunir et la méthode de classement souhaitée.',
        [
            ['title' => 'Démarche et pièces du dossier', 'fields' => [
                ['name' => 'demarche_dossier', 'label' => 'Démarche ou objectif du dossier', 'type' => 'text', 'required' => true],
                ['name' => 'organisme_dossier', 'label' => 'Administration ou organisme destinataire', 'type' => 'text'],
                ['name' => 'pieces_dossier', 'label' => 'Liste des pièces disponibles et des pièces manquantes', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'volume_dossier', 'label' => 'Nombre de documents et format (papier / numérique)', 'type' => 'text'],
                ['name' => 'consignes_dossier', 'label' => 'Ordre, formulaires, copies certifiées ou exigences particulières', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Classement et remise', 'fields' => [
                ['name' => 'mode_classement', 'label' => 'Méthode de classement souhaitée', 'type' => 'select', 'options' => ['Par ordre chronologique', 'Par catégorie de pièces', 'Selon la liste de l’administration', 'Classement numérique', 'À proposer']],
                ['name' => 'index_dossier', 'label' => 'Sommaire, index ou étiquetage souhaité ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À recommander']],
                ['name' => 'restitution_dossier', 'label' => 'Les originaux doivent-ils être restitués séparément ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Sans objet']],
            ]],
        ],
        ['Dossier classé', 'Sommaire des pièces', 'Copies papier', 'Dossier numérique organisé']
    ),
    'secretariat_assistance_aux_demarches_administratives_en_ligne' => $buildSecretariatBrief(
        'assistance aux démarches administratives en ligne',
        'Décrivez la procédure et son état d’avancement. Ne transmettez jamais de mot de passe ni de code de validation.',
        [
            ['title' => 'Procédure et situation', 'fields' => [
                ['name' => 'type_demarche', 'label' => 'Démarche à effectuer', 'type' => 'text', 'required' => true],
                ['name' => 'plateforme_demarche', 'label' => 'Site ou plateforme officielle concernée', 'type' => 'url'],
                ['name' => 'etape_demarche', 'label' => 'Étape actuelle et résultat attendu', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'difficultes_demarche', 'label' => 'Difficulté rencontrée ou message d’erreur (sans données sensibles)', 'type' => 'textarea', 'full' => true],
                ['name' => 'pieces_demarche', 'label' => 'Pièces justificatives nécessaires et disponibilité', 'type' => 'textarea', 'full' => true],
                ['name' => 'identifiant_demarche', 'label' => 'Un compte existe-t-il déjà sur la plateforme ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Je ne sais pas']],
                ['name' => 'note_securite_demarche', 'label' => 'Sécurité', 'type' => 'textarea', 'default' => 'Ne saisissez pas ici de mot de passe, code OTP, numéro complet de carte bancaire ou autre secret d’authentification.'],
            ]],
        ],
        ['Accompagnement guidé', 'Formulaire en ligne complété avec le client', 'Récapitulatif des étapes', 'Liste des pièces à fournir']
    ),
    'secretariat_transcription_audio_en_texte' => $buildSecretariatBrief(
        'transcription audio en texte',
        'Indiquez la langue, la durée et le niveau de fidélité souhaité. Transmettez les enregistrements par un canal convenu avec l’équipe.',
        [
            ['title' => 'Enregistrement à transcrire', 'fields' => [
                ['name' => 'type_audio', 'label' => 'Type d’enregistrement', 'type' => 'select', 'required' => true, 'options' => ['Entretien', 'Réunion', 'Cours / conférence', 'Interview', 'Dictée', 'Podcast', 'Autre']],
                ['name' => 'duree_audio', 'label' => 'Durée totale approximative', 'type' => 'text', 'required' => true],
                ['name' => 'langue_audio', 'label' => 'Langue(s), accents ou termes spécialisés', 'type' => 'text', 'required' => true],
                ['name' => 'locuteurs_audio', 'label' => 'Nombre de personnes qui parlent et identification possible', 'type' => 'text'],
                ['name' => 'qualite_audio', 'label' => 'Qualité de l’enregistrement (bruit, volume, interruptions)', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Style de transcription et confidentialité', 'fields' => [
                ['name' => 'style_transcription', 'label' => 'Niveau de fidélité souhaité', 'type' => 'select', 'required' => true, 'options' => ['Verbatim intégral', 'Verbatim nettoyé (hésitations retirées)', 'Texte révisé et fluide', 'Compte rendu synthétique']],
                ['name' => 'horodatage_audio', 'label' => 'Horodatage ou repérage des intervenants requis ?', 'type' => 'select', 'options' => ['Horodatage régulier', 'Noms des intervenants', 'Les deux', 'Aucun']],
                ['name' => 'confidentialite_audio', 'label' => 'L’enregistrement contient-il des informations confidentielles ?', 'type' => 'select', 'options' => ['Oui', 'Non']],
                ['name' => 'transfert_audio', 'label' => 'Mode de transmission du fichier à convenir', 'type' => 'select', 'options' => ['Lien de transfert sécurisé', 'Support physique remis à l’équipe', 'Autre modalité convenue']],
            ]],
        ],
        ['Transcription Word modifiable', 'Transcription PDF', 'Identification des intervenants', 'Horodatage', 'Compte rendu synthétique']
    ),
    'secretariat_correction_et_mise_en_forme_de_documents' => $buildSecretariatBrief(
        'correction et mise en forme de documents',
        'Distinguez le niveau de correction linguistique attendu de la mise en page afin de préserver votre sens et votre style.',
        [
            ['title' => 'Document et correction souhaitée', 'fields' => [
                ['name' => 'type_correction', 'label' => 'Type et sujet du document', 'type' => 'text', 'required' => true],
                ['name' => 'volume_correction', 'label' => 'Nombre de pages ou de mots', 'type' => 'text'],
                ['name' => 'niveau_correction', 'label' => 'Niveau de correction souhaité', 'type' => 'select', 'required' => true, 'options' => ['Orthographe et grammaire', 'Correction linguistique approfondie', 'Reformulation légère', 'Relecture sans modification du style']],
                ['name' => 'priorites_correction', 'label' => 'Points à vérifier (cohérence, terminologie, citations, chiffres...)', 'type' => 'textarea', 'full' => true],
                ['name' => 'normes_correction', 'label' => 'Normes ou règles de présentation à respecter', 'type' => 'textarea', 'full' => true],
                ['name' => 'limites_correction', 'label' => 'Le sens et les formulations techniques doivent-ils être conservés strictement ?', 'type' => 'select', 'options' => ['Oui, aucune reformulation de fond', 'Reformulations légères autorisées', 'À valider avec moi']],
            ]],
        ],
        ['Document avec corrections visibles', 'Version finale propre', 'Document Word avec suivi des modifications', 'PDF final']
    ),
];

$studioBriefs = [
    'studio_photographie_professionnelle' => [
        'label' => 'photographie professionnelle',
        'sections' => [
            ['title' => 'Séance photo et objectifs', 'fields' => [
                ['name' => 'type_seance', 'label' => 'Type de séance', 'type' => 'select', 'required' => true, 'options' => ['Corporate / entreprise', 'Portrait professionnel', 'Équipe', 'Institutionnelle', 'Éditoriale', 'Autre']],
                ['name' => 'objectifs_photo', 'label' => 'Objectif des photos et public visé', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'nombre_sujets', 'label' => 'Nombre de personnes / sujets à photographier', 'type' => 'text'],
                ['name' => 'lieu_photo', 'label' => 'Lieu souhaité et disponibilité du lieu', 'type' => 'text'],
                ['name' => 'style_photo', 'label' => 'Style recherché', 'type' => 'select', 'options' => ['Naturel', 'Corporate', 'Éditorial', 'Artistique', 'Studio', 'À recommander']],
            ]],
            ['title' => 'Organisation et autorisations', 'fields' => [
                ['name' => 'organisation_photo', 'label' => 'Tenues, accessoires ou mise en scène', 'type' => 'textarea', 'full' => true],
                ['name' => 'droits_photo', 'label' => 'Supports, durée et territoire de diffusion', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'autorisations_photo', 'label' => 'Autorisations des personnes / du lieu disponibles ?', 'type' => 'select', 'options' => ['Oui', 'À obtenir', 'Non applicable', 'À discuter']],
            ]],
        ],
        'deliverables' => ['Photos retouchées HD', 'Versions web optimisées', 'Galerie privée', 'Tirages papier'],
    ],
    'studio_photographie_de_produits' => [
        'label' => 'photographie de produits',
        'sections' => [
            ['title' => 'Produits et prises de vue', 'fields' => [
                ['name' => 'produits', 'label' => 'Liste, quantité et dimensions des produits', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'vues_produit', 'label' => 'Vues souhaitées par produit', 'type' => 'checkboxes', 'options' => ['Face', 'Dos', 'Profil', 'Détail / texture', 'En situation', 'Packaging'], 'full' => true],
                ['name' => 'fond_produit', 'label' => 'Fond / décor souhaité', 'type' => 'select', 'options' => ['Blanc', 'Uni de couleur', 'Transparent / détourage', 'Mise en scène', 'À définir']],
                ['name' => 'usage_produit', 'label' => 'Usage des images (catalogue, e-commerce, publicité...)', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'variantes_produit', 'label' => 'Variantes de couleur / taille à photographier', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Logistique produits', 'fields' => [
                ['name' => 'livraison_produits', 'label' => 'Mode et date de remise des produits', 'type' => 'text'],
                ['name' => 'retour_produits', 'label' => 'Instructions de conservation ou de retour', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        'deliverables' => ['Photos détourées', 'Photos sur fond', 'Photos mises en scène', 'Formats e-commerce', 'Images haute résolution'],
    ],
    'studio_photographie_evenementielle' => [
        'label' => 'photographie événementielle',
        'sections' => [
            ['title' => 'Événement et couverture photo', 'fields' => [
                ['name' => 'nom_evenement', 'label' => 'Nom, type et objectif de l’événement', 'type' => 'text', 'required' => true],
                ['name' => 'programme_evenement', 'label' => 'Programme avec horaires et moments importants', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'lieu_evenement', 'label' => 'Adresse, accès et consignes du lieu', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'participants_evenement', 'label' => 'Nombre approximatif de participants / VIP à photographier', 'type' => 'text'],
                ['name' => 'moments_evenement', 'label' => 'Photos prioritaires (discours, public, équipe, détails...)', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Accréditation et diffusion', 'fields' => [
                ['name' => 'acces_evenement', 'label' => 'Accréditation, accès backstage ou restrictions', 'type' => 'textarea', 'full' => true],
                ['name' => 'livraison_rapide', 'label' => 'Quelques photos à livrer pendant l’événement ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
                ['name' => 'publication_evenement', 'label' => 'Personne autorisée à valider les images avant publication', 'type' => 'text'],
            ]],
        ],
        'deliverables' => ['Galerie complète retouchée', 'Sélection express le jour même', 'Galerie privée', 'Photos presse'],
    ],
    'studio_photographie_de_portraits' => [
        'label' => 'photographie de portraits',
        'sections' => [
            ['title' => 'Personnes et intention', 'fields' => [
                ['name' => 'nombre_portraits', 'label' => 'Nombre de personnes à photographier', 'type' => 'text', 'required' => true],
                ['name' => 'usage_portraits', 'label' => 'Usage des portraits (CV, équipe, réseaux, presse...)', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'style_portraits', 'label' => 'Style et ambiance souhaités', 'type' => 'select', 'options' => ['Professionnel sobre', 'Corporate', 'Naturel', 'Créatif / artistique', 'Studio', 'Extérieur', 'À conseiller']],
                ['name' => 'cadrages_portraits', 'label' => 'Cadrages souhaités', 'type' => 'checkboxes', 'options' => ['Portrait individuel', 'Buste', 'Plein pied', 'Portrait d’équipe', 'Portrait en situation'], 'full' => true],
                ['name' => 'preferences_portraits', 'label' => 'Préférences, tenue, maquillage ou retouches', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Séance et autorisations', 'fields' => [
                ['name' => 'lieu_portraits', 'label' => 'Studio ou lieu de prise de vue', 'type' => 'select', 'options' => ['Studio', 'Locaux du client', 'Extérieur', 'À définir']],
                ['name' => 'usage_droits_portraits', 'label' => 'Supports et durée d’utilisation des portraits', 'type' => 'textarea', 'required' => true, 'full' => true],
            ]],
        ],
        'deliverables' => ['Portraits retouchés HD', 'Versions web', 'Fonds alternatifs', 'Photos d’équipe'],
    ],
    'studio_tournage_de_videos_publicitaires' => [
        'label' => 'tournage de vidéos publicitaires',
        'sections' => [
            ['title' => 'Campagne et concept vidéo', 'fields' => [
                ['name' => 'offre_video', 'label' => 'Produit, service ou offre à promouvoir', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'objectif_video', 'label' => 'Objectif, public cible et appel à l’action', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'concept_video', 'label' => 'Concept, scénario ou références', 'type' => 'textarea', 'full' => true],
                ['name' => 'duree_video', 'label' => 'Durée et nombre de versions souhaités', 'type' => 'text'],
                ['name' => 'talents_video', 'label' => 'Acteurs, présentateur, voix off ou figurants nécessaires ?', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Tournage et diffusion', 'fields' => [
                ['name' => 'lieux_video', 'label' => 'Lieux et autorisations de tournage', 'type' => 'textarea', 'full' => true],
                ['name' => 'formats_video', 'label' => 'Canaux et formats (horizontal, vertical, carré)', 'type' => 'checkboxes', 'options' => ['TV / cinéma', 'YouTube', 'Instagram / TikTok', 'Facebook', 'Site web', 'WhatsApp'], 'full' => true],
                ['name' => 'musique_video', 'label' => 'Musique, voix off ou éléments sous licence à prévoir', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        'deliverables' => ['Vidéo principale', 'Versions réseaux sociaux', 'Sous-titres', 'Fichiers sans habillage', 'Fichiers source'],
    ],
    'studio_production_de_clips_video' => [
        'label' => 'production de clips vidéo',
        'sections' => [
            ['title' => 'Artiste, morceau et intention', 'fields' => [
                ['name' => 'artiste_clip', 'label' => 'Nom de l’artiste / groupe et contacts', 'type' => 'text', 'required' => true],
                ['name' => 'titre_clip', 'label' => 'Titre, durée et genre du morceau', 'type' => 'text', 'required' => true],
                ['name' => 'concept_clip', 'label' => 'Concept, histoire et ambiance du clip', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'paroles_clip', 'label' => 'Paroles ou passages à mettre en scène', 'type' => 'textarea', 'full' => true],
                ['name' => 'references_clip', 'label' => 'Références visuelles et clips d’inspiration', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Production musicale et visuelle', 'fields' => [
                ['name' => 'lieux_clip', 'label' => 'Lieux, décors et autorisations nécessaires', 'type' => 'textarea', 'full' => true],
                ['name' => 'participants_clip', 'label' => 'Artistes, figurants, danseurs ou acteurs impliqués', 'type' => 'textarea', 'full' => true],
                ['name' => 'effets_clip', 'label' => 'Chorégraphie, effets spéciaux ou accessoires', 'type' => 'textarea', 'full' => true],
                ['name' => 'droits_clip', 'label' => 'Droits sur le morceau, les lieux et les personnes confirmés ?', 'type' => 'select', 'options' => ['Oui', 'En cours', 'À clarifier']],
            ]],
        ],
        'deliverables' => ['Clip master', 'Teaser', 'Extraits verticaux', 'Sous-titres', 'Captures / photos plateau'],
    ],
    'studio_realisation_de_films_institutionnels' => [
        'label' => 'réalisation de films institutionnels',
        'sections' => [
            ['title' => 'Organisation et objectifs du film', 'fields' => [
                ['name' => 'presentation_institution', 'label' => 'Présentez l’institution, ses missions et son public', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'objectif_institution', 'label' => 'Objectif du film et message central', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'audience_institution', 'label' => 'Audience et lieux de diffusion', 'type' => 'textarea', 'full' => true],
                ['name' => 'contenu_institution', 'label' => 'Activités, sites, personnes ou projets à présenter', 'type' => 'textarea', 'full' => true],
                ['name' => 'langues_institution', 'label' => 'Langue(s), sous-titres et voix off souhaités', 'type' => 'text'],
            ]],
            ['title' => 'Scénario, tournage et validation', 'fields' => [
                ['name' => 'script_institution', 'label' => 'Script, plan ou documents de référence disponibles ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'interviews_institution', 'label' => 'Interviews / porte-parole à prévoir', 'type' => 'textarea', 'full' => true],
                ['name' => 'validation_institution', 'label' => 'Personnes responsables de la validation', 'type' => 'text'],
                ['name' => 'autorisations_institution', 'label' => 'Autorisations de tournage et de diffusion', 'type' => 'select', 'options' => ['Disponibles', 'À obtenir', 'À accompagner']],
            ]],
        ],
        'deliverables' => ['Film master', 'Version courte', 'Sous-titres', 'Version multilingue', 'Fichiers source'],
    ],
    'studio_couverture_video_devenements' => [
        'label' => 'couverture vidéo d’événements',
        'sections' => [
            ['title' => 'Événement, programme et captation', 'fields' => [
                ['name' => 'type_evenement_video', 'label' => 'Type, date et nom de l’événement', 'type' => 'text', 'required' => true],
                ['name' => 'programme_video', 'label' => 'Programme détaillé avec horaires et séquences clés', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'lieu_video_evenement', 'label' => 'Adresse, accès, alimentation et contraintes du lieu', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'mode_captation', 'label' => 'Mode de couverture', 'type' => 'select', 'options' => ['Résumé / aftermovie', 'Captation intégrale', 'Captation multicaméra', 'Diffusion en direct', 'À définir']],
                ['name' => 'intervenants_video', 'label' => 'Discours, intervenants ou séquences à enregistrer', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Technique et livrables', 'fields' => [
                ['name' => 'son_video_evenement', 'label' => 'Reprise du son de scène / micros nécessaire ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À vérifier sur place']],
                ['name' => 'diffusion_video_evenement', 'label' => 'Diffusion en direct : plateforme et accès réseau', 'type' => 'textarea', 'full' => true],
                ['name' => 'autorisations_video_evenement', 'label' => 'Autorisations des intervenants et du site', 'type' => 'select', 'options' => ['Disponibles', 'À obtenir', 'À accompagner']],
            ]],
        ],
        'deliverables' => ['Aftermovie', 'Captation intégrale', 'Diffusion en direct', 'Extraits réseaux sociaux', 'Enregistrements des interventions'],
    ],
    'studio_montage_video_professionnel' => [
        'label' => 'montage vidéo professionnel',
        'sections' => [
            ['title' => 'Rushes et intention de montage', 'fields' => [
                ['name' => 'contenu_montage', 'label' => 'Type de vidéo et objectif du montage', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'volume_rushes', 'label' => 'Durée et volume approximatifs des rushes', 'type' => 'text', 'required' => true],
                ['name' => 'transfert_rushes', 'label' => 'Lien / méthode de transfert et organisation des fichiers', 'type' => 'textarea', 'full' => true],
                ['name' => 'structure_montage', 'label' => 'Ordre des séquences, prises retenues ou script', 'type' => 'textarea', 'full' => true],
                ['name' => 'references_montage', 'label' => 'Références de style, rythme et transitions', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Habillage et exports', 'fields' => [
                ['name' => 'habillage_montage', 'label' => 'Titres, logo, sous-titres, musique ou voix off à intégrer', 'type' => 'textarea', 'full' => true],
                ['name' => 'formats_montage', 'label' => 'Formats d’image et plateformes de diffusion', 'type' => 'checkboxes', 'options' => ['16:9 horizontal', '9:16 vertical', '1:1 carré', 'YouTube', 'Réseaux sociaux', 'Projection'], 'full' => true],
                ['name' => 'projet_montage', 'label' => 'Projet de montage modifiable requis ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
            ]],
        ],
        'deliverables' => ['Vidéo finale', 'Déclinaisons de formats', 'Sous-titres', 'Projet de montage', 'Fichiers intermédiaires'],
    ],
    'studio_etalonnage_et_correction_des_couleurs' => [
        'label' => 'étalonnage et correction des couleurs',
        'sections' => [
            ['title' => 'Sources et intention colorimétrique', 'fields' => [
                ['name' => 'sources_couleur', 'label' => 'Caméra, codec, profil colorimétrique et format des sources', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'volume_couleur', 'label' => 'Durée, nombre de plans et état du montage', 'type' => 'text', 'required' => true],
                ['name' => 'look_couleur', 'label' => 'Look / ambiance colorimétrique recherchée', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'references_couleur', 'label' => 'Images ou vidéos de référence', 'type' => 'textarea', 'full' => true],
                ['name' => 'problemes_couleur', 'label' => 'Problèmes à corriger (balance, exposition, raccords...)', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Chaîne de livraison', 'fields' => [
                ['name' => 'espace_couleur', 'label' => 'Espace colorimétrique / destination de diffusion', 'type' => 'select', 'options' => ['Rec.709', 'HDR', 'Web / réseaux sociaux', 'Cinéma / DCP', 'À confirmer']],
                ['name' => 'sorties_couleur', 'label' => 'Formats d’export et fichiers de projet nécessaires', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        'deliverables' => ['Master étalonné', 'Exports par plateforme', 'Projet colorimétrique', 'Référence avant / après'],
    ],
    'studio_enregistrement_audio' => [
        'label' => 'enregistrement audio',
        'sections' => [
            ['title' => 'Projet et séance', 'fields' => [
                ['name' => 'type_enregistrement', 'label' => 'Type d’enregistrement', 'type' => 'select', 'required' => true, 'options' => ['Voix / narration', 'Interview', 'Podcast', 'Musique', 'Doublage', 'Annonce', 'Autre']],
                ['name' => 'contenu_audio', 'label' => 'Contenu à enregistrer et objectif', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'participants_audio', 'label' => 'Nombre d’intervenants / musiciens', 'type' => 'text'],
                ['name' => 'langue_audio', 'label' => 'Langue(s), accent et prononciations spécifiques', 'type' => 'text'],
                ['name' => 'volume_audio', 'label' => 'Durée finale souhaitée et volume de contenu', 'type' => 'text'],
            ]],
            ['title' => 'Technique et fichiers', 'fields' => [
                ['name' => 'session_audio', 'label' => 'Prise de son seule ou ingénieur du son inclus ?', 'type' => 'select', 'options' => ['Enregistrement seul', 'Avec ingénieur du son', 'À discuter']],
                ['name' => 'traitement_audio', 'label' => 'Montage, nettoyage, mixage ou mastering requis ?', 'type' => 'checkboxes', 'options' => ['Nettoyage', 'Montage', 'Mixage', 'Mastering', 'Aucun'], 'full' => true],
                ['name' => 'format_audio', 'label' => 'Formats, fréquence et profondeur de bits demandés', 'type' => 'text'],
            ]],
        ],
        'deliverables' => ['Fichiers WAV', 'Fichiers MP3', 'Pistes séparées / stems', 'Audio nettoyé', 'Projet audio'],
    ],
    'studio_production_de_podcasts' => [
        'label' => 'production de podcasts',
        'sections' => [
            ['title' => 'Concept éditorial et épisodes', 'fields' => [
                ['name' => 'concept_podcast', 'label' => 'Sujet, concept et objectif du podcast', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'audience_podcast', 'label' => 'Public visé et ton éditorial', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'format_podcast', 'label' => 'Format d’émission', 'type' => 'select', 'options' => ['Interview', 'Conversation / table ronde', 'Chronique solo', 'Reportage', 'Narratif', 'Autre']],
                ['name' => 'episodes_podcast', 'label' => 'Nombre d’épisodes, durée et fréquence', 'type' => 'text', 'required' => true],
                ['name' => 'invites_podcast', 'label' => 'Invités, animateurs et intervenants', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Production et publication', 'fields' => [
                ['name' => 'services_podcast', 'label' => 'Étapes de production nécessaires', 'type' => 'checkboxes', 'options' => ['Préparation / conducteur', 'Enregistrement studio', 'Enregistrement à distance', 'Montage', 'Jingle / habillage', 'Transcription', 'Publication'], 'full' => true],
                ['name' => 'plateformes_podcast', 'label' => 'Plateformes de diffusion prévues', 'type' => 'text'],
                ['name' => 'identite_podcast', 'label' => 'Nom, logo et identité sonore déjà disponibles ?', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        'deliverables' => ['Épisodes finalisés', 'Extraits promotionnels', 'Jingle', 'Transcription', 'Fichiers source'],
    ],
    'studio_doublage_et_voix_off' => [
        'label' => 'doublage et voix off',
        'sections' => [
            ['title' => 'Script et œuvre à doubler', 'fields' => [
                ['name' => 'type_voix', 'label' => 'Type de prestation', 'type' => 'select', 'required' => true, 'options' => ['Voix off', 'Doublage', 'Narration', 'Publicité', 'E-learning', 'Annonce', 'Autre']],
                ['name' => 'script_voix', 'label' => 'Script final ou texte à enregistrer', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'duree_voix', 'label' => 'Durée cible et nombre de versions', 'type' => 'text'],
                ['name' => 'langue_voix', 'label' => 'Langue, accent et prononciations', 'type' => 'text', 'required' => true],
                ['name' => 'synchronisation_voix', 'label' => 'Synchronisation à l’image / timecodes nécessaires ?', 'type' => 'select', 'options' => ['Oui, timecodes fournis', 'Oui, à préparer', 'Non']],
            ]],
            ['title' => 'Direction et droits d’utilisation', 'fields' => [
                ['name' => 'profil_voix', 'label' => 'Profil vocal souhaité (timbre, âge, genre, ton)', 'type' => 'textarea', 'full' => true],
                ['name' => 'references_voix', 'label' => 'Exemples ou références de voix', 'type' => 'textarea', 'full' => true],
                ['name' => 'diffusion_voix', 'label' => 'Supports, territoire et durée d’exploitation', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'voix_traduction', 'label' => 'Traduction / adaptation du texte requise ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Déjà fournie']],
            ]],
        ],
        'deliverables' => ['Voix seule non traitée', 'Voix montée et nettoyée', 'Fichiers synchronisés', 'Versions par paragraphe'],
    ],
    'studio_enregistrement_musical' => [
        'label' => 'enregistrement musical',
        'sections' => [
            ['title' => 'Artiste et projet musical', 'fields' => [
                ['name' => 'artiste_musique', 'label' => 'Nom de l’artiste / groupe et contact référent', 'type' => 'text', 'required' => true],
                ['name' => 'genre_musique', 'label' => 'Genre, titre(s) et objectif du projet', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'morceaux_musique', 'label' => 'Nombre de morceaux et durée approximative', 'type' => 'text', 'required' => true],
                ['name' => 'musiciens_musique', 'label' => 'Chanteurs, musiciens et instruments présents', 'type' => 'textarea', 'full' => true],
                ['name' => 'maquette_musique', 'label' => 'Maquette, tonalité, tempo ou références disponibles', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Séance et accompagnement technique', 'fields' => [
                ['name' => 'prises_musique', 'label' => 'Prises live ou instruments enregistrés séparément ?', 'type' => 'select', 'options' => ['Live ensemble', 'Pistes séparées', 'Voix uniquement', 'À définir']],
                ['name' => 'ingenieur_musique', 'label' => 'Ingénieur du son / direction artistique souhaité ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
                ['name' => 'accompagnement_musique', 'label' => 'Arrangement, musiciens additionnels ou production nécessaires', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        'deliverables' => ['Pistes brutes séparées', 'Mix de référence', 'Version finale', 'Instrumental', 'Version a cappella'],
    ],
    'studio_mixage_et_mastering_audio' => [
        'label' => 'mixage et mastering audio',
        'sections' => [
            ['title' => 'Sources et état du projet', 'fields' => [
                ['name' => 'type_mixage', 'label' => 'Type de projet audio', 'type' => 'select', 'required' => true, 'options' => ['Chanson / musique', 'Podcast', 'Voix off', 'Film / vidéo', 'Publicité', 'Autre']],
                ['name' => 'pistes_mixage', 'label' => 'Nombre de pistes et format des fichiers sources', 'type' => 'text', 'required' => true],
                ['name' => 'organisation_mixage', 'label' => 'Organisation, nommage et fréquence d’échantillonnage des pistes', 'type' => 'textarea', 'full' => true],
                ['name' => 'references_mixage', 'label' => 'Références sonores et équilibre recherché', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'etat_mixage', 'label' => 'Traitements déjà effectués ou problèmes connus', 'type' => 'textarea', 'full' => true],
            ]],
            ['title' => 'Master et plateformes', 'fields' => [
                ['name' => 'traitement_mixage', 'label' => 'Prestations nécessaires', 'type' => 'checkboxes', 'options' => ['Nettoyage', 'Édition', 'Mixage', 'Mastering', 'Versions instrumentales', 'Versions radio'], 'full' => true],
                ['name' => 'plateformes_mixage', 'label' => 'Plateformes / destination de diffusion', 'type' => 'text'],
                ['name' => 'livraison_mixage', 'label' => 'Formats de master ou niveau sonore demandé', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        'deliverables' => ['Master WAV haute résolution', 'MP3 d’écoute', 'Instrumental / acapella', 'Stems', 'Versions radio / plateformes'],
    ],
    'studio_realisation_de_reportages' => [
        'label' => 'réalisation de reportages',
        'sections' => [
            ['title' => 'Sujet et approche éditoriale', 'fields' => [
                ['name' => 'sujet_reportage', 'label' => 'Sujet, angle et message du reportage', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'contexte_reportage', 'label' => 'Contexte, faits et personnes concernées', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'sources_reportage', 'label' => 'Sources, documents et contacts à interviewer', 'type' => 'textarea', 'full' => true],
                ['name' => 'lieux_reportage', 'label' => 'Lieux, dates et accès de tournage', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'duree_reportage', 'label' => 'Durée finale et format de diffusion', 'type' => 'text'],
            ]],
            ['title' => 'Éthique, autorisations et livrables', 'fields' => [
                ['name' => 'autorisations_reportage', 'label' => 'Accords de tournage et consentements requis', 'type' => 'textarea', 'full' => true],
                ['name' => 'verification_reportage', 'label' => 'Référent pour validation factuelle et droit de réponse', 'type' => 'text'],
                ['name' => 'diffusion_reportage', 'label' => 'Média, audience et territoire de diffusion', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        'deliverables' => ['Reportage final', 'Version courte', 'Transcription / sous-titres', 'Rushes sélectionnés', 'Photos de tournage'],
    ],
    'studio_location_de_studio_et_de_materiel_audiovisuel' => [
        'label' => 'location de studio et de matériel audiovisuel',
        'sections' => [
            ['title' => 'Réservation et projet', 'fields' => [
                ['name' => 'type_reservation', 'label' => 'Besoin de réservation', 'type' => 'checkboxes', 'required' => true, 'options' => ['Studio / plateau', 'Caméra', 'Éclairage', 'Son / microphones', 'Accessoires', 'Autre matériel'], 'full' => true],
                ['name' => 'projet_location', 'label' => 'Description du projet et usage prévu', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'date_debut_location', 'label' => 'Date et heure de début souhaitées', 'type' => 'date', 'required' => true],
                ['name' => 'date_fin_location', 'label' => 'Date et heure de fin / retour prévues', 'type' => 'date', 'required' => true],
                ['name' => 'quantites_location', 'label' => 'Quantités, modèles ou caractéristiques nécessaires', 'type' => 'textarea', 'required' => true, 'full' => true],
            ]],
            ['title' => 'Logistique et conditions', 'fields' => [
                ['name' => 'operateur_location', 'label' => 'Technicien ou opérateur à prévoir ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
                ['name' => 'installation_location', 'label' => 'Installation, transport, alimentation ou accessoires requis', 'type' => 'textarea', 'full' => true],
                ['name' => 'retrait_location', 'label' => 'Personne autorisée pour le retrait et le retour', 'type' => 'text'],
                ['name' => 'assurance_location', 'label' => 'Assurance / garantie et exigences particulières', 'type' => 'textarea', 'full' => true],
            ]],
        ],
        'deliverables' => ['Réservation de studio', 'Matériel préparé', 'Assistance technique', 'Installation / opérateur'],
    ],
];

$buildStudioBrief = static function (array $brief): array {
    return [
        'title' => 'Questionnaire — Brief de '.$brief['label'],
        'description' => 'Décrivez votre besoin de '.$brief['label'].' pour permettre à l’équipe Studio de préparer le dispositif, le devis et les livrables adaptés.',
        'sections' => array_merge([
            [
                'title' => '1. Informations du client',
                'fields' => [
                    ['name' => 'organisation', 'label' => 'Nom de l’entreprise / organisation ou du particulier', 'type' => 'text', 'required' => true],
                    ['name' => 'responsable', 'label' => 'Nom du responsable du projet', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'telephone', 'label' => 'Téléphone / WhatsApp', 'type' => 'tel', 'required' => true],
                ],
            ],
            [
                'title' => '2. Projet et objectifs',
                'fields' => [
                    ['name' => 'nom_projet', 'label' => 'Nom du projet', 'type' => 'text', 'required' => true],
                    ['name' => 'objectif_projet', 'label' => 'Objectif, public cible et résultat attendu', 'type' => 'textarea', 'required' => true, 'full' => true],
                    ['name' => 'description_projet', 'label' => 'Description et contexte du projet', 'type' => 'textarea', 'full' => true],
                ],
            ],
        ], $brief['sections'], [
            [
                'title' => 'Planning, budget et livraison',
                'fields' => [
                    ['name' => 'date_livraison', 'label' => 'Date souhaitée de livraison / réalisation', 'type' => 'date', 'required' => true],
                    ['name' => 'budget_projet', 'label' => 'Budget estimatif', 'type' => 'text', 'placeholder' => 'Montant et devise'],
                    ['name' => 'livrables_projet', 'label' => 'Livrables souhaités', 'type' => 'checkboxes', 'options' => $brief['deliverables'], 'full' => true],
                    ['name' => 'droits_projet', 'label' => 'Supports, durée et territoire de diffusion / droits d’utilisation', 'type' => 'textarea', 'full' => true],
                    ['name' => 'remarques_projet', 'label' => 'Contraintes ou informations complémentaires', 'type' => 'textarea', 'full' => true],
                ],
            ],
        ]),
    ];
};

$developmentBriefs = [
    'developpement_creation_de_sites_web_vitrines' => [
        'label' => 'création de site web vitrine',
        'section' => 'Présence en ligne et contenu',
        'fields' => [
            ['name' => 'pages_site', 'label' => 'Pages et rubriques souhaitées', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'public_site', 'label' => 'Public visé et action attendue des visiteurs', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'contenus_site', 'label' => 'Textes, photos, logo et identité visuelle disponibles', 'type' => 'textarea', 'full' => true],
            ['name' => 'fonctions_site', 'label' => 'Fonctionnalités souhaitées', 'type' => 'checkboxes', 'options' => ['Formulaire de contact', 'Carte / itinéraire', 'Blog / actualités', 'Galerie', 'Multilingue', 'Prise de rendez-vous', 'Espace privé'], 'full' => true],
        ],
        'deliverables' => ['Site responsive publié', 'Gestion de contenu', 'Formation à l’administration', 'Documentation', 'Maintenance initiale'],
    ],
    'developpement_creation_de_sites_e_commerce' => [
        'label' => 'création de site e-commerce',
        'section' => 'Catalogue, commandes et commerce',
        'fields' => [
            ['name' => 'produits', 'label' => 'Nombre de produits et catégories au lancement', 'type' => 'text', 'required' => true],
            ['name' => 'options_produits', 'label' => 'Variantes, stocks, tarifs ou promotions à gérer', 'type' => 'textarea', 'full' => true],
            ['name' => 'parcours_achat', 'label' => 'Livraison, retrait, retours et parcours de commande', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'paiements', 'label' => 'Moyens de paiement souhaités', 'type' => 'checkboxes', 'options' => ['Mobile money', 'Carte bancaire', 'Virement', 'Paiement à la livraison', 'Autre'], 'full' => true],
            ['name' => 'gestion_commandes', 'label' => 'Qui gérera les produits et commandes au quotidien ?', 'type' => 'textarea', 'full' => true],
        ],
        'deliverables' => ['Boutique en ligne', 'Import initial du catalogue', 'Configuration des paiements', 'Formation', 'Documentation'],
    ],
    'developpement_developpement_dapplications_web' => [
        'label' => 'développement d’application web',
        'section' => 'Utilisateurs et fonctionnalités',
        'fields' => [
            ['name' => 'utilisateurs', 'label' => 'Profils utilisateurs et droits d’accès', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'parcours_application', 'label' => 'Parcours principaux des utilisateurs', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'fonctionnalites', 'label' => 'Fonctionnalités prioritaires et règles métier', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'volume_application', 'label' => 'Nombre estimé d’utilisateurs et de données', 'type' => 'text'],
            ['name' => 'interfaces_application', 'label' => 'Interfaces / maquettes déjà disponibles ?', 'type' => 'select', 'options' => ['Oui, prêtes', 'Partiellement', 'Non, à concevoir']],
        ],
        'deliverables' => ['Application web', 'Interface d’administration', 'Code source', 'Documentation technique', 'Formation'],
    ],
    'developpement_developpement_dapplications_mobiles_android_et_ios' => [
        'label' => 'développement d’applications mobiles Android et iOS',
        'section' => 'Plateformes et expérience mobile',
        'fields' => [
            ['name' => 'plateformes_mobiles', 'label' => 'Plateformes visées', 'type' => 'checkboxes', 'required' => true, 'options' => ['Android', 'iOS', 'Tablettes', 'Version web complémentaire'], 'full' => true],
            ['name' => 'utilisateurs_mobiles', 'label' => 'Profils utilisateurs et modes de connexion', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'fonctions_mobiles', 'label' => 'Fonctionnalités, notifications et usages hors ligne', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'appareils_mobiles', 'label' => 'Appareils, langues et pays de lancement', 'type' => 'textarea', 'full' => true],
            ['name' => 'publication_mobiles', 'label' => 'Comptes développeur Play Store / App Store disponibles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À créer / accompagner']],
        ],
        'deliverables' => ['Application Android', 'Application iOS', 'Code source', 'Publication sur les stores', 'Documentation'],
    ],
    'developpement_developpement_de_logiciels_de_gestion' => [
        'label' => 'développement de logiciel de gestion',
        'section' => 'Activité et processus de gestion',
        'fields' => [
            ['name' => 'processus_gestion', 'label' => 'Processus à gérer et difficultés actuelles', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'modules_gestion', 'label' => 'Modules souhaités', 'type' => 'checkboxes', 'options' => ['Clients / CRM', 'Ventes', 'Stock', 'Achats', 'Facturation', 'Comptabilité', 'Ressources humaines', 'Rapports'], 'full' => true],
            ['name' => 'roles_gestion', 'label' => 'Services, profils et niveaux d’autorisation', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'donnees_gestion', 'label' => 'Données ou fichiers existants à reprendre', 'type' => 'textarea', 'full' => true],
            ['name' => 'utilisateurs_gestion', 'label' => 'Nombre d’utilisateurs et sites concernés', 'type' => 'text'],
        ],
        'deliverables' => ['Logiciel de gestion', 'Migration des données', 'Formation utilisateurs', 'Documentation', 'Support au démarrage'],
    ],
    'developpement_developpement_de_plateformes_numeriques' => [
        'label' => 'développement de plateforme numérique',
        'section' => 'Écosystème et modèle de plateforme',
        'fields' => [
            ['name' => 'type_plateforme', 'label' => 'Type de plateforme et valeur proposée', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'acteurs_plateforme', 'label' => 'Rôles des utilisateurs / organisations participantes', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'interactions_plateforme', 'label' => 'Échanges, publications ou transactions à gérer', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'monetisation_plateforme', 'label' => 'Modèle économique ou modalités d’accès', 'type' => 'textarea', 'full' => true],
            ['name' => 'moderation_plateforme', 'label' => 'Validation, modération et signalement requis ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À définir']],
        ],
        'deliverables' => ['Plateforme numérique', 'Portail d’administration', 'Mise en production', 'Documentation', 'Formation'],
    ],
    'developpement_creation_de_systemes_de_reservation_en_ligne' => [
        'label' => 'création de système de réservation en ligne',
        'section' => 'Offres, disponibilités et réservation',
        'fields' => [
            ['name' => 'ressources_reservation', 'label' => 'Services, ressources ou espaces réservables', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'regles_reservation', 'label' => 'Durées, horaires, capacités, délais et règles de disponibilité', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'parcours_reservation', 'label' => 'Étapes de réservation, confirmation et annulation', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'paiements_reservation', 'label' => 'Acompte ou paiement en ligne nécessaire ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À définir']],
            ['name' => 'notifications_reservation', 'label' => 'Notifications souhaitées', 'type' => 'checkboxes', 'options' => ['Email', 'SMS', 'WhatsApp', 'Rappels automatiques'], 'full' => true],
        ],
        'deliverables' => ['Parcours de réservation', 'Calendrier de gestion', 'Notifications', 'Paiement en ligne', 'Guide administrateur'],
    ],
    'developpement_creation_de_systemes_de_paiement_en_ligne' => [
        'label' => 'création de système de paiement en ligne',
        'section' => 'Paiements et rapprochement',
        'fields' => [
            ['name' => 'modele_paiement', 'label' => 'Type de paiement à intégrer (achat, abonnement, acompte...)', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'moyens_paiement', 'label' => 'Prestataires et moyens de paiement souhaités', 'type' => 'checkboxes', 'required' => true, 'options' => ['Mobile money', 'Carte bancaire', 'Virement', 'Portefeuille numérique', 'Autre'], 'full' => true],
            ['name' => 'devises_paiement', 'label' => 'Devises, pays et montants concernés', 'type' => 'text', 'required' => true],
            ['name' => 'parcours_paiement', 'label' => 'Confirmation, reçu, remboursement et gestion des échecs', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'prestataire_paiement', 'label' => 'Compte marchand / prestataire déjà activé ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'En cours de création']],
            ['name' => 'note_securite_paiement', 'label' => 'Sécurité des accès', 'type' => 'textarea', 'default' => 'Ne communiquez aucun mot de passe, clé secrète, code OTP ou numéro complet de carte dans ce formulaire.'],
        ],
        'deliverables' => ['Intégration de paiement', 'Notifications de transaction', 'Rapprochement / rapports', 'Documentation de configuration', 'Tests en environnement sandbox'],
    ],
    'developpement_developpement_de_logiciels_sur_mesure' => [
        'label' => 'développement de logiciel sur mesure',
        'section' => 'Besoins métier et périmètre',
        'fields' => [
            ['name' => 'probleme_logiciel', 'label' => 'Problème métier à résoudre et objectifs mesurables', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'processus_logiciel', 'label' => 'Processus actuel, étapes et exceptions', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'fonctionnalites_logiciel', 'label' => 'Fonctionnalités prioritaires et critères de réussite', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'contraintes_logiciel', 'label' => 'Contraintes technologiques, réglementaires ou d’hébergement', 'type' => 'textarea', 'full' => true],
            ['name' => 'evolution_logiciel', 'label' => 'Évolutions envisagées après la première version', 'type' => 'textarea', 'full' => true],
        ],
        'deliverables' => ['Version initiale fonctionnelle', 'Code source', 'Documentation', 'Migration des données', 'Formation et accompagnement'],
    ],
    'developpement_developpement_dapi_et_services_web' => [
        'label' => 'développement d’API et de services web',
        'section' => 'Contrat d’API et échanges',
        'fields' => [
            ['name' => 'consommateurs_api', 'label' => 'Applications / systèmes consommateurs et propriétaires', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'ressources_api', 'label' => 'Ressources, opérations et données à exposer', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'protocole_api', 'label' => 'Protocoles ou formats attendus', 'type' => 'checkboxes', 'options' => ['REST / JSON', 'GraphQL', 'SOAP / XML', 'Webhooks', 'À définir'], 'full' => true],
            ['name' => 'auth_api', 'label' => 'Mode d’authentification ou exigences de sécurité connues', 'type' => 'textarea', 'full' => true],
            ['name' => 'volume_api', 'label' => 'Volumes, fréquence d’appels et limites attendus', 'type' => 'textarea', 'full' => true],
            ['name' => 'documentation_api', 'label' => 'Documentation / environnement de test existant ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Partiellement']],
        ],
        'deliverables' => ['API opérationnelle', 'Documentation OpenAPI', 'Environnement de test', 'Tests d’intégration', 'Guide de déploiement'],
    ],
    'developpement_creation_de_tableaux_de_bord_et_espaces_administrateurs' => [
        'label' => 'création de tableaux de bord et espaces administrateurs',
        'section' => 'Indicateurs, données et administration',
        'fields' => [
            ['name' => 'decisions_dashboard', 'label' => 'Décisions à faciliter avec le tableau de bord', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'indicateurs_dashboard', 'label' => 'Indicateurs, filtres et périodes à afficher', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'sources_dashboard', 'label' => 'Sources de données et fréquence de mise à jour', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'roles_dashboard', 'label' => 'Profils, permissions et actions d’administration', 'type' => 'textarea', 'full' => true],
            ['name' => 'exports_dashboard', 'label' => 'Exports et rapports souhaités', 'type' => 'checkboxes', 'options' => ['CSV / Excel', 'PDF', 'Impression', 'Envoi planifié par email'], 'full' => true],
        ],
        'deliverables' => ['Tableau de bord', 'Espace administrateur', 'Exports / rapports', 'Gestion des rôles', 'Guide utilisateur'],
    ],
    'developpement_automatisation_des_processus_dentreprise' => [
        'label' => 'automatisation des processus d’entreprise',
        'section' => 'Processus à automatiser',
        'fields' => [
            ['name' => 'processus_automatisation', 'label' => 'Décrivez le processus actuel étape par étape', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'declencheur_automatisation', 'label' => 'Événement qui doit déclencher l’automatisation', 'type' => 'text', 'required' => true],
            ['name' => 'regles_automatisation', 'label' => 'Règles, conditions, exceptions et validations humaines', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'outils_automatisation', 'label' => 'Outils et applications à connecter', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'volumes_automatisation', 'label' => 'Fréquence, volume et temps actuellement consacré', 'type' => 'text'],
            ['name' => 'alertes_automatisation', 'label' => 'Notifications, alertes et traces à conserver', 'type' => 'textarea', 'full' => true],
        ],
        'deliverables' => ['Flux automatisé', 'Intégrations configurées', 'Journalisation / alertes', 'Documentation', 'Formation des utilisateurs'],
    ],
    'developpement_integration_de_solutions_informatiques' => [
        'label' => 'intégration de solutions informatiques',
        'section' => 'Systèmes et intégration cible',
        'fields' => [
            ['name' => 'solutions_integration', 'label' => 'Solutions, logiciels ou équipements concernés', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'architecture_integration', 'label' => 'Architecture actuelle, versions et environnements', 'type' => 'textarea', 'full' => true],
            ['name' => 'flux_integration', 'label' => 'Données, flux ou synchronisations nécessaires', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'contraintes_integration', 'label' => 'Contraintes d’accès, réseau, sécurité ou disponibilité', 'type' => 'textarea', 'full' => true],
            ['name' => 'maintenance_integration', 'label' => 'Fenêtre de changement et continuité de service exigées', 'type' => 'text'],
        ],
        'deliverables' => ['Solution intégrée', 'Configuration documentée', 'Tests de bout en bout', 'Plan de retour arrière', 'Transfert de compétences'],
    ],
    'developpement_maintenance_et_evolution_de_logiciels' => [
        'label' => 'maintenance et évolution de logiciels',
        'section' => 'Application existante et intervention',
        'fields' => [
            ['name' => 'logiciel_existant', 'label' => 'Nom, version, technologie et hébergement du logiciel', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'problemes_logiciel', 'label' => 'Anomalies, étapes de reproduction et fréquence', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'impact_logiciel', 'label' => 'Impact métier et utilisateurs touchés', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'acces_logiciel', 'label' => 'Documentation, dépôt et environnement de test disponibles ?', 'type' => 'select', 'options' => ['Oui', 'Partiellement', 'Non']],
            ['name' => 'evolutions_logiciel', 'label' => 'Évolutions fonctionnelles souhaitées', 'type' => 'textarea', 'full' => true],
            ['name' => 'sauvegarde_logiciel', 'label' => 'Sauvegardes et procédure de restauration connues ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À vérifier']],
        ],
        'deliverables' => ['Diagnostic', 'Correctifs', 'Évolutions', 'Tests de non-régression', 'Notes de version'],
    ],
    'developpement_hebergement_et_deploiement_dapplications' => [
        'label' => 'hébergement et déploiement d’applications',
        'section' => 'Application et environnement cible',
        'fields' => [
            ['name' => 'application_deploiement', 'label' => 'Application, technologie et état de préparation', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'environnements_deploiement', 'label' => 'Environnements nécessaires', 'type' => 'checkboxes', 'options' => ['Développement', 'Test / préproduction', 'Production', 'Sauvegarde / reprise'], 'full' => true],
            ['name' => 'charge_deploiement', 'label' => 'Utilisateurs, trafic, stockage et croissance prévus', 'type' => 'textarea', 'full' => true],
            ['name' => 'domaine_deploiement', 'label' => 'Nom de domaine, DNS, certificats ou ressources existantes', 'type' => 'textarea', 'full' => true],
            ['name' => 'disponibilite_deploiement', 'label' => 'Disponibilité, sauvegarde et reprise après incident attendues', 'type' => 'textarea', 'required' => true, 'full' => true],
        ],
        'deliverables' => ['Application déployée', 'Configuration des environnements', 'Sauvegardes', 'Surveillance / alertes', 'Guide d’exploitation'],
    ],
    'developpement_gestion_de_bases_de_donnees' => [
        'label' => 'gestion de bases de données',
        'section' => 'Données, volumes et opérations',
        'fields' => [
            ['name' => 'type_base', 'label' => 'Base de données et version (si connues)', 'type' => 'text'],
            ['name' => 'besoin_base', 'label' => 'Besoin : conception, migration, optimisation ou administration', 'type' => 'checkboxes', 'required' => true, 'options' => ['Conception / modélisation', 'Migration', 'Optimisation', 'Nettoyage / dédoublonnage', 'Sauvegarde / restauration', 'Administration'], 'full' => true],
            ['name' => 'donnees_base', 'label' => 'Nature, volume, croissance et sensibilité des données', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'problemes_base', 'label' => 'Problèmes observés ou résultats attendus', 'type' => 'textarea', 'full' => true],
            ['name' => 'acces_base', 'label' => 'Environnements, accès disponibles et fenêtre d’intervention', 'type' => 'textarea', 'full' => true],
            ['name' => 'confidentialite_base', 'label' => 'Les données contiennent-elles des informations personnelles ou sensibles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À confirmer']],
        ],
        'deliverables' => ['Schéma / documentation', 'Base migrée', 'Rapport d’optimisation', 'Plan de sauvegarde', 'Procédure d’exploitation'],
    ],
    'developpement_integration_de_solutions_dintelligence_artificielle' => [
        'label' => 'intégration de solutions d’intelligence artificielle',
        'section' => 'Cas d’usage et données',
        'fields' => [
            ['name' => 'cas_usage_ia', 'label' => 'Tâche ou décision à améliorer avec l’IA', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'utilisateurs_ia', 'label' => 'Utilisateurs, volume et parcours d’utilisation', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'donnees_ia', 'label' => 'Données disponibles, qualité, droits et sensibilité', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'sorties_ia', 'label' => 'Résultats attendus et critères de qualité', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'controle_ia', 'label' => 'Validation humaine, traçabilité et limites nécessaires', 'type' => 'textarea', 'full' => true],
            ['name' => 'contraintes_ia', 'label' => 'Contraintes de confidentialité, pays ou hébergement', 'type' => 'textarea', 'full' => true],
        ],
        'deliverables' => ['Prototype évalué', 'Intégration applicative', 'Jeu de tests', 'Documentation', 'Formation utilisateurs'],
    ],
    'developpement_creation_de_solutions_saas' => [
        'label' => 'création de solution SaaS',
        'section' => 'Produit SaaS et modèle d’accès',
        'fields' => [
            ['name' => 'probleme_saas', 'label' => 'Problème client résolu et proposition de valeur', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'segments_saas', 'label' => 'Types de clients, espaces organisationnels et rôles', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'offres_saas', 'label' => 'Offres, abonnements, essai ou limites d’usage', 'type' => 'textarea', 'full' => true],
            ['name' => 'donnees_saas', 'label' => 'Données à isoler par client et besoins d’export / suppression', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'integrations_saas', 'label' => 'Intégrations, paiement, emails et notifications', 'type' => 'textarea', 'full' => true],
            ['name' => 'support_saas', 'label' => 'Support, disponibilité et supervision attendus', 'type' => 'textarea', 'full' => true],
        ],
        'deliverables' => ['Produit SaaS opérationnel', 'Gestion des espaces clients', 'Abonnements / facturation', 'Console d’administration', 'Documentation'],
    ],
    'developpement_assistance_technique_et_support_informatique' => [
        'label' => 'assistance technique et support informatique',
        'section' => 'Incident ou demande d’assistance',
        'fields' => [
            ['name' => 'equipement_support', 'label' => 'Matériel, logiciel ou service concerné', 'type' => 'text', 'required' => true],
            ['name' => 'incident_support', 'label' => 'Décrivez le problème et les étapes pour le reproduire', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'impact_support', 'label' => 'Impact, personnes concernées et urgence métier', 'type' => 'select', 'required' => true, 'options' => ['Bloquant / activité arrêtée', 'Majeur / travail dégradé', 'Mineur / question', 'Demande de configuration']],
            ['name' => 'debut_support', 'label' => 'Depuis quand le problème se produit-il ?', 'type' => 'text'],
            ['name' => 'changements_support', 'label' => 'Changements récents ou messages d’erreur (sans mot de passe)', 'type' => 'textarea', 'full' => true],
            ['name' => 'intervention_support', 'label' => 'Mode d’intervention possible', 'type' => 'select', 'options' => ['À distance', 'Sur site', 'Dépôt du matériel', 'À convenir']],
            ['name' => 'note_securite_support', 'label' => 'Sécurité des accès', 'type' => 'textarea', 'default' => 'Ne transmettez pas de mot de passe, code OTP, clé privée ou données bancaires dans ce formulaire.'],
        ],
        'deliverables' => ['Diagnostic', 'Résolution documentée', 'Recommandations', 'Maintenance préventive', 'Suivi / assistance'],
    ],
];

$buildDevelopmentBrief = static function (array $brief): array {
    return [
        'title' => 'Questionnaire — Brief de '.$brief['label'],
        'description' => 'Décrivez votre besoin, vos utilisateurs et vos contraintes afin que l’équipe Développement puisse cadrer la solution et préparer une proposition adaptée.',
        'sections' => [
            [
                'title' => '1. Informations du client',
                'fields' => [
                    ['name' => 'organisation', 'label' => 'Nom de l’entreprise / organisation ou du particulier', 'type' => 'text', 'required' => true],
                    ['name' => 'responsable', 'label' => 'Nom du responsable du projet', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'telephone', 'label' => 'Téléphone / WhatsApp', 'type' => 'tel', 'required' => true],
                    ['name' => 'secteur', 'label' => 'Secteur d’activité', 'type' => 'text'],
                    ['name' => 'localisation', 'label' => 'Pays et zone d’activité', 'type' => 'text'],
                ],
            ],
            [
                'title' => '2. Contexte et objectifs du projet',
                'fields' => [
                    ['name' => 'nom_projet', 'label' => 'Nom du projet', 'type' => 'text', 'required' => true],
                    ['name' => 'objectifs_projet', 'label' => 'Quel problème souhaitez-vous résoudre et quel résultat attendez-vous ?', 'type' => 'textarea', 'required' => true, 'full' => true],
                    ['name' => 'description_projet', 'label' => 'Décrivez le fonctionnement actuel et le contexte', 'type' => 'textarea', 'required' => true, 'full' => true],
                    ['name' => 'public_cible', 'label' => 'Utilisateurs concernés et leurs besoins', 'type' => 'textarea', 'required' => true, 'full' => true],
                    ['name' => 'references_projet', 'label' => 'Solutions, exemples ou maquettes de référence', 'type' => 'textarea', 'full' => true],
                ],
            ],
            [
                'title' => $brief['section'],
                'fields' => $brief['fields'],
            ],
            [
                'title' => '4. Contraintes, données et intégrations',
                'fields' => [
                    ['name' => 'systemes_existants', 'label' => 'Outils, systèmes ou données déjà utilisés', 'type' => 'textarea', 'full' => true],
                    ['name' => 'integrations', 'label' => 'Services tiers, plateformes ou équipements à connecter', 'type' => 'textarea', 'full' => true],
                    ['name' => 'contraintes_techniques', 'label' => 'Contraintes techniques, sécurité, confidentialité ou conformité', 'type' => 'textarea', 'full' => true],
                ],
            ],
            [
                'title' => '5. Planning, budget et livrables',
                'fields' => [
                    ['name' => 'date_livraison', 'label' => 'Date souhaitée de mise en service / livraison', 'type' => 'date', 'required' => true],
                    ['name' => 'budget', 'label' => 'Budget estimatif', 'type' => 'text', 'placeholder' => 'Montant et devise'],
                    ['name' => 'livrables', 'label' => 'Livrables ou accompagnement attendus', 'type' => 'checkboxes', 'options' => $brief['deliverables'], 'full' => true],
                    ['name' => 'maintenance', 'label' => 'Maintenance ou support après livraison nécessaire ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
                    ['name' => 'decideur', 'label' => 'Décideur et personnes chargées de valider le projet', 'type' => 'text'],
                    ['name' => 'remarques', 'label' => 'Autres informations ou priorités à prendre en compte', 'type' => 'textarea', 'full' => true],
                ],
            ],
        ],
    ];
};

$forms = [
    'graphisme' => [
        'graphisme_creation_de_logos' => [
            'title' => 'Questionnaire — Brief de création de logo',
            'description' => 'Formulaire professionnel pour recueillir le brief complet avant conception du logo.',
            'sections' => [
                [
                    'title' => '1. Informations sur l’entreprise',
                    'fields' => [
                        ['name' => 'nom', 'label' => 'Nom de la société', 'type' => 'text', 'required' => true, 'default' => 'EUNITAL'],
                        ['name' => 'secteur', 'label' => 'Secteur d’activité', 'type' => 'text', 'required' => true, 'default' => 'Hub digital, créativité, formations et développement du monde digital'],
                        ['name' => 'services', 'label' => 'Produits ou services proposés', 'type' => 'textarea', 'required' => true, 'full' => true, 'default' => 'EUNITAL est un hub regroupant plusieurs chambres indépendantes : développement informatique, cybersécurité, data science, génie logiciel, IA, graphisme, studios audio & vidéo, multimédia, marketing digital, création et gestion d’entreprise, financement de microentreprises et mise en relation de partenaires internationaux.'],
                        ['name' => 'cible', 'label' => 'Public cible', 'type' => 'textarea', 'required' => true, 'full' => true, 'default' => 'Jeunes, entreprises, structures religieuses, États, familles, étudiants et élèves.'],
                        ['name' => 'message', 'label' => 'Message clé à transmettre', 'type' => 'textarea', 'required' => true, 'full' => true, 'default' => 'EUNITAL est le hub du savoir digital et de la révolution numérique.'],
                    ],
                ],
                [
                    'title' => '2. Coordonnées',
                    'fields' => [
                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'default' => 'josueservicedigital@gmail.com'],
                        ['name' => 'tel1', 'label' => 'Téléphone principal', 'type' => 'tel', 'default' => '+22677986162'],
                        ['name' => 'tel2', 'label' => 'Téléphone secondaire', 'type' => 'tel', 'default' => '+212772376608'],
                    ],
                ],
                [
                    'title' => '3. Style et direction visuelle',
                    'fields' => [
                        ['name' => 'type', 'label' => 'Type de logo', 'type' => 'select', 'required' => true, 'options' => ['Logo combiné (texte + icône)', 'Logo typographique', 'Symbole / icône', 'Emblème', 'Autre'], 'default' => 'Logo combiné (texte + icône)'],
                        ['name' => 'style', 'label' => 'Style souhaité', 'type' => 'select', 'required' => true, 'options' => ['Moderne', 'Minimaliste', 'Premium', 'Technologique', 'Futuriste', 'Élégant', 'Institutionnel', 'Autre'], 'default' => 'Moderne'],
                        ['name' => 'ancien', 'label' => 'Avez-vous déjà un logo ?', 'type' => 'radio', 'options' => ['Oui', 'Non'], 'default' => 'Non', 'full' => true],
                        ['name' => 'elements', 'label' => 'Éléments visuels souhaités', 'type' => 'textarea', 'full' => true],
                        ['name' => 'eviter', 'label' => 'Éléments ou styles à éviter', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '4. Couleurs',
                    'fields' => [
                        ['name' => 'palette', 'label' => 'Avez-vous une palette spécifique ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À définir'], 'default' => 'Oui'],
                        ['name' => 'c1', 'label' => 'Couleur principale', 'type' => 'text', 'default' => '#10A7A3'],
                        ['name' => 'c2', 'label' => 'Couleur secondaire', 'type' => 'text', 'default' => '#1A1A1A'],
                        ['name' => 'c3', 'label' => 'Couleur complémentaire', 'type' => 'text', 'default' => '#FFFFFF'],
                        ['name' => 'regle', 'label' => 'Règle concernant les couleurs', 'type' => 'textarea', 'full' => true, 'default' => 'Le turquoise foncé #10A7A3 est la couleur principale signature et doit dominer.'],
                    ],
                ],
                [
                    'title' => '5. Typographie',
                    'fields' => [
                        ['name' => 'police', 'label' => 'Style de police', 'type' => 'select', 'options' => ['Sans serif', 'Serif', 'Géométrique', 'Manuscrite', 'Personnalisée', 'À définir'], 'default' => 'Sans serif'],
                        ['name' => 'graisse', 'label' => 'Graisse préférée', 'type' => 'select', 'options' => ['Fine', 'Normale', 'Semi-bold', 'Bold', 'Très bold'], 'default' => 'Semi-bold'],
                        ['name' => 'typo', 'label' => 'Autres indications', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '6. Supports d’utilisation',
                    'fields' => [
                        ['name' => 'supports', 'label' => 'Supports concernés', 'type' => 'checkboxes', 'options' => ['Dossiers administratifs', 'Sites web', 'Cartes de visite', 'Panneaux', 'Packaging', 'Réseaux sociaux'], 'full' => true],
                        ['name' => 'autres_supports', 'label' => 'Autres supports', 'type' => 'text', 'full' => true],
                    ],
                ],
                [
                    'title' => '7. Formats et livrables',
                    'fields' => [
                        ['name' => 'formats', 'label' => 'Formats souhaités', 'type' => 'checkboxes', 'options' => ['PNG', 'JPG', 'JPEG', 'SVG', 'PDF'], 'full' => true],
                        ['name' => 'versions', 'label' => 'Versions souhaitées', 'type' => 'checkboxes', 'options' => ['Couleur', 'Noir et blanc', 'Fond transparent', 'Fond sombre'], 'full' => true],
                    ],
                ],
                [
                    'title' => '8. Budget et délais',
                    'fields' => [
                        ['name' => 'budget_logo', 'label' => 'Budget du logo (DH)', 'type' => 'number', 'min' => 0, 'default' => '1000'],
                        ['name' => 'budget_charte', 'label' => 'Budget minimal charte graphique (DH)', 'type' => 'number', 'min' => 0, 'default' => '500'],
                        ['name' => 'charte', 'label' => 'Souhaitez-vous une charte graphique ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter'], 'default' => 'Oui'],
                        ['name' => 'delai', 'label' => 'Délai souhaité', 'type' => 'text', 'default' => 'Au plus tard demain'],
                    ],
                ],
                [
                    'title' => '9. Vision créative',
                    'fields' => [
                        ['name' => 'vision', 'label' => 'Que doit incarner le logo ?', 'type' => 'textarea', 'required' => true, 'full' => true, 'default' => 'Eunital est un hub du monde numérique et digital africain. Le logo doit incarner un hub du savoir, la digitalisation, la créativité et le numérique.'],
                        ['name' => 'personnalite', 'label' => 'Personnalité de la marque', 'type' => 'checkboxes', 'options' => ['Innovante', 'Professionnelle', 'Internationale', 'Créative', 'Intemporelle', 'Technologique'], 'full' => true],
                        ['name' => 'references', 'label' => 'Références / inspirations', 'type' => 'textarea', 'full' => true],
                        ['name' => 'remarques', 'label' => 'Remarques et instructions spécifiques', 'type' => 'textarea', 'full' => true, 'default' => 'Le logo doit être intemporel, fonctionner en couleur et en noir et blanc. Éviter tout ce qui fait ONG classique ou association basique. Nous voulons une marque internationale.'],
                    ],
                ],
                [
                    'title' => '10. Services complémentaires',
                    'fields' => [
                        ['name' => 'carte', 'label' => 'Souhaitez-vous une carte de visite personnalisée associée au logo ?', 'type' => 'select', 'options' => ['Je réfléchirai', 'Oui', 'Non', 'À discuter'], 'default' => 'Je réfléchirai'],
                    ],
                ],
            ],
        ],
        'graphisme_creation_de_chartes_graphiques' => [
            'title' => 'Questionnaire — Brief de création de charte graphique',
            'description' => 'Brief complet pour définir une identité visuelle cohérente, reconnaissable et facile à appliquer.',
            'sections' => [
                [
                    'title' => '1. Informations sur l’entreprise ou la marque',
                    'fields' => [
                        ['name' => 'nom', 'label' => 'Nom de l’entreprise / marque', 'type' => 'text', 'required' => true],
                        ['name' => 'secteur', 'label' => 'Secteur d’activité', 'type' => 'text', 'required' => true],
                        ['name' => 'presentation', 'label' => 'Présentez votre entreprise et vos activités', 'type' => 'textarea', 'required' => true, 'full' => true],
                        ['name' => 'cible', 'label' => 'Public cible', 'type' => 'textarea', 'required' => true, 'full' => true],
                        ['name' => 'valeurs', 'label' => 'Valeurs et mission de la marque', 'type' => 'textarea', 'required' => true, 'full' => true],
                        ['name' => 'positionnement', 'label' => 'Positionnement souhaité', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '2. Coordonnées et interlocuteur',
                    'fields' => [
                        ['name' => 'responsable', 'label' => 'Nom et prénom du responsable', 'type' => 'text', 'required' => true],
                        ['name' => 'fonction', 'label' => 'Fonction', 'type' => 'text'],
                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                        ['name' => 'telephone', 'label' => 'Téléphone / WhatsApp', 'type' => 'tel', 'required' => true],
                        ['name' => 'site', 'label' => 'Site web', 'type' => 'url', 'placeholder' => 'https://'],
                        ['name' => 'localisation', 'label' => 'Ville et pays', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => '3. État actuel de votre identité visuelle',
                    'fields' => [
                        ['name' => 'identite_existante', 'label' => 'Disposez-vous déjà d’une identité visuelle ?', 'type' => 'radio', 'required' => true, 'options' => ['Oui, complète', 'Partiellement', 'Non'], 'full' => true],
                        ['name' => 'elements_existants', 'label' => 'Quels éléments possédez-vous déjà ?', 'type' => 'checkboxes', 'options' => ['Logo', 'Palette de couleurs', 'Typographies', 'Modèles de documents', 'Visuels réseaux sociaux', 'Aucun'], 'full' => true],
                        ['name' => 'conserver', 'label' => 'Qu’est-ce qui doit être conservé, amélioré ou remplacé ?', 'type' => 'textarea', 'full' => true],
                        ['name' => 'liens_existants', 'label' => 'Liens vers les éléments existants', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '4. Objectifs de la charte graphique',
                    'fields' => [
                        ['name' => 'objectifs', 'label' => 'Objectifs prioritaires', 'type' => 'checkboxes', 'required' => true, 'options' => ['Uniformiser la communication', 'Renforcer la crédibilité', 'Moderniser l’image', 'Se différencier', 'Améliorer la reconnaissance', 'Accompagner un lancement', 'Autre'], 'full' => true],
                        ['name' => 'probleme', 'label' => 'Quel problème votre identité visuelle doit-elle résoudre ?', 'type' => 'textarea', 'full' => true],
                        ['name' => 'sentiment', 'label' => 'Quel sentiment le public doit-il ressentir au contact de votre marque ?', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '5. Logo et règles d’utilisation',
                    'fields' => [
                        ['name' => 'logo_finalise', 'label' => 'Le logo est-il déjà finalisé ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'En cours de création']],
                        ['name' => 'logo_charte', 'label' => 'Souhaitez-vous intégrer le logo à la charte ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À discuter']],
                        ['name' => 'versions_logo', 'label' => 'Versions du logo à prévoir', 'type' => 'checkboxes', 'options' => ['Version principale', 'Version secondaire', 'Symbole seul', 'Version horizontale', 'Version verticale', 'Noir et blanc'], 'full' => true],
                        ['name' => 'regles_logo', 'label' => 'Règles ou contraintes particulières concernant le logo', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '6. Couleurs de marque',
                    'fields' => [
                        ['name' => 'couleurs_existantes', 'label' => 'Avez-vous déjà des couleurs définies ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À définir avec EUNITAL']],
                        ['name' => 'conserver_couleurs', 'label' => 'Souhaitez-vous conserver les couleurs actuelles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Les faire évoluer', 'Sans préférence']],
                        ['name' => 'couleur_principale', 'label' => 'Couleur principale souhaitée', 'type' => 'text'],
                        ['name' => 'couleurs_secondaires', 'label' => 'Couleurs secondaires souhaitées', 'type' => 'text'],
                        ['name' => 'couleurs_eviter', 'label' => 'Couleurs à éviter et raison', 'type' => 'textarea', 'full' => true],
                        ['name' => 'ambiance_couleur', 'label' => 'Ambiance colorée recherchée', 'type' => 'checkboxes', 'options' => ['Sobre', 'Dynamique', 'Élégante', 'Chaleureuse', 'Audacieuse', 'Naturelle'], 'full' => true],
                    ],
                ],
                [
                    'title' => '7. Typographies et éléments graphiques',
                    'fields' => [
                        ['name' => 'typographie', 'label' => 'Style typographique souhaité', 'type' => 'select', 'options' => ['Sans serif / moderne', 'Serif / classique', 'Géométrique', 'Élégant', 'Manuscrit', 'À définir']],
                        ['name' => 'nb_polices', 'label' => 'Nombre de familles de polices souhaité', 'type' => 'select', 'options' => ['À définir', 'Une seule', 'Deux', 'Trois']],
                        ['name' => 'elements_graphiques', 'label' => 'Éléments graphiques à intégrer', 'type' => 'checkboxes', 'options' => ['Motifs et textures', 'Formes géométriques', 'Icônes', 'Illustrations', 'Photographies', 'Pictogrammes', 'Aucun élément supplémentaire'], 'full' => true],
                        ['name' => 'details_graphiques', 'label' => 'Indications complémentaires sur les polices ou les éléments graphiques', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '8. Supports et applications de la charte',
                    'fields' => [
                        ['name' => 'supports', 'label' => 'Supports concernés', 'type' => 'checkboxes', 'options' => ['Papeterie et documents administratifs', 'Cartes de visite', 'En-têtes et signatures email', 'Présentation PowerPoint', 'Site web', 'Réseaux sociaux', 'Affiches et flyers', 'Brochures et catalogues', 'Packaging et étiquettes', 'Signalétique et enseignes', 'Textile et uniformes', 'Autres'], 'full' => true],
                        ['name' => 'autres_supports', 'label' => 'Autres supports ou applications à préciser', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '9. Contenu du guide de charte graphique',
                    'fields' => [
                        ['name' => 'contenu_charte', 'label' => 'Rubriques souhaitées dans le document final', 'type' => 'checkboxes', 'options' => ['Présentation de la marque', 'Présentation et construction du logo', 'Zone de protection et taille minimale', 'Utilisations autorisées et interdites', 'Palette avec codes couleurs', 'Hiérarchie typographique', 'Éléments graphiques et iconographie', 'Règles de mise en page', 'Exemples d’applications', 'Bonnes pratiques de communication', 'Autres'], 'full' => true],
                        ['name' => 'autres_rubriques', 'label' => 'Rubriques ou consignes supplémentaires', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '10. Style, références et concurrence',
                    'fields' => [
                        ['name' => 'style_global', 'label' => 'Style global recherché', 'type' => 'select', 'options' => ['Minimaliste', 'Moderne', 'Premium', 'Institutionnel', 'Créatif', 'Technologique', 'Élégant', 'Audacieux', 'À définir']],
                        ['name' => 'liberte', 'label' => 'Degré de liberté créative', 'type' => 'select', 'options' => ['Proposition libre du designer', 'Respect strict de mes indications', 'Équilibre entre les deux']],
                        ['name' => 'concurrents', 'label' => 'Marques ou concurrents dont vous appréciez l’identité visuelle', 'type' => 'textarea', 'full' => true],
                        ['name' => 'references', 'label' => 'Liens vers des références ou inspirations', 'type' => 'textarea', 'full' => true],
                        ['name' => 'aime_references', 'label' => 'Ce que vous aimez dans ces références', 'type' => 'textarea', 'full' => true],
                        ['name' => 'a_eviter', 'label' => 'Styles, symboles ou pratiques à éviter absolument', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '11. Livrables, budget et délais',
                    'fields' => [
                        ['name' => 'formats', 'label' => 'Format du guide final souhaité', 'type' => 'checkboxes', 'options' => ['PDF haute qualité', 'PDF optimisé pour écran', 'Fichiers sources modifiables', 'Version imprimable'], 'full' => true],
                        ['name' => 'budget', 'label' => 'Budget prévu', 'type' => 'text'],
                        ['name' => 'delai', 'label' => 'Date limite souhaitée', 'type' => 'date'],
                        ['name' => 'priorite', 'label' => 'Niveau de priorité', 'type' => 'select', 'options' => ['Normal', 'Urgent', 'Flexible']],
                        ['name' => 'origine', 'label' => 'Comment avez-vous connu EUNITAL ?', 'type' => 'text'],
                        ['name' => 'besoins', 'label' => 'Autres besoins graphiques à prévoir', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '12. Validation et remarques finales',
                    'fields' => [
                        ['name' => 'validation', 'label' => 'Qui validera la charte graphique ?', 'type' => 'text', 'full' => true],
                        ['name' => 'contraintes', 'label' => 'Contraintes internes ou techniques à respecter', 'type' => 'textarea', 'full' => true],
                        ['name' => 'remarques', 'label' => 'Remarques et instructions complémentaires', 'type' => 'textarea', 'full' => true],
                        ['name' => 'confirmation', 'label' => 'Confirmation des informations transmises', 'type' => 'checkboxes', 'required' => true, 'options' => ['Je confirme que les informations communiquées sont exactes et serviront de base au projet.'], 'full' => true],
                    ],
                ],
            ],
        ],
        'graphisme_conception_daffiches_publicitaires' => [
            'title' => 'Questionnaire — Brief de conception d’affiche publicitaire',
            'description' => 'Brief complet pour cadrer la campagne, le public, le message et la conception de l’affiche.',
            'sections' => [
                [
                    'title' => '1. Informations sur le client',
                    'fields' => [
                        ['name' => 'organisation', 'label' => 'Nom de l’entreprise / organisation', 'type' => 'text', 'required' => true],
                        ['name' => 'responsable', 'label' => 'Nom du responsable', 'type' => 'text', 'required' => true],
                        ['name' => 'fonction', 'label' => 'Fonction', 'type' => 'text'],
                        ['name' => 'secteur', 'label' => 'Secteur d’activité', 'type' => 'text'],
                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                        ['name' => 'telephone', 'label' => 'Téléphone / WhatsApp', 'type' => 'tel', 'required' => true],
                        ['name' => 'localisation', 'label' => 'Ville et pays', 'type' => 'text'],
                        ['name' => 'presence_web', 'label' => 'Site web / réseaux sociaux', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => '2. Présentation de la campagne',
                    'fields' => [
                        ['name' => 'campagne', 'label' => 'Nom ou titre de la campagne', 'type' => 'text', 'required' => true],
                        ['name' => 'type_campagne', 'label' => 'Type de campagne', 'type' => 'select', 'required' => true, 'options' => ['Promotion commerciale', 'Lancement de produit ou service', 'Événement / manifestation', 'Annonce institutionnelle', 'Sensibilisation / information', 'Recrutement', 'Offre spéciale / soldes', 'Autre']],
                        ['name' => 'description', 'label' => 'Produit, service, événement ou sujet à promouvoir', 'type' => 'textarea', 'required' => true, 'full' => true],
                        ['name' => 'objectif', 'label' => 'Objectif principal', 'type' => 'select', 'required' => true, 'options' => ['Attirer de nouveaux clients', 'Générer des ventes / commandes', 'Faire connaître une offre', 'Augmenter la participation à un événement', 'Informer ou sensibiliser', 'Renforcer la notoriété', 'Autre']],
                        ['name' => 'action', 'label' => 'Action attendue du public', 'type' => 'text', 'placeholder' => 'Acheter, appeler, s’inscrire, visiter...'],
                        ['name' => 'message', 'label' => 'Message essentiel à retenir', 'type' => 'textarea', 'required' => true, 'full' => true],
                        ['name' => 'avantage', 'label' => 'Offre, avantage ou argument principal à mettre en avant', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '3. Public cible',
                    'fields' => [
                        ['name' => 'public', 'label' => 'À qui s’adresse cette affiche ?', 'type' => 'textarea', 'required' => true, 'full' => true],
                        ['name' => 'cibles', 'label' => 'Public prioritaire', 'type' => 'checkboxes', 'options' => ['Grand public', 'Jeunes / étudiants', 'Parents / familles', 'Professionnels', 'Entreprises', 'Institutions', 'Communauté locale', 'Autre'], 'full' => true],
                        ['name' => 'zone', 'label' => 'Zone de diffusion', 'type' => 'text', 'placeholder' => 'Quartier, ville, région, pays...'],
                        ['name' => 'langues', 'label' => 'Langue(s) de l’affiche', 'type' => 'text', 'placeholder' => 'Français, anglais...'],
                    ],
                ],
                [
                    'title' => '4. Contenu à intégrer sur l’affiche',
                    'fields' => [
                        ['name' => 'titre', 'label' => 'Titre principal exact', 'type' => 'text', 'required' => true, 'full' => true],
                        ['name' => 'accroche', 'label' => 'Sous-titre / accroche', 'type' => 'text', 'full' => true],
                        ['name' => 'details', 'label' => 'Texte complémentaire et détails à afficher', 'type' => 'textarea', 'full' => true],
                        ['name' => 'date_heure', 'label' => 'Date(s) et horaires, si applicable', 'type' => 'text'],
                        ['name' => 'lieu', 'label' => 'Lieu / adresse, si applicable', 'type' => 'text'],
                        ['name' => 'prix', 'label' => 'Prix / tarif / réduction', 'type' => 'text'],
                        ['name' => 'coordonnees', 'label' => 'Coordonnées à afficher', 'type' => 'text'],
                        ['name' => 'cta', 'label' => 'Appel à l’action (CTA)', 'type' => 'text', 'full' => true, 'placeholder' => 'Réservez maintenant, Contactez-nous, Inscrivez-vous...'],
                        ['name' => 'mentions', 'label' => 'Mentions obligatoires, conditions ou avertissements', 'type' => 'textarea', 'full' => true],
                        ['name' => 'liens_qr', 'label' => 'Hashtags, QR code ou lien à intégrer', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '5. Identité visuelle et éléments fournis',
                    'fields' => [
                        ['name' => 'logo', 'label' => 'Disposez-vous d’un logo ?', 'type' => 'select', 'options' => ['Oui, fichier disponible', 'Oui, à récupérer sur le web', 'Non', 'Logo à créer séparément']],
                        ['name' => 'charte', 'label' => 'Une charte graphique existe-t-elle ?', 'type' => 'select', 'options' => ['Oui, à respecter', 'Non', 'Partiellement', 'À définir']],
                        ['name' => 'couleurs', 'label' => 'Couleurs à respecter', 'type' => 'text'],
                        ['name' => 'polices', 'label' => 'Polices ou style typographique imposés', 'type' => 'text'],
                        ['name' => 'fournis', 'label' => 'Éléments que le client fournira', 'type' => 'checkboxes', 'options' => ['Logo', 'Photos produits', 'Photos équipe / personnes', 'Textes', 'Illustrations', 'QR code', 'Aucun'], 'full' => true],
                        ['name' => 'fichiers', 'label' => 'Liens de téléchargement ou précisions sur les fichiers', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '6. Direction artistique et style souhaité',
                    'fields' => [
                        ['name' => 'styles', 'label' => 'Style d’affiche préféré', 'type' => 'checkboxes', 'options' => ['Moderne', 'Minimaliste', 'Élégant / premium', 'Audacieux / impactant', 'Institutionnel', 'Festif / dynamique', 'Artistique / illustré', 'Naturel', 'Traditionnel', 'À proposer par EUNITAL'], 'full' => true],
                        ['name' => 'ambiance', 'label' => 'Ambiance ou émotion à transmettre', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Confiance, urgence, joie, prestige, proximité, innovation...'],
                        ['name' => 'preferences_visuelles', 'label' => 'Couleurs ou visuels à privilégier', 'type' => 'textarea', 'full' => true],
                        ['name' => 'a_eviter', 'label' => 'Éléments, styles ou couleurs à éviter', 'type' => 'textarea', 'full' => true],
                        ['name' => 'references', 'label' => 'Liens vers des affiches de référence', 'type' => 'textarea', 'full' => true],
                        ['name' => 'references_commentaires', 'label' => 'Qu’appréciez-vous dans ces références ?', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '7. Format, dimensions et supports',
                    'fields' => [
                        ['name' => 'orientation', 'label' => 'Orientation souhaitée', 'type' => 'select', 'options' => ['Portrait', 'Paysage', 'Carré', 'À adapter selon les supports']],
                        ['name' => 'dimensions', 'label' => 'Dimensions / format', 'type' => 'text', 'placeholder' => 'A4, A3, A2, 50 × 70 cm...'],
                        ['name' => 'diffusion', 'label' => 'Supports et canaux de diffusion', 'type' => 'checkboxes', 'options' => ['Impression papier', 'Réseaux sociaux', 'Site web', 'Email / newsletter', 'Affichage extérieur', 'Écran / télévision', 'WhatsApp', 'Autre'], 'full' => true],
                        ['name' => 'quantite', 'label' => 'Quantité d’impression envisagée', 'type' => 'text', 'placeholder' => 'Nombre d’exemplaires'],
                        ['name' => 'impression', 'label' => 'Impression à prévoir par EUNITAL ?', 'type' => 'select', 'options' => ['Oui', 'Non, design uniquement', 'À discuter']],
                        ['name' => 'declinaisons', 'label' => 'Autres déclinaisons nécessaires', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Story, post carré, bannière, version A3, version bilingue...'],
                    ],
                ],
                [
                    'title' => '8. Planning et diffusion',
                    'fields' => [
                        ['name' => 'date_presentation', 'label' => 'Date de première présentation souhaitée', 'type' => 'date'],
                        ['name' => 'date_livraison', 'label' => 'Date de livraison finale', 'type' => 'date', 'required' => true],
                        ['name' => 'date_campagne', 'label' => 'Date de début de campagne', 'type' => 'date'],
                        ['name' => 'duree', 'label' => 'Durée de la campagne', 'type' => 'text', 'placeholder' => 'Ex. : 2 semaines'],
                        ['name' => 'echeance', 'label' => 'Échéance impérative ou événement associé', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '9. Budget et modalités',
                    'fields' => [
                        ['name' => 'budget', 'label' => 'Budget estimatif', 'type' => 'text', 'placeholder' => 'Montant et devise'],
                        ['name' => 'propositions', 'label' => 'Nombre de propositions souhaitées', 'type' => 'select', 'options' => ['À recommander par EUNITAL', '1 proposition', '2 propositions', '3 propositions']],
                        ['name' => 'retouches', 'label' => 'Nombre de séries de modifications attendues', 'type' => 'select', 'options' => ['À définir dans le devis', '1 série', '2 séries', '3 séries']],
                        ['name' => 'origine', 'label' => 'Comment avez-vous connu EUNITAL ?', 'type' => 'text'],
                        ['name' => 'complementaires', 'label' => 'Prestations complémentaires souhaitées', 'type' => 'checkboxes', 'options' => ['Impression', 'Photographie', 'Rédaction publicitaire', 'Déclinaisons réseaux sociaux', 'Flyers', 'Bâches / banderoles'], 'full' => true],
                    ],
                ],
                [
                    'title' => '10. Validation et remarques finales',
                    'fields' => [
                        ['name' => 'decideur', 'label' => 'Nom du décideur / validateur', 'type' => 'text'],
                        ['name' => 'fonction_validateur', 'label' => 'Fonction du validateur', 'type' => 'text'],
                        ['name' => 'contraintes', 'label' => 'Contraintes légales, techniques ou commerciales à respecter', 'type' => 'textarea', 'full' => true],
                        ['name' => 'remarques', 'label' => 'Informations complémentaires / demandes particulières', 'type' => 'textarea', 'full' => true],
                    ],
                ],
            ],
        ],
        'graphisme_conception_de_flyers' => [
            'title' => 'Questionnaire — Brief de conception de flyer',
            'description' => 'Brief complet pour définir le contenu, le format, la création et la diffusion de votre flyer.',
            'sections' => [
                [
                    'title' => '1. Informations sur le client',
                    'fields' => [
                        ['name' => 'organisation', 'label' => 'Nom de l’entreprise / organisation', 'type' => 'text', 'required' => true],
                        ['name' => 'responsable', 'label' => 'Nom du responsable', 'type' => 'text', 'required' => true],
                        ['name' => 'fonction', 'label' => 'Fonction', 'type' => 'text'],
                        ['name' => 'secteur', 'label' => 'Secteur d’activité', 'type' => 'text'],
                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                        ['name' => 'telephone', 'label' => 'Téléphone / WhatsApp', 'type' => 'tel', 'required' => true],
                        ['name' => 'localisation', 'label' => 'Ville et pays', 'type' => 'text'],
                        ['name' => 'presence_web', 'label' => 'Site web / réseaux sociaux', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => '2. Présentation du projet',
                    'fields' => [
                        ['name' => 'nom_projet', 'label' => 'Nom ou titre du projet', 'type' => 'text', 'required' => true],
                        ['name' => 'type_flyer', 'label' => 'Type de flyer', 'type' => 'select', 'required' => true, 'options' => ['Promotion commerciale', 'Présentation d’un produit ou service', 'Événement / manifestation', 'Offre spéciale / soldes', 'Menu / catalogue de produits', 'Information / sensibilisation', 'Institutionnel', 'Autre']],
                        ['name' => 'description', 'label' => 'Décrivez le produit, service, événement ou sujet à présenter', 'type' => 'textarea', 'required' => true, 'full' => true],
                        ['name' => 'objectif', 'label' => 'Objectif principal du flyer', 'type' => 'select', 'required' => true, 'options' => ['Attirer de nouveaux clients', 'Générer des ventes / commandes', 'Présenter une offre', 'Inviter à un événement', 'Informer ou sensibiliser', 'Faire connaître l’organisation', 'Autre']],
                        ['name' => 'action', 'label' => 'Action attendue du lecteur', 'type' => 'text', 'placeholder' => 'Acheter, appeler, s’inscrire, visiter...'],
                        ['name' => 'offre', 'label' => 'Offre, avantage ou argument principal', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '3. Public cible et message',
                    'fields' => [
                        ['name' => 'public', 'label' => 'À qui s’adresse le flyer ?', 'type' => 'textarea', 'required' => true, 'full' => true, 'placeholder' => 'Âge, profil, profession, centres d’intérêt, localisation...'],
                        ['name' => 'cibles', 'label' => 'Public prioritaire', 'type' => 'checkboxes', 'options' => ['Grand public', 'Jeunes / étudiants', 'Parents / familles', 'Professionnels', 'Entreprises', 'Institutions', 'Communauté locale', 'Autre'], 'full' => true],
                        ['name' => 'message', 'label' => 'Message essentiel à retenir', 'type' => 'textarea', 'required' => true, 'full' => true],
                        ['name' => 'ton', 'label' => 'Ton de communication souhaité', 'type' => 'select', 'options' => ['Commercial et persuasif', 'Informatif', 'Institutionnel', 'Chaleureux et accessible', 'Urgent / promotionnel', 'Inspirant', 'À proposer par EUNITAL']],
                        ['name' => 'langues', 'label' => 'Langue(s) du flyer', 'type' => 'text', 'placeholder' => 'Français, anglais...'],
                        ['name' => 'zone', 'label' => 'Zone de distribution', 'type' => 'text', 'placeholder' => 'Quartier, ville, région, pays...'],
                    ],
                ],
                [
                    'title' => '4. Contenu à intégrer',
                    'fields' => [
                        ['name' => 'titre', 'label' => 'Titre principal exact', 'type' => 'text', 'required' => true, 'full' => true],
                        ['name' => 'accroche', 'label' => 'Sous-titre / accroche', 'type' => 'text', 'full' => true],
                        ['name' => 'textes', 'label' => 'Textes définitifs ou informations à présenter', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Description, programme, liste de produits/services, conditions...'],
                        ['name' => 'date_heure', 'label' => 'Date(s) et horaires, si applicable', 'type' => 'text'],
                        ['name' => 'lieu', 'label' => 'Lieu / adresse, si applicable', 'type' => 'text'],
                        ['name' => 'prix', 'label' => 'Prix / tarif / réduction', 'type' => 'text'],
                        ['name' => 'coordonnees', 'label' => 'Coordonnées à afficher', 'type' => 'text'],
                        ['name' => 'cta', 'label' => 'Appel à l’action', 'type' => 'text', 'full' => true, 'placeholder' => 'Commandez maintenant, Contactez-nous, Inscrivez-vous...'],
                        ['name' => 'mentions', 'label' => 'Mentions légales, conditions ou avertissements', 'type' => 'textarea', 'full' => true],
                        ['name' => 'liens_qr', 'label' => 'Lien, QR code ou réseaux sociaux à intégrer', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '5. Identité visuelle et fichiers fournis',
                    'fields' => [
                        ['name' => 'logo', 'label' => 'Disposez-vous d’un logo ?', 'type' => 'select', 'options' => ['Oui, fichier disponible', 'Oui, à récupérer sur le web', 'Non', 'Logo à créer séparément']],
                        ['name' => 'charte', 'label' => 'Disposez-vous d’une charte graphique ?', 'type' => 'select', 'options' => ['Oui, à respecter', 'Non', 'Partiellement', 'À définir']],
                        ['name' => 'couleurs', 'label' => 'Couleurs à respecter', 'type' => 'text'],
                        ['name' => 'polices', 'label' => 'Polices ou style typographique imposés', 'type' => 'text'],
                        ['name' => 'fournis', 'label' => 'Éléments que vous allez fournir', 'type' => 'checkboxes', 'options' => ['Logo', 'Photos produits', 'Photos équipe / personnes', 'Textes', 'Illustrations', 'QR code', 'Aucun'], 'full' => true],
                        ['name' => 'fichiers', 'label' => 'Liens de téléchargement ou précisions sur les fichiers', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '6. Direction artistique',
                    'fields' => [
                        ['name' => 'styles', 'label' => 'Style graphique souhaité', 'type' => 'checkboxes', 'options' => ['Moderne', 'Minimaliste', 'Élégant / premium', 'Audacieux / impactant', 'Institutionnel', 'Festif / dynamique', 'Artistique / illustré', 'Naturel', 'Traditionnel', 'À proposer par EUNITAL'], 'full' => true],
                        ['name' => 'ambiance', 'label' => 'Ambiance ou émotion à transmettre', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Confiance, urgence, joie, prestige, proximité, innovation...'],
                        ['name' => 'preferences_visuelles', 'label' => 'Couleurs ou visuels à privilégier', 'type' => 'textarea', 'full' => true],
                        ['name' => 'a_eviter', 'label' => 'Éléments, styles ou couleurs à éviter', 'type' => 'textarea', 'full' => true],
                        ['name' => 'references', 'label' => 'Liens vers des flyers de référence', 'type' => 'textarea', 'full' => true],
                        ['name' => 'references_commentaires', 'label' => 'Ce que vous appréciez dans ces références', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '7. Format, impression et diffusion',
                    'fields' => [
                        ['name' => 'format', 'label' => 'Format souhaité', 'type' => 'select', 'options' => ['A6', 'A5', 'A4', 'DL / tiers de A4', 'Carré', 'Format personnalisé', 'À recommander par EUNITAL']],
                        ['name' => 'orientation', 'label' => 'Orientation', 'type' => 'select', 'options' => ['Portrait', 'Paysage', 'Carré', 'À adapter au format']],
                        ['name' => 'faces', 'label' => 'Nombre de faces', 'type' => 'select', 'options' => ['Recto uniquement', 'Recto-verso', 'À recommander par EUNITAL']],
                        ['name' => 'papier', 'label' => 'Type ou grammage de papier souhaité', 'type' => 'text', 'placeholder' => 'Ex. : couché brillant 135 g/m²'],
                        ['name' => 'diffusion', 'label' => 'Supports et canaux de diffusion', 'type' => 'checkboxes', 'options' => ['Distribution en main propre', 'Dépôt en magasin / établissement', 'Réseaux sociaux', 'WhatsApp', 'Email / newsletter', 'Site web', 'Autre'], 'full' => true],
                        ['name' => 'quantite', 'label' => 'Quantité d’impression envisagée', 'type' => 'text', 'placeholder' => 'Nombre d’exemplaires'],
                        ['name' => 'impression', 'label' => 'Impression à prévoir par EUNITAL ?', 'type' => 'select', 'options' => ['Oui', 'Non, design uniquement', 'À discuter']],
                        ['name' => 'declinaisons', 'label' => 'Déclinaisons numériques ou autres formats nécessaires', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '8. Planning',
                    'fields' => [
                        ['name' => 'date_presentation', 'label' => 'Date de première présentation souhaitée', 'type' => 'date'],
                        ['name' => 'date_livraison', 'label' => 'Date de livraison finale', 'type' => 'date', 'required' => true],
                        ['name' => 'date_distribution', 'label' => 'Date prévue de distribution / diffusion', 'type' => 'date'],
                        ['name' => 'echeance', 'label' => 'Échéance impérative ou événement associé', 'type' => 'textarea', 'full' => true],
                    ],
                ],
                [
                    'title' => '9. Budget et modalités',
                    'fields' => [
                        ['name' => 'budget', 'label' => 'Budget estimatif', 'type' => 'text', 'placeholder' => 'Montant et devise'],
                        ['name' => 'propositions', 'label' => 'Nombre de propositions souhaitées', 'type' => 'select', 'options' => ['À recommander par EUNITAL', '1 proposition', '2 propositions', '3 propositions']],
                        ['name' => 'retouches', 'label' => 'Nombre de séries de modifications attendues', 'type' => 'select', 'options' => ['À définir dans le devis', '1 série', '2 séries', '3 séries']],
                        ['name' => 'origine', 'label' => 'Comment avez-vous connu EUNITAL ?', 'type' => 'text'],
                        ['name' => 'complementaires', 'label' => 'Prestations complémentaires souhaitées', 'type' => 'checkboxes', 'options' => ['Impression', 'Photographie', 'Rédaction publicitaire', 'Déclinaisons réseaux sociaux', 'Affiche', 'Brochure'], 'full' => true],
                    ],
                ],
                [
                    'title' => '10. Validation et remarques finales',
                    'fields' => [
                        ['name' => 'decideur', 'label' => 'Nom du décideur / validateur', 'type' => 'text'],
                        ['name' => 'fonction_validateur', 'label' => 'Fonction du validateur', 'type' => 'text'],
                        ['name' => 'contraintes', 'label' => 'Contraintes légales, techniques ou commerciales à respecter', 'type' => 'textarea', 'full' => true],
                        ['name' => 'remarques', 'label' => 'Informations complémentaires / demandes particulières', 'type' => 'textarea', 'full' => true],
                    ],
                ],
            ],
        ],
    ],
];

$forms['graphisme'] += $additionalGraphicForms;
$forms['secretariat'] = $additionalSecretariatForms;
$forms['studio'] = [];
foreach ($studioBriefs as $serviceKey => $brief) {
    $forms['studio'][$serviceKey] = $buildStudioBrief($brief);
}
$forms['developpement'] = [];
foreach ($developmentBriefs as $serviceKey => $brief) {
    if ($serviceKey === 'developpement_assistance_technique_et_support_informatique') {
        continue;
    }
    $forms['developpement'][$serviceKey] = $buildDevelopmentBrief($brief);
}
$forms['developpement']['developpement_creation_de_solutions_saas'] = [
    'title' => 'Formulaire de prospection — Création de solutions SaaS',
    'description' => 'Définissons ensemble votre plateforme, ses utilisateurs, ses fonctionnalités et son modèle de service.',
    'sections' => [
        [
            'title' => '01. Informations sur le porteur du projet',
            'fields' => [
                ['name' => 'client', 'label' => 'Nom et prénom / Raison sociale', 'type' => 'text', 'required' => true],
                ['name' => 'contact', 'label' => 'Nom du responsable du projet', 'type' => 'text'],
                ['name' => 'email', 'label' => 'Adresse e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'phone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true],
                ['name' => 'location', 'label' => 'Ville / Pays', 'type' => 'text'],
                ['name' => 'website', 'label' => 'Site web existant', 'type' => 'url', 'placeholder' => 'https://'],
                ['name' => 'structure', 'label' => 'Type de structure', 'type' => 'radio', 'options' => ['Startup', 'PME', 'Grande entreprise', 'Association', 'Institution', 'Projet individuel', 'Autre'], 'full' => true],
            ],
        ],
        [
            'title' => '02. Présentation et vision du SaaS',
            'fields' => [
                ['name' => 'product_name', 'label' => 'Nom provisoire de la solution', 'type' => 'text'],
                ['name' => 'sector', 'label' => 'Secteur d’activité ciblé', 'type' => 'text', 'required' => true, 'placeholder' => 'Éducation, santé, commerce, RH…'],
                ['name' => 'idea', 'label' => 'Décrivez votre idée de solution SaaS', 'type' => 'textarea', 'required' => true, 'full' => true, 'placeholder' => 'Quel service en ligne souhaitez-vous proposer ? À quel problème répond-il ?'],
                ['name' => 'problem', 'label' => 'Quel problème votre solution doit-elle résoudre ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'value', 'label' => 'Quelle est votre proposition de valeur / votre avantage distinctif ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'stage', 'label' => 'La solution est-elle…', 'type' => 'select', 'options' => ['Une nouvelle idée', 'En phase d’étude', 'En cours de conception', 'Déjà développée — refonte souhaitée', 'Déjà lancée — évolution souhaitée']],
                ['name' => 'competitors', 'label' => 'Existe-t-il des solutions concurrentes ou similaires ?', 'type' => 'text'],
            ],
        ],
        [
            'title' => '03. Utilisateurs et marché cible',
            'fields' => [
                ['name' => 'audience', 'label' => 'À qui s’adresse principalement la plateforme ?', 'type' => 'checkboxes', 'options' => ['Particuliers', 'Entreprises', 'Administrations / institutions', 'Associations', 'Établissements éducatifs', 'Autre'], 'full' => true],
                ['name' => 'users_start', 'label' => 'Nombre estimé d’utilisateurs au lancement', 'type' => 'number', 'min' => 0],
                ['name' => 'users_future', 'label' => 'Nombre estimé à 2–3 ans', 'type' => 'number', 'min' => 0],
                ['name' => 'market', 'label' => 'Zone géographique visée', 'type' => 'text', 'placeholder' => 'Locale, nationale, internationale…'],
                ['name' => 'languages', 'label' => 'Langues nécessaires', 'type' => 'text', 'placeholder' => 'Français, anglais…'],
                ['name' => 'personas', 'label' => 'Décrivez les profils d’utilisateurs et leurs besoins', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '04. Fonctionnalités attendues',
            'fields' => [
                ['name' => 'features', 'label' => 'Quelles fonctionnalités souhaitez-vous intégrer ?', 'type' => 'checkboxes', 'options' => ['Création de compte / connexion', 'Profils et rôles utilisateurs', 'Gestion de contenu', 'Gestion de clients / CRM', 'Gestion de projets / tâches', 'Facturation / devis', 'Paiement en ligne', 'Abonnements', 'Réservation / agenda', 'Messagerie / notifications', 'Recherche / filtres', 'Tableaux de bord / statistiques', 'Import / export de données', 'Documents / fichiers', 'API / intégrations externes', 'Intelligence artificielle', 'Application mobile associée', 'Autre'], 'full' => true],
                ['name' => 'mvp', 'label' => 'Décrivez les fonctionnalités indispensables au lancement (MVP)', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'later_features', 'label' => 'Fonctionnalités souhaitées dans une version ultérieure', 'type' => 'textarea', 'full' => true],
                ['name' => 'multitenant', 'label' => 'Faut-il prévoir plusieurs espaces ou organisations séparés (multi-tenant) ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À étudier']],
                ['name' => 'collaboration', 'label' => 'Les utilisateurs doivent-ils collaborer entre eux ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Selon les rôles']],
            ],
        ],
        [
            'title' => '05. Comptes, rôles et administration',
            'fields' => [
                ['name' => 'roles', 'label' => 'Quels profils / rôles doivent exister ?', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Ex. : super-administrateur, administrateur client, gestionnaire, utilisateur…'],
                ['name' => 'registration', 'label' => 'Inscription des utilisateurs', 'type' => 'select', 'options' => ['Libre et publique', 'Sur invitation', 'Après validation administrative', 'Création manuelle par l’administrateur', 'À définir']],
                ['name' => 'auth', 'label' => 'Authentification souhaitée', 'type' => 'select', 'options' => ['E-mail et mot de passe', 'Connexion Google / Apple / Microsoft', 'Authentification à deux facteurs', 'SSO entreprise', 'À conseiller']],
                ['name' => 'admin', 'label' => 'Quelles opérations l’administrateur doit-il pouvoir effectuer ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '06. Modèle économique et abonnements',
            'fields' => [
                ['name' => 'business_model', 'label' => 'Modèle de commercialisation envisagé', 'type' => 'select', 'options' => ['Gratuit', 'Freemium', 'Abonnement mensuel', 'Abonnement annuel', 'Paiement à l’usage', 'Licence / forfait', 'Commission sur transactions', 'Plusieurs formules', 'À définir']],
                ['name' => 'plans', 'label' => 'Nombre de formules d’abonnement envisagées', 'type' => 'number', 'min' => 0],
                ['name' => 'pricing', 'label' => 'Décrivez les offres, tarifs ou limites par formule', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Ex. : offre gratuite, professionnelle, entreprise…'],
                ['name' => 'trial', 'label' => 'Périodes d’essai gratuites nécessaires ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À définir']],
                ['name' => 'payment_methods', 'label' => 'Paiements à accepter', 'type' => 'text', 'placeholder' => 'Carte bancaire, virement, mobile money…'],
                ['name' => 'billing', 'label' => 'Gestion des factures, renouvellements, impayés ou résiliations', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '07. Design, expérience utilisateur et contenus',
            'fields' => [
                ['name' => 'identity', 'label' => 'Avez-vous déjà une identité visuelle ?', 'type' => 'select', 'options' => ['Oui, complète', 'Partiellement', 'Non, à créer']],
                ['name' => 'mockups', 'label' => 'Disposez-vous de maquettes ou wireframes ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'En cours']],
                ['name' => 'design', 'label' => 'Style graphique et expérience souhaités', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Sobre, moderne, professionnel, intuitif, accessible…'],
                ['name' => 'references', 'label' => 'Sites ou applications de référence (liens et éléments appréciés)', 'type' => 'textarea', 'full' => true],
                ['name' => 'content_owner', 'label' => 'Qui fournira les textes, images, icônes et autres contenus ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '08. Données, intégrations et contraintes techniques',
            'fields' => [
                ['name' => 'data', 'label' => 'Disposez-vous déjà de données à importer ?', 'type' => 'radio', 'options' => ['Oui', 'Non', 'À confirmer'], 'full' => true],
                ['name' => 'integrations', 'label' => 'Quelles intégrations externes sont nécessaires ?', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Comptabilité, CRM, paiement, e-mail, SMS, calendrier, outils métier…'],
                ['name' => 'tech', 'label' => 'Technologies imposées ou préférées', 'type' => 'text'],
                ['name' => 'hosting', 'label' => 'Hébergement / cloud préféré', 'type' => 'text', 'placeholder' => 'Cloud, serveur privé, fournisseur souhaité…'],
                ['name' => 'compatibility', 'label' => 'Compatibilité navigateurs / appareils', 'type' => 'text', 'placeholder' => 'Ordinateur, tablette, mobile…'],
                ['name' => 'availability', 'label' => 'Disponibilité attendue', 'type' => 'select', 'options' => ['Standard', 'Haute disponibilité', 'À définir selon le besoin']],
                ['name' => 'compliance', 'label' => 'Contraintes réglementaires, localisation des données ou exigences sectorielles', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '09. Sécurité, confidentialité et conformité',
            'fields' => [
                ['name' => 'security', 'label' => 'Exigences de sécurité connues', 'type' => 'checkboxes', 'options' => ['Chiffrement des données', 'Sauvegardes automatiques', 'Journalisation des actions', 'Gestion fine des permissions', 'Authentification multifacteur', 'Audit de sécurité', 'Conformité RGPD', 'Autre'], 'full' => true],
                ['name' => 'sensitive', 'label' => 'La solution traitera-t-elle des données personnelles ou sensibles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À déterminer']],
                ['name' => 'retention', 'label' => 'Des règles de conservation / suppression des données existent-elles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À définir']],
                ['name' => 'security_details', 'label' => 'Précisez les obligations, risques ou exigences spécifiques', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '10. Lancement, maintenance et accompagnement',
            'fields' => [
                ['name' => 'launch_date', 'label' => 'Date cible de mise en ligne', 'type' => 'date'],
                ['name' => 'beta', 'label' => 'Souhaitez-vous un lancement progressif (bêta) ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À conseiller']],
                ['name' => 'support', 'label' => 'Accompagnement souhaité', 'type' => 'checkboxes', 'options' => ['Formation administrateurs', 'Formation utilisateurs', 'Documentation', 'Maintenance corrective', 'Maintenance évolutive', 'Supervision', 'Support utilisateurs', 'Hébergement géré'], 'full' => true],
                ['name' => 'aftercare', 'label' => 'Quel niveau de support après lancement attendez-vous ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '11. Budget, planning et priorités',
            'fields' => [
                ['name' => 'budget', 'label' => 'Budget estimatif pour la conception et le développement', 'type' => 'select', 'options' => ['Moins de 2 000 €', '2 000 à 5 000 €', '5 000 à 10 000 €', '10 000 à 25 000 €', 'Plus de 25 000 €', 'À définir avec EUNITAL']],
                ['name' => 'monthly_budget', 'label' => 'Budget mensuel envisagé pour l’exploitation', 'type' => 'select', 'options' => ['Moins de 50 €', '50 à 150 €', '150 à 500 €', 'Plus de 500 €', 'À estimer']],
                ['name' => 'timeline', 'label' => 'Échéance souhaitée pour une première version', 'type' => 'select', 'options' => ['Moins d’un mois', '1 à 3 mois', '3 à 6 mois', 'Plus de 6 mois', 'Flexible']],
                ['name' => 'priority', 'label' => 'Votre priorité principale', 'type' => 'select', 'options' => ['Rapidité de lancement', 'Maîtrise du budget', 'Richesse fonctionnelle', 'Qualité de l’expérience utilisateur', 'Sécurité et fiabilité', 'Évolutivité et scalabilité']],
                ['name' => 'milestones', 'label' => 'Jalons, contraintes de calendrier ou éléments déjà planifiés', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '12. Validation et informations complémentaires',
            'fields' => [
                ['name' => 'source', 'label' => 'Comment avez-vous connu EUNITAL ?', 'type' => 'text', 'full' => true],
                ['name' => 'comments', 'label' => 'Autres informations, attentes ou questions', 'type' => 'textarea', 'full' => true],
                ['name' => 'documents', 'label' => 'Documents que vous pourrez transmettre ultérieurement', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Cahier des charges, schémas, maquettes, fichiers de données d’exemple anonymisés…'],
                ['name' => 'consent', 'label' => 'Je confirme l’exactitude des informations fournies et j’accepte d’être recontacté(e) au sujet de ce projet.', 'type' => 'checkboxes', 'required' => true, 'options' => ['yes'], 'full' => true],
                ['name' => 'security_reminder', 'label' => 'Protection des données', 'type' => 'textarea', 'default' => 'N’inscrivez aucun mot de passe, secret d’API ou donnée confidentielle dans ce formulaire.', 'full' => true],
            ],
        ],
    ],
];
$forms['developpement']['developpement_integration_de_solutions_dintelligence_artificielle'] = [
    'title' => 'Formulaire de prospection — Intégration de solutions d’intelligence artificielle',
    'description' => 'Identifions les usages pertinents de l’IA dans votre activité et les conditions de leur intégration à vos outils et processus.',
    'sections' => [
        [
            'title' => '01. Informations sur le client',
            'fields' => [
                ['name' => 'client', 'label' => 'Nom et prénom / Raison sociale', 'type' => 'text', 'required' => true],
                ['name' => 'contact', 'label' => 'Nom du responsable du projet', 'type' => 'text'],
                ['name' => 'email', 'label' => 'Adresse e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'phone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true],
                ['name' => 'location', 'label' => 'Ville / Pays', 'type' => 'text'],
                ['name' => 'website', 'label' => 'Site web', 'type' => 'url', 'placeholder' => 'https://'],
                ['name' => 'structure', 'label' => 'Type de structure', 'type' => 'radio', 'options' => ['Particulier', 'Startup', 'PME', 'Grande entreprise', 'Association', 'Institution', 'Autre'], 'full' => true],
                ['name' => 'sector', 'label' => 'Secteur d’activité', 'type' => 'text', 'required' => true],
                ['name' => 'staff', 'label' => 'Effectif approximatif', 'type' => 'select', 'options' => ['1 personne', '2 à 10', '11 à 50', '51 à 250', 'Plus de 250']],
            ],
        ],
        [
            'title' => '02. Objectifs et contexte du projet IA',
            'fields' => [
                ['name' => 'goal', 'label' => 'Quel est votre objectif principal avec l’intelligence artificielle ?', 'type' => 'textarea', 'required' => true, 'full' => true, 'placeholder' => 'Ex. : gagner du temps, améliorer le service client, analyser des données, automatiser des tâches…'],
                ['name' => 'pain_points', 'label' => 'Décrivez les difficultés ou besoins métiers à résoudre', 'type' => 'textarea', 'full' => true],
                ['name' => 'outcomes', 'label' => 'Quels résultats concrets souhaitez-vous mesurer ?', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Réduction des délais, baisse des erreurs, qualité des réponses, productivité…'],
                ['name' => 'maturity', 'label' => 'Votre niveau actuel de maturité IA', 'type' => 'select', 'options' => ['Aucune utilisation à ce jour', 'Tests individuels / outils grand public', 'Quelques usages au sein des équipes', 'Solutions IA déjà intégrées', 'Équipe ou stratégie IA structurée']],
                ['name' => 'trigger', 'label' => 'Échéance ou événement déclencheur', 'type' => 'text', 'placeholder' => 'Projet, croissance, transformation…'],
            ],
        ],
        [
            'title' => '03. Cas d’usage et domaines à améliorer',
            'fields' => [
                ['name' => 'use_cases', 'label' => 'Dans quels domaines souhaitez-vous intégrer l’IA ?', 'type' => 'checkboxes', 'options' => ['Service client / chatbot', 'Assistant interne / FAQ', 'Traitement de documents', 'Recherche dans les connaissances', 'Rédaction / génération de contenu', 'Analyse de données / prévisions', 'Reconnaissance d’images / audio', 'Automatisation de tâches', 'Aide à la décision', 'Personnalisation / recommandations', 'Détection d’anomalies / fraude', 'Traduction / transcription', 'Développement logiciel assisté', 'Autre'], 'full' => true],
                ['name' => 'scenarios', 'label' => 'Décrivez les scénarios d’utilisation prioritaires', 'type' => 'textarea', 'full' => true],
                ['name' => 'users', 'label' => 'Qui utilisera principalement la solution ?', 'type' => 'text'],
                ['name' => 'volume', 'label' => 'Volume approximatif de demandes / opérations', 'type' => 'text', 'placeholder' => 'Par jour, semaine ou mois'],
            ],
        ],
        [
            'title' => '04. Processus actuels et niveau d’automatisation',
            'fields' => [
                ['name' => 'process', 'label' => 'Décrivez le processus actuel que vous souhaitez améliorer', 'type' => 'textarea', 'full' => true],
                ['name' => 'current_method', 'label' => 'Comment ce travail est-il effectué aujourd’hui ?', 'type' => 'select', 'options' => ['Manuellement', 'Avec des logiciels classiques', 'Avec des automatisations simples', 'Avec une solution IA existante', 'Combinaison de plusieurs méthodes']],
                ['name' => 'frequency', 'label' => 'Fréquence de la tâche concernée', 'type' => 'select', 'options' => ['En continu', 'Plusieurs fois par jour', 'Chaque jour', 'Chaque semaine', 'Chaque mois', 'Occasionnelle']],
                ['name' => 'human_control', 'label' => 'Quelles étapes doivent être automatisées et lesquelles doivent rester sous contrôle humain ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'current_issues', 'label' => 'Quelles erreurs, limites ou pertes de temps rencontrez-vous actuellement ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '05. Données et connaissances disponibles',
            'fields' => [
                ['name' => 'data_sources', 'label' => 'Quelles données ou ressources pourraient être utilisées ?', 'type' => 'checkboxes', 'options' => ['Documents PDF / Word', 'Tableurs / CSV', 'Base de données', 'Site web / catalogue', 'E-mails / conversations', 'Images / audio / vidéo', 'CRM / ERP', 'Données métier spécifiques', 'Aucune donnée disponible', 'Autre'], 'full' => true],
                ['name' => 'data_volume', 'label' => 'Volume approximatif des données', 'type' => 'text', 'placeholder' => 'Nombre de fichiers, Go, lignes…'],
                ['name' => 'data_quality', 'label' => 'Qualité et actualisation des données', 'type' => 'select', 'options' => ['Structurées et régulièrement mises à jour', 'Partiellement organisées', 'Dispersées / non structurées', 'À évaluer']],
                ['name' => 'digital_data', 'label' => 'Les données sont-elles accessibles numériquement ?', 'type' => 'select', 'options' => ['Oui', 'Partiellement', 'Non', 'À vérifier']],
                ['name' => 'data_permission', 'label' => 'Les données peuvent-elles être utilisées pour un traitement IA ?', 'type' => 'select', 'options' => ['Oui, sous réserve des règles applicables', 'Non', 'À vérifier avec notre organisation']],
                ['name' => 'data_details', 'label' => 'Précisez les sources, formats, propriétaires et conditions d’accès', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '06. Outils existants et intégrations nécessaires',
            'fields' => [
                ['name' => 'systems', 'label' => 'Quels outils doivent communiquer avec la solution IA ?', 'type' => 'checkboxes', 'options' => ['Site web / application', 'CRM', 'ERP / logiciel de gestion', 'Messagerie e-mail', 'WhatsApp / réseaux sociaux', 'Suite bureautique', 'Base de données', 'Outils métiers', 'Aucun / nouveau système', 'Autre'], 'full' => true],
                ['name' => 'software_details', 'label' => 'Noms des logiciels, versions et fournisseurs concernés', 'type' => 'textarea', 'full' => true],
                ['name' => 'api', 'label' => 'Disposez-vous d’API ou de documentation technique ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À vérifier']],
                ['name' => 'environment', 'label' => 'Environnement technique', 'type' => 'text', 'placeholder' => 'Cloud, serveur interne, outils SaaS…'],
                ['name' => 'data_flows', 'label' => 'Décrivez les échanges de données attendus entre les outils', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '07. Type de solution IA envisagée',
            'fields' => [
                ['name' => 'ai_types', 'label' => 'Avez-vous déjà une préférence technologique ?', 'type' => 'checkboxes', 'options' => ['Assistant conversationnel / LLM', 'IA générative de texte', 'Analyse prédictive / machine learning', 'Vision par ordinateur', 'Reconnaissance vocale / audio', 'Recherche documentaire / RAG', 'Automatisation intelligente / agents', 'Modèle personnalisé', 'Je souhaite être conseillé'], 'full' => true],
                ['name' => 'provider', 'label' => 'Fournisseur ou modèle déjà envisagé', 'type' => 'text', 'placeholder' => 'Si connu'],
                ['name' => 'deployment', 'label' => 'Solution cloud ou hébergement privé ?', 'type' => 'select', 'options' => ['API / cloud externe', 'Cloud privé / dédié', 'Hébergement sur site', 'Hybride', 'À recommander']],
                ['name' => 'performance', 'label' => 'Contraintes de précision, de rapidité, de disponibilité ou de langues', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '08. Sécurité, confidentialité et conformité',
            'fields' => [
                ['name' => 'personal_data', 'label' => 'Les données contiennent-elles des informations personnelles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À vérifier']],
                ['name' => 'sensitive_data', 'label' => 'Des données sensibles ou confidentielles sont-elles concernées ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À vérifier']],
                ['name' => 'security', 'label' => 'Exigences ou mesures souhaitées', 'type' => 'checkboxes', 'options' => ['Conformité RGPD', 'Anonymisation / pseudonymisation', 'Chiffrement', 'Contrôle des accès', 'Journalisation / traçabilité', 'Hébergement dans une zone géographique définie', 'Pas de réutilisation des données pour entraîner des modèles', 'Validation humaine obligatoire', 'Audit de sécurité', 'Autre'], 'full' => true],
                ['name' => 'compliance', 'label' => 'Règles internes, obligations sectorielles ou contraintes juridiques à respecter', 'type' => 'textarea', 'full' => true],
                ['name' => 'security_reminder', 'label' => 'Protection des données', 'type' => 'textarea', 'default' => 'Ne joignez pas de données personnelles, dossiers clients, mots de passe, clés API ou informations confidentielles à ce formulaire. Les modalités de transmission sécurisée seront définies séparément.', 'full' => true],
            ],
        ],
        [
            'title' => '09. Contrôle humain, qualité et limites',
            'fields' => [
                ['name' => 'autonomy', 'label' => 'Quel degré d’autonomie souhaitez-vous ?', 'type' => 'select', 'options' => ['Assistance uniquement — décision humaine', 'Proposition IA avec validation humaine', 'Automatisation de tâches à faible risque', 'Automatisation avancée avec supervision', 'À définir selon les cas d’usage']],
                ['name' => 'human_validation', 'label' => 'Une validation humaine doit-elle être obligatoire ?', 'type' => 'select', 'options' => ['Toujours', 'Pour certaines opérations', 'Non, selon des règles définies', 'À étudier']],
                ['name' => 'failure_risks', 'label' => 'Quelles erreurs ou réponses incorrectes seraient inacceptables ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'quality', 'label' => 'Comment souhaitez-vous vérifier la qualité des résultats ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'prohibited_actions', 'label' => 'Quelles actions l’IA ne doit-elle jamais effectuer ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '10. Déploiement et accompagnement',
            'fields' => [
                ['name' => 'initial_users', 'label' => 'Nombre d’utilisateurs concernés au démarrage', 'type' => 'number', 'min' => 0],
                ['name' => 'launch_date', 'label' => 'Date cible de mise en service', 'type' => 'date'],
                ['name' => 'poc', 'label' => 'Souhaitez-vous un projet pilote / POC ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À conseiller']],
                ['name' => 'rollout', 'label' => 'Déploiement envisagé', 'type' => 'select', 'options' => ['Progressif par équipe', 'Sur toute l’organisation', 'Sur un seul processus au départ', 'À définir']],
                ['name' => 'support', 'label' => 'Accompagnement souhaité', 'type' => 'checkboxes', 'options' => ['Formation des équipes', 'Documentation utilisateur', 'Formation des administrateurs', 'Maintenance et mises à jour', 'Suivi des performances', 'Support technique', 'Optimisation continue'], 'full' => true],
                ['name' => 'owner', 'label' => 'Qui sera responsable du suivi de la solution côté client ?', 'type' => 'text', 'full' => true],
            ],
        ],
        [
            'title' => '11. Budget, planning et priorités',
            'fields' => [
                ['name' => 'budget', 'label' => 'Budget estimatif pour l’intégration', 'type' => 'select', 'options' => ['Moins de 1 000 €', '1 000 à 3 000 €', '3 000 à 7 000 €', '7 000 à 15 000 €', 'Plus de 15 000 €', 'À définir après étude']],
                ['name' => 'monthly_budget', 'label' => 'Budget mensuel estimé (API, hébergement, exploitation)', 'type' => 'select', 'options' => ['Moins de 50 €', '50 à 200 €', '200 à 500 €', '500 à 1 000 €', 'Plus de 1 000 €', 'À estimer']],
                ['name' => 'timeline', 'label' => 'Délai souhaité', 'type' => 'select', 'options' => ['Moins d’un mois', '1 à 2 mois', '2 à 4 mois', '4 à 6 mois', 'Flexible']],
                ['name' => 'priority', 'label' => 'Priorité principale', 'type' => 'select', 'options' => ['Réduire les coûts', 'Gagner du temps', 'Améliorer la qualité de service', 'Augmenter les ventes', 'Fiabiliser les opérations', 'Innover / créer un nouveau service', 'Autre']],
                ['name' => 'milestones', 'label' => 'Contraintes budgétaires, étapes ou échéances importantes', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '12. Validation et informations complémentaires',
            'fields' => [
                ['name' => 'source', 'label' => 'Comment avez-vous connu EUNITAL ?', 'type' => 'text', 'full' => true],
                ['name' => 'comments', 'label' => 'Autres informations, questions ou attentes', 'type' => 'textarea', 'full' => true],
                ['name' => 'documents', 'label' => 'Documents ou exemples que vous pourrez transmettre ultérieurement', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Cartographie de processus, documentation d’API, exemples de données anonymisées, procédures…'],
                ['name' => 'consent', 'label' => 'Je confirme l’exactitude des informations fournies et j’accepte d’être recontacté(e) au sujet de ce projet.', 'type' => 'checkboxes', 'required' => true, 'options' => ['yes'], 'full' => true],
            ],
        ],
    ],
];
$forms['developpement']['developpement_assistance_technique_et_support_informatique'] = [
    'title' => 'Formulaire de prospection — Assistance technique et support informatique',
    'description' => 'Décrivez votre environnement, vos difficultés et l’accompagnement souhaité. Ne transmettez aucun mot de passe ni code confidentiel.',
    'sections' => [
        [
            'title' => '01. Informations sur le client',
            'fields' => [
                ['name' => 'client', 'label' => 'Nom et prénom / Raison sociale', 'type' => 'text', 'required' => true],
                ['name' => 'contact', 'label' => 'Contact principal', 'type' => 'text'],
                ['name' => 'email', 'label' => 'Adresse e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'phone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true],
                ['name' => 'location', 'label' => 'Ville / Pays', 'type' => 'text'],
                ['name' => 'website', 'label' => 'Site web', 'type' => 'url', 'placeholder' => 'https://'],
                ['name' => 'client_type', 'label' => 'Type de client', 'type' => 'radio', 'options' => ['Particulier', 'Entreprise', 'Association', 'Institution', 'Autre'], 'full' => true],
            ],
        ],
        [
            'title' => '02. Présentation du besoin',
            'fields' => [
                ['name' => 'support_type', 'label' => 'Type d’assistance recherchée', 'type' => 'select', 'required' => true, 'options' => ['Diagnostic et dépannage', 'Assistance à distance', 'Intervention sur site', 'Installation et configuration', 'Maintenance préventive', 'Maintenance corrective', 'Formation / accompagnement', 'Contrat de support récurrent', 'Audit du parc informatique', 'Autre']],
                ['name' => 'sector', 'label' => 'Secteur d’activité', 'type' => 'text'],
                ['name' => 'need', 'label' => 'Décrivez votre problème ou votre demande', 'type' => 'textarea', 'required' => true, 'full' => true, 'placeholder' => 'Que se passe-t-il ? Depuis quand ?'],
                ['name' => 'expected', 'label' => 'Quels résultats attendez-vous ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '03. Équipements et environnement informatique',
            'fields' => [
                ['name' => 'equipment', 'label' => 'Équipements concernés', 'type' => 'checkboxes', 'options' => ['Ordinateurs fixes', 'Ordinateurs portables', 'Serveurs', 'Imprimantes / scanners', 'Réseau / Wi-Fi', 'Smartphones / tablettes', 'Périphériques', 'Autre'], 'full' => true],
                ['name' => 'device_count', 'label' => 'Nombre approximatif d’équipements', 'type' => 'number', 'min' => 0],
                ['name' => 'os', 'label' => 'Systèmes d’exploitation', 'type' => 'text', 'placeholder' => 'Windows, macOS, Linux, Android…'],
                ['name' => 'software', 'label' => 'Logiciels / applications concernés', 'type' => 'text'],
                ['name' => 'internet', 'label' => 'Connexion Internet / opérateur', 'type' => 'text'],
                ['name' => 'network', 'label' => 'Environnement en réseau ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Je ne sais pas']],
                ['name' => 'technical', 'label' => 'Précisions techniques (modèles, versions, messages d’erreur…)', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '04. Incidents et niveau de priorité',
            'fields' => [
                ['name' => 'issue', 'label' => 'Nature du problème', 'type' => 'select', 'options' => ['Ordinateur lent ou bloqué', 'Panne matérielle', 'Problème logiciel', 'Connexion / réseau', 'Messagerie / comptes', 'Impression', 'Sécurité / virus', 'Perte / récupération de données', 'Installation / mise à jour', 'Autre']],
                ['name' => 'frequency', 'label' => 'Fréquence', 'type' => 'select', 'options' => ['Ponctuel', 'Occasionnel', 'Récurrent', 'Permanent / bloquant']],
                ['name' => 'users', 'label' => 'Nombre d’utilisateurs impactés', 'type' => 'number', 'min' => 0],
                ['name' => 'urgency', 'label' => 'Niveau d’urgence', 'type' => 'select', 'required' => true, 'options' => ['Critique — activité arrêtée', 'Élevé — forte perturbation', 'Modéré — gêne partielle', 'Faible — non urgent']],
                ['name' => 'history', 'label' => 'Depuis quand ? Quelles actions avez-vous déjà tentées ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '05. Sécurité, sauvegarde et accès',
            'fields' => [
                ['name' => 'security', 'label' => 'Antivirus / solution de sécurité ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Je ne sais pas']],
                ['name' => 'backup', 'label' => 'Sauvegardes des données ?', 'type' => 'select', 'options' => ['Oui, automatiques', 'Oui, manuelles', 'Non', 'Je ne sais pas']],
                ['name' => 'data_loss', 'label' => 'Perte de données suspectée ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À vérifier']],
                ['name' => 'remote', 'label' => 'Assistance à distance envisageable ?', 'type' => 'select', 'options' => ['Oui, avec autorisation', 'Non', 'À discuter']],
                ['name' => 'security_notes', 'label' => 'Contraintes de sécurité, confidentialité ou accès', 'type' => 'textarea', 'full' => true],
                ['name' => 'security_reminder', 'label' => 'Rappel de sécurité', 'type' => 'textarea', 'default' => 'Ne communiquez aucun mot de passe ni code confidentiel dans ce formulaire.', 'full' => true],
            ],
        ],
        [
            'title' => '06. Modalités d’intervention',
            'fields' => [
                ['name' => 'mode', 'label' => 'Mode d’intervention préféré', 'type' => 'radio', 'options' => ['À distance', 'Sur site', 'Les deux', 'À déterminer']],
                ['name' => 'site_address', 'label' => 'Adresse du site (si intervention sur place)', 'type' => 'text'],
                ['name' => 'availability', 'label' => 'Disponibilités habituelles', 'type' => 'text', 'placeholder' => 'Jours et horaires'],
                ['name' => 'coverage', 'label' => 'Type de prise en charge souhaité', 'type' => 'select', 'options' => ['Intervention ponctuelle', 'Forfait d’heures', 'Contrat mensuel', 'Contrat annuel', 'À conseiller']],
                ['name' => 'organization', 'label' => 'Contraintes d’organisation', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '07. Services complémentaires',
            'fields' => [
                ['name' => 'extras', 'label' => 'Souhaitez-vous également bénéficier de…', 'type' => 'checkboxes', 'options' => ['Formation des utilisateurs', 'Conseils en cybersécurité', 'Mise à niveau du matériel', 'Installation de logiciels', 'Organisation des sauvegardes', 'Gestion du parc informatique', 'Conseil en évolution informatique'], 'full' => true],
                ['name' => 'future', 'label' => 'Autres besoins ou projets à venir', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '08. Budget et délais',
            'fields' => [
                ['name' => 'budget', 'label' => 'Budget estimatif', 'type' => 'select', 'options' => ['Moins de 100 €', '100 à 300 €', '300 à 700 €', '700 à 1 500 €', 'Plus de 1 500 €', 'À définir après diagnostic']],
                ['name' => 'date', 'label' => 'Date souhaitée d’intervention', 'type' => 'date'],
                ['name' => 'deadline', 'label' => 'Délai souhaité', 'type' => 'select', 'options' => ['Immédiat / urgent', 'Dans les 48 heures', 'Cette semaine', 'Dans les 2 à 4 semaines', 'Flexible']],
                ['name' => 'quote', 'label' => 'Devis avant intervention ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Selon le diagnostic']],
            ],
        ],
        [
            'title' => '09. Validation et remarques',
            'fields' => [
                ['name' => 'source', 'label' => 'Comment avez-vous connu EUNITAL ?', 'type' => 'text', 'full' => true],
                ['name' => 'comments', 'label' => 'Informations ou remarques complémentaires', 'type' => 'textarea', 'full' => true],
                ['name' => 'documents', 'label' => 'Documents que vous pourrez transmettre ultérieurement', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Photos, captures d’écran sans données sensibles, inventaire du matériel…'],
                ['name' => 'consent', 'label' => 'Je confirme l’exactitude des informations et j’accepte d’être recontacté(e) au sujet de cette demande.', 'type' => 'checkboxes', 'required' => true, 'options' => ['yes'], 'full' => true],
            ],
        ],
    ],
];
$forms['developpement']['developpement_gestion_de_bases_de_donnees'] = [
    'title' => 'Formulaire de brief — Création et gestion de bases de données',
    'description' => 'Document de prospection commerciale et de cadrage technique pour concevoir, organiser, sécuriser et gérer votre base de données.',
    'sections' => [
        [
            'title' => '01. Informations sur le client',
            'fields' => [
                ['name' => 'organisation', 'label' => 'Nom de l’entreprise ou de l’organisation', 'type' => 'text', 'required' => true],
                ['name' => 'representant', 'label' => 'Nom et prénom du représentant', 'type' => 'text', 'required' => true],
                ['name' => 'fonction', 'label' => 'Fonction du représentant', 'type' => 'text'],
                ['name' => 'secteur', 'label' => 'Secteur d’activité', 'type' => 'text'],
                ['name' => 'telephone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true],
                ['name' => 'email', 'label' => 'Adresse e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'localisation', 'label' => 'Ville et pays', 'type' => 'text'],
                ['name' => 'site_web', 'label' => 'Site web (si disponible)', 'type' => 'url', 'placeholder' => 'https://'],
            ],
        ],
        [
            'title' => '02. Identification du besoin',
            'fields' => [
                ['name' => 'besoin_principal', 'label' => 'Quel est votre besoin principal ?', 'type' => 'checkboxes', 'required' => true, 'options' => ['Création d’une nouvelle base de données', 'Organisation d’une base de données existante', 'Modernisation d’une ancienne base de données', 'Migration de données vers un nouveau système', 'Correction et nettoyage de données', 'Maintenance d’une base de données', 'Optimisation des performances', 'Sécurisation d’une base de données', 'Autre'], 'full' => true],
                ['name' => 'description_besoin', 'label' => 'Décrivez votre besoin et les difficultés que vous rencontrez actuellement', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'objectif', 'label' => 'Quel objectif souhaitez-vous atteindre grâce à cette solution ?', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'autre_besoin', 'label' => 'Précision si vous avez sélectionné « Autre »', 'type' => 'text', 'full' => true],
            ],
        ],
        [
            'title' => '03. Nature des données à gérer',
            'fields' => [
                ['name' => 'types_donnees', 'label' => 'Quels types de données souhaitez-vous enregistrer et gérer ?', 'type' => 'checkboxes', 'options' => ['Informations sur les clients et prospects', 'Informations sur les employés et collaborateurs', 'Informations sur les membres et adhérents', 'Produits et services', 'Stocks et inventaires', 'Ventes et commandes', 'Factures et paiements', 'Informations scolaires et universitaires', 'Documents et dossiers administratifs', 'Rendez-vous et réservations', 'Informations financières et comptables', 'Autres'], 'full' => true],
                ['name' => 'autres_donnees', 'label' => 'Précisez les autres types de données', 'type' => 'text'],
                ['name' => 'etat_donnees', 'label' => 'Disposez-vous déjà de ces données ?', 'type' => 'checkboxes', 'options' => ['Oui, sous Excel', 'Oui, sous Word ou PDF', 'Oui, dans un logiciel existant', 'Oui, dans plusieurs fichiers', 'Non, elles sont à constituer'], 'full' => true],
                ['name' => 'volume_donnees', 'label' => 'Quel est le volume approximatif des données ?', 'type' => 'select', 'options' => ['Moins de 500 enregistrements', 'Entre 500 et 5 000', 'Entre 5 000 et 50 000', 'Entre 50 000 et 500 000', 'Plus de 500 000', 'Je ne sais pas']],
            ],
        ],
        [
            'title' => '04. Fonctionnalités souhaitées',
            'fields' => [
                ['name' => 'fonctionnalites', 'label' => 'Quelles fonctionnalités souhaitez-vous intégrer ?', 'type' => 'checkboxes', 'options' => ['Ajouter de nouvelles données', 'Modifier les informations enregistrées', 'Supprimer ou archiver des données', 'Rechercher rapidement des informations', 'Filtrer et trier les données', 'Importer des fichiers Excel ou CSV', 'Exporter les données en Excel, PDF ou CSV', 'Générer automatiquement des rapports', 'Produire des statistiques', 'Créer des tableaux de bord', 'Gérer les utilisateurs et leurs permissions', 'Suivre l’historique des opérations', 'Recevoir des notifications', 'Effectuer des sauvegardes automatiques', 'Autres'], 'full' => true],
                ['name' => 'autres_fonctionnalites', 'label' => 'Précisez les autres fonctionnalités attendues', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '05. Utilisateurs et gestion des accès',
            'fields' => [
                ['name' => 'nombre_utilisateurs', 'label' => 'Combien de personnes utiliseront la base de données ?', 'type' => 'select', 'options' => ['Une seule personne', 'Entre 2 et 5 personnes', 'Entre 6 et 20 personnes', 'Entre 21 et 50 personnes', 'Plus de 50 personnes']],
                ['name' => 'profils', 'label' => 'Quels profils auront accès à la base de données ?', 'type' => 'checkboxes', 'options' => ['Administrateur', 'Direction générale', 'Comptabilité et finance', 'Service commercial', 'Ressources humaines', 'Employés et agents', 'Clients ou utilisateurs externes', 'Autres'], 'full' => true],
                ['name' => 'autres_profils', 'label' => 'Précisez les autres profils', 'type' => 'text'],
                ['name' => 'niveaux_acces', 'label' => 'Souhaitez-vous attribuer des niveaux d’accès différents ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À définir avec EUNITAL']],
                ['name' => 'precisions_acces', 'label' => 'Précisions sur les accès et permissions', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '06. Environnement technique et hébergement',
            'fields' => [
                ['name' => 'environnement_hebergement', 'label' => 'Où souhaitez-vous accéder à votre base de données ?', 'type' => 'select', 'options' => ['Sur un ordinateur local', 'Sur un serveur interne', 'En ligne, depuis Internet', 'Sur un cloud', 'Je souhaite être conseillé']],
                ['name' => 'appareils', 'label' => 'Sur quels appareils souhaitez-vous utiliser le système ?', 'type' => 'checkboxes', 'options' => ['Ordinateurs', 'Smartphones', 'Tablettes', 'Tous ces appareils'], 'full' => true],
                ['name' => 'integrations', 'label' => 'La base de données doit-elle être connectée à d’autres solutions ?', 'type' => 'checkboxes', 'options' => ['Un site web', 'Une application mobile', 'Un logiciel de gestion', 'Un logiciel comptable', 'Un CRM', 'Une plateforme de paiement', 'Une API externe', 'Autre'], 'full' => true],
                ['name' => 'autres_integrations', 'label' => 'Précisez les autres solutions à connecter', 'type' => 'text'],
                ['name' => 'details_logiciels', 'label' => 'Précisez les logiciels ou plateformes concernés', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '07. Sécurité et protection des données',
            'fields' => [
                ['name' => 'confidentialite', 'label' => 'Les données à gérer sont-elles confidentielles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Certaines données uniquement']],
                ['name' => 'mesures_securite', 'label' => 'Quelles mesures de sécurité souhaitez-vous ?', 'type' => 'checkboxes', 'options' => ['Connexion sécurisée par identifiant et mot de passe', 'Gestion des permissions par utilisateur', 'Sauvegardes automatiques', 'Chiffrement des données', 'Journalisation des connexions et opérations', 'Protection contre les accès non autorisés', 'Récupération des données en cas d’incident', 'Autres'], 'full' => true],
                ['name' => 'autres_mesures_securite', 'label' => 'Précisez les autres mesures de sécurité', 'type' => 'text'],
                ['name' => 'exigences_confidentialite', 'label' => 'Exigences particulières concernant la confidentialité', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '08. Migration, maintenance et accompagnement',
            'fields' => [
                ['name' => 'migration', 'label' => 'Souhaitez-vous transférer des données depuis un ancien système ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À déterminer']],
                ['name' => 'formation', 'label' => 'Souhaitez-vous bénéficier d’une formation à l’utilisation du système ?', 'type' => 'select', 'options' => ['Oui, formation complète', 'Oui, formation de prise en main', 'Non']],
                ['name' => 'maintenance', 'label' => 'Souhaitez-vous un service de maintenance après livraison ?', 'type' => 'checkboxes', 'options' => ['Maintenance régulière', 'Assistance technique à la demande', 'Surveillance et optimisation périodiques', 'Non', 'À discuter'], 'full' => true],
                ['name' => 'accompagnement', 'label' => 'Quel accompagnement souhaitez-vous ?', 'type' => 'select', 'options' => ['Conception uniquement', 'Conception et développement', 'Développement et déploiement', 'Développement, déploiement et formation', 'Accompagnement complet avec maintenance']],
            ],
        ],
        [
            'title' => '09. Budget et délais',
            'fields' => [
                ['name' => 'budget', 'label' => 'Quel est votre budget prévisionnel ?', 'type' => 'select', 'options' => ['Moins de 100 000 FCFA', '100 000 à 300 000 FCFA', '300 000 à 500 000 FCFA', '500 000 à 1 000 000 FCFA', 'Plus de 1 000 000 FCFA', 'Budget à déterminer après étude']],
                ['name' => 'delai', 'label' => 'Quel est votre délai souhaité ?', 'type' => 'select', 'options' => ['Immédiatement', 'Dans les deux semaines', 'Dans un mois', 'Dans les deux à trois mois', 'Plus de trois mois', 'Date précise']],
                ['name' => 'date_precise', 'label' => 'Date précise souhaitée (si applicable)', 'type' => 'date'],
                ['name' => 'approbation', 'label' => 'Le projet a-t-il déjà reçu une approbation interne ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'En attente de validation']],
            ],
        ],
        [
            'title' => '10. Informations complémentaires et suivi commercial',
            'fields' => [
                ['name' => 'documents_disponibles', 'label' => 'Disposez-vous de documents pouvant aider à comprendre votre besoin ?', 'type' => 'checkboxes', 'options' => ['Fichiers Excel ou CSV', 'Ancienne base de données', 'Documentation technique', 'Captures d’écran', 'Exemples de rapports', 'Aucun document disponible'], 'full' => true],
                ['name' => 'autres_attentes', 'label' => 'Autres précisions ou attentes du client', 'type' => 'textarea', 'full' => true],
                ['name' => 'prospecteur', 'label' => 'Nom du prospecteur EUNITAL', 'type' => 'text'],
                ['name' => 'date_entretien', 'label' => 'Date de l’entretien', 'type' => 'date'],
                ['name' => 'statut_prospect', 'label' => 'Statut du prospect', 'type' => 'select', 'options' => ['Nouveau contact', 'Besoin identifié', 'Proposition technique à préparer', 'Devis transmis', 'En négociation', 'Projet confirmé', 'À relancer', 'Sans suite']],
                ['name' => 'prochaine_action', 'label' => 'Prochaine action à effectuer', 'type' => 'textarea', 'full' => true],
                ['name' => 'date_relance', 'label' => 'Date de relance', 'type' => 'date'],
            ],
        ],
        [
            'title' => '11. Observations techniques — réservé à EUNITAL',
            'fields' => [
                ['name' => 'type_base_envisage', 'label' => 'Type de base de données envisagé', 'type' => 'text'],
                ['name' => 'architecture', 'label' => 'Architecture technique', 'type' => 'textarea', 'full' => true],
                ['name' => 'technologies', 'label' => 'Technologies envisagées', 'type' => 'text'],
                ['name' => 'capacite_stockage', 'label' => 'Volume et capacité de stockage', 'type' => 'text'],
                ['name' => 'hebergement', 'label' => 'Besoins en hébergement', 'type' => 'textarea', 'full' => true],
                ['name' => 'niveau_securite', 'label' => 'Niveau de sécurité requis', 'type' => 'text'],
                ['name' => 'complexite', 'label' => 'Complexité du projet', 'type' => 'text'],
                ['name' => 'ressources', 'label' => 'Ressources nécessaires', 'type' => 'textarea', 'full' => true],
                ['name' => 'duree_estimee', 'label' => 'Durée estimée de réalisation', 'type' => 'text'],
                ['name' => 'cout_technique', 'label' => 'Coût technique estimé', 'type' => 'text'],
                ['name' => 'recommandations', 'label' => 'Recommandations techniques', 'type' => 'textarea', 'full' => true],
                ['name' => 'decision_technique', 'label' => 'Décision technique', 'type' => 'select', 'options' => ['Projet réalisable', 'Projet réalisable sous certaines conditions', 'Étude technique approfondie nécessaire', 'Projet à réorienter']],
            ],
        ],
        [
            'title' => '12. Validation du brief',
            'fields' => [
                ['name' => 'nom_representant_client', 'label' => 'Nom du représentant du client', 'type' => 'text'],
                ['name' => 'signature_client', 'label' => 'Signature du client (à compléter lors de la validation)', 'type' => 'text'],
                ['name' => 'nom_representant_eunital', 'label' => 'Nom du représentant EUNITAL', 'type' => 'text'],
                ['name' => 'signature_eunital', 'label' => 'Signature du représentant EUNITAL (à compléter lors de la validation)', 'type' => 'text'],
                ['name' => 'date_validation', 'label' => 'Date de validation', 'type' => 'date'],
            ],
        ],
    ],
];
$forms['developpement']['developpement_hebergement_et_deploiement_dapplications'] = [
    'title' => 'Brief — Hébergement et déploiement d’applications',
    'description' => 'Recueillons les informations nécessaires pour héberger, mettre en ligne et exploiter votre application dans de bonnes conditions.',
    'sections' => [
        [
            'title' => '1. Informations sur le client',
            'fields' => [
                ['name' => 'nom', 'label' => 'Nom et prénom', 'type' => 'text', 'required' => true],
                ['name' => 'entreprise', 'label' => 'Entreprise / organisation', 'type' => 'text'],
                ['name' => 'email', 'label' => 'Adresse e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'telephone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true],
                ['name' => 'site', 'label' => 'Site web actuel', 'type' => 'url', 'placeholder' => 'https://'],
                ['name' => 'referent', 'label' => 'Personne référente / fonction', 'type' => 'text'],
                ['name' => 'activite', 'label' => 'Présentez brièvement votre activité et votre projet', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '2. Application à héberger',
            'fields' => [
                ['name' => 'projet', 'label' => 'Nom du projet / de l’application', 'type' => 'text', 'required' => true],
                ['name' => 'type_app', 'label' => 'Type d’application', 'type' => 'select', 'required' => true, 'options' => ['Site web vitrine', 'Site e-commerce', 'Application web', 'Application mobile avec backend', 'API / service web', 'Plateforme SaaS', 'Logiciel métier', 'Autre']],
                ['name' => 'url_actuelle', 'label' => 'URL actuelle (si existante)', 'type' => 'url', 'placeholder' => 'https://'],
                ['name' => 'etat', 'label' => 'État du projet', 'type' => 'select', 'options' => ['En développement', 'Prêt à être mis en production', 'Déjà en ligne, migration souhaitée', 'En production avec problèmes', 'Refonte / nouvelle version']],
                ['name' => 'technologies', 'label' => 'Technologies utilisées (langages, frameworks, CMS…)', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Ex. : PHP, Laravel, Node.js, React, Python, WordPress…'],
                ['name' => 'depot', 'label' => 'Référentiel de code / dépôt et accès disponibles', 'type' => 'textarea', 'full' => true, 'placeholder' => 'GitHub, GitLab, Bitbucket, dépôt privé… Ne transmettez aucun mot de passe ici.'],
            ],
        ],
        [
            'title' => '3. Objectifs de l’hébergement',
            'fields' => [
                ['name' => 'objectifs', 'label' => 'Quels résultats attendez-vous de cette prestation ?', 'type' => 'checkboxes', 'options' => ['Mise en ligne initiale', 'Migration depuis un hébergeur', 'Amélioration des performances', 'Renforcement de la sécurité', 'Haute disponibilité / continuité', 'Optimisation des coûts', 'Environnements de test / préproduction', 'Automatisation du déploiement'], 'full' => true],
                ['name' => 'resultat', 'label' => 'Priorité principale et résultat attendu', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '4. Hébergement et infrastructure',
            'fields' => [
                ['name' => 'hebergeur', 'label' => 'Hébergement actuel', 'type' => 'select', 'options' => ['Aucun', 'Hébergement mutualisé', 'VPS / serveur dédié', 'Cloud public', 'Cloud privé / infrastructure interne', 'Plateforme serverless / PaaS', 'Autre / ne sait pas']],
                ['name' => 'fournisseur', 'label' => 'Fournisseur actuel ou souhaité', 'type' => 'text', 'placeholder' => 'OVHcloud, AWS, Azure, GCP…'],
                ['name' => 'preference', 'label' => 'Préférence d’hébergement', 'type' => 'select', 'options' => ['Sans préférence / à conseiller', 'Serveur en France', 'Serveur dans l’Union européenne', 'Cloud international', 'Infrastructure sur site (on-premise)']],
                ['name' => 'environnements', 'label' => 'Environnements nécessaires', 'type' => 'select', 'options' => ['Production uniquement', 'Développement et production', 'Développement, préproduction et production', 'À définir']],
                ['name' => 'ressources', 'label' => 'Ressources estimées ou contraintes connues', 'type' => 'textarea', 'full' => true, 'placeholder' => 'CPU, RAM, stockage, bande passante, OS, conteneurs Docker/Kubernetes…'],
                ['name' => 'contraintes_hebergement', 'label' => 'Contraintes d’hébergement (localisation, réseau, fournisseur imposé…)', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '5. Domaine, DNS et certificats',
            'fields' => [
                ['name' => 'domaine', 'label' => 'Nom de domaine concerné', 'type' => 'text', 'placeholder' => 'exemple.fr'],
                ['name' => 'dns', 'label' => 'Gestionnaire du domaine / DNS', 'type' => 'text', 'placeholder' => 'Registrar ou fournisseur DNS'],
                ['name' => 'domaine_reserve', 'label' => 'Le domaine est-il déjà réservé ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À vérifier']],
                ['name' => 'ssl', 'label' => 'Certificat SSL/TLS', 'type' => 'select', 'options' => ['À installer', 'Déjà en place', 'À renouveler / corriger', 'Je ne sais pas']],
                ['name' => 'sous_domaines', 'label' => 'Sous-domaines, redirections ou configuration DNS souhaités', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Ex. : app.exemple.fr, api.exemple.fr, www…'],
            ],
        ],
        [
            'title' => '6. Déploiement et mise en production',
            'fields' => [
                ['name' => 'mode_deploiement', 'label' => 'Mode de déploiement souhaité', 'type' => 'select', 'options' => ['À définir', 'Manuel', 'Automatisé (CI/CD)', 'Déploiement via conteneurs', 'Déploiement sans interruption si possible']],
                ['name' => 'frequence', 'label' => 'Fréquence des mises à jour', 'type' => 'select', 'options' => ['Rare / ponctuelle', 'Hebdomadaire', 'Plusieurs fois par semaine', 'Quotidienne', 'Continue / plusieurs fois par jour']],
                ['name' => 'outils', 'label' => 'Outils déjà utilisés', 'type' => 'text', 'placeholder' => 'GitHub Actions, GitLab CI, Docker…'],
                ['name' => 'fenetre', 'label' => 'Fenêtre de mise en production souhaitée', 'type' => 'text', 'placeholder' => 'Ex. : en soirée, week-end…'],
                ['name' => 'etapes', 'label' => 'Étapes, validations ou procédures de déploiement connues', 'type' => 'textarea', 'full' => true],
                ['name' => 'rollback', 'label' => 'Plan de retour arrière (rollback) ou exigences de disponibilité', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '7. Données, bases de données et stockage',
            'fields' => [
                ['name' => 'bdd', 'label' => 'Base de données utilisée', 'type' => 'text', 'placeholder' => 'PostgreSQL, MySQL, MongoDB…'],
                ['name' => 'volume_donnees', 'label' => 'Volume de données estimé', 'type' => 'text', 'placeholder' => 'Ex. : 5 Go, 500 000 enregistrements…'],
                ['name' => 'migration_donnees', 'label' => 'Migration de données nécessaire ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À évaluer']],
                ['name' => 'stockage', 'label' => 'Stockage de fichiers / médias', 'type' => 'select', 'options' => ['Local sur serveur', 'Stockage objet / cloud', 'NAS / stockage interne', 'Aucun besoin particulier', 'À définir']],
                ['name' => 'sauvegardes', 'label' => 'Sauvegardes attendues (fréquence, rétention, restauration)', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Ex. : sauvegarde quotidienne, conservation 30 jours, test de restauration…'],
            ],
        ],
        [
            'title' => '8. Sécurité et conformité',
            'fields' => [
                ['name' => 'securite', 'label' => 'Mesures de sécurité souhaitées', 'type' => 'checkboxes', 'options' => ['Pare-feu / filtrage réseau', 'Protection anti-DDoS', 'Gestion des accès et MFA', 'Chiffrement des données', 'Journalisation et audit', 'Mises à jour de sécurité'], 'full' => true],
                ['name' => 'donnees_personnelles', 'label' => 'Données personnelles traitées ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À confirmer']],
                ['name' => 'conformite', 'label' => 'Exigences réglementaires / certifications', 'type' => 'text', 'placeholder' => 'RGPD, HDS, ISO 27001…'],
                ['name' => 'risques', 'label' => 'Exigences de sécurité spécifiques ou risques identifiés', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '9. Performance, disponibilité et supervision',
            'fields' => [
                ['name' => 'utilisateurs', 'label' => 'Nombre d’utilisateurs / visiteurs estimé', 'type' => 'text', 'placeholder' => 'Actuel et cible, simultané si connu'],
                ['name' => 'disponibilite', 'label' => 'Disponibilité souhaitée', 'type' => 'select', 'options' => ['À définir', 'Standard', 'Élevée', 'Très élevée / service critique']],
                ['name' => 'plages', 'label' => 'Plages d’utilisation', 'type' => 'select', 'options' => ['Heures ouvrées', 'Étendu (soir et week-end)', '24 h/24 et 7 j/7', 'Saisonnier / variable']],
                ['name' => 'supervision', 'label' => 'Supervision souhaitée', 'type' => 'select', 'options' => ['Disponibilité uniquement', 'Serveur, ressources et application', 'Logs, alertes et performances', 'Supervision complète avec astreinte', 'À conseiller']],
                ['name' => 'performance', 'label' => 'Objectifs de performance, temps de réponse ou pics de charge', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '10. Maintenance et assistance',
            'fields' => [
                ['name' => 'maintenance', 'label' => 'Maintenance souhaitée', 'type' => 'select', 'options' => ['Installation et mise en ligne uniquement', 'Maintenance corrective', 'Maintenance préventive et mises à jour', 'Infogérance / administration serveur', 'Accompagnement évolutif']],
                ['name' => 'support', 'label' => 'Support attendu', 'type' => 'select', 'options' => ['Heures ouvrées', 'Étendu', '24/7 pour incidents critiques', 'À définir']],
                ['name' => 'equipe', 'label' => 'Équipe technique côté client', 'type' => 'select', 'options' => ['Oui, équipe dédiée', 'Oui, interlocuteur technique', 'Non', 'Prestataire externe']],
                ['name' => 'documentation', 'label' => 'Documentation / transfert de compétences', 'type' => 'select', 'options' => ['Documentation technique', 'Formation de l’équipe', 'Les deux', 'Non nécessaire']],
                ['name' => 'support_details', 'label' => 'Précisions sur le support, les délais d’intervention ou les responsabilités', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '11. Planning, budget et priorités',
            'fields' => [
                ['name' => 'date_mise_en_ligne', 'label' => 'Date souhaitée de mise en ligne', 'type' => 'date'],
                ['name' => 'urgence', 'label' => 'Urgence', 'type' => 'select', 'options' => ['Normale', 'Prioritaire', 'Urgente', 'Critique / date impérative']],
                ['name' => 'budget_initial', 'label' => 'Budget initial estimé', 'type' => 'select', 'options' => ['Moins de 500 €', '500 à 1 500 €', '1 500 à 3 000 €', '3 000 à 5 000 €', '5 000 à 10 000 €', 'Plus de 10 000 €', 'À définir']],
                ['name' => 'budget_mensuel', 'label' => 'Budget mensuel d’hébergement / maintenance', 'type' => 'select', 'options' => ['Moins de 50 €', '50 à 150 €', '150 à 300 €', '300 à 600 €', 'Plus de 600 €', 'À définir']],
                ['name' => 'indispensables', 'label' => 'Éléments indispensables pour le lancement (MVP)', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '12. Documents, accès et remarques',
            'fields' => [
                ['name' => 'documents', 'label' => 'Documents ou informations disponibles', 'type' => 'checkboxes', 'options' => ['Documentation technique', 'Schéma d’architecture', 'Accès hébergeur existant', 'Accès DNS / domaine', 'Dépôt de code', 'Procédure de déploiement'], 'hint' => 'Ne saisissez pas de mots de passe, clés privées, tokens ou secrets dans ce formulaire. Les accès seront échangés par un canal sécurisé.', 'full' => true],
                ['name' => 'remarques', 'label' => 'Questions, attentes ou contraintes complémentaires', 'type' => 'textarea', 'full' => true],
                ['name' => 'origine', 'label' => 'Comment avez-vous connu EUNITAL ?', 'type' => 'select', 'options' => ['Recommandation', 'Réseaux sociaux', 'Recherche Internet', 'Ancien client', 'Autre']],
                ['name' => 'accord', 'label' => 'Je confirme que les informations fournies sont exactes et j’accepte d’être recontacté(e) au sujet de cette demande.', 'type' => 'checkboxes', 'required' => true, 'options' => ['on'], 'full' => true],
            ],
        ],
    ],
];
$forms['developpement']['developpement_maintenance_et_evolution_de_logiciels'] = [
    'title' => 'Brief — Maintenance et évolution de logiciels',
    'description' => 'Décrivez votre logiciel, les difficultés rencontrées et les améliorations que vous souhaitez apporter.',
    'sections' => [
        [
            'title' => '1. Informations sur le client',
            'fields' => [
                ['name' => 'nom', 'label' => 'Nom et prénom', 'type' => 'text', 'required' => true],
                ['name' => 'entreprise', 'label' => 'Entreprise / organisation', 'type' => 'text'],
                ['name' => 'email', 'label' => 'Adresse e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'telephone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true],
                ['name' => 'site', 'label' => 'Site web', 'type' => 'url', 'placeholder' => 'https://'],
                ['name' => 'referent', 'label' => 'Personne référente / fonction', 'type' => 'text'],
                ['name' => 'activite', 'label' => 'Présentez votre activité et l’utilisation du logiciel dans votre organisation', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '2. Présentation du logiciel',
            'fields' => [
                ['name' => 'logiciel', 'label' => 'Nom du logiciel / projet', 'type' => 'text', 'required' => true],
                ['name' => 'type', 'label' => 'Type de logiciel', 'type' => 'select', 'required' => true, 'options' => ['Application web', 'Application mobile', 'Logiciel de bureau', 'Logiciel de gestion / métier', 'Site web / e-commerce', 'Plateforme SaaS', 'API / service backend', 'Autre']],
                ['name' => 'origine', 'label' => 'Logiciel développé par', 'type' => 'select', 'options' => ['Votre entreprise / équipe interne', 'EUNITAL', 'Un prestataire externe', 'Un éditeur / fournisseur', 'Je ne sais pas']],
                ['name' => 'date_mise_service', 'label' => 'Date de mise en service approximative', 'type' => 'date'],
                ['name' => 'technologies', 'label' => 'Technologies utilisées (si connues)', 'type' => 'text', 'placeholder' => 'Langages, frameworks, base de données…'],
                ['name' => 'version', 'label' => 'Version actuelle', 'type' => 'text', 'placeholder' => 'Numéro de version ou date de release'],
                ['name' => 'fonctionnalites', 'label' => 'Décrivez les principales fonctionnalités du logiciel', 'type' => 'textarea', 'full' => true],
                ['name' => 'ressources', 'label' => 'Documentation, dépôt de code ou environnement de démonstration disponible', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Indiquez les références utiles, sans communiquer de mots de passe ni de secrets.'],
            ],
        ],
        [
            'title' => '3. Situation actuelle et difficultés',
            'fields' => [
                ['name' => 'problemes', 'label' => 'Quels problèmes motivent votre demande ?', 'type' => 'checkboxes', 'options' => ['Bugs et erreurs récurrentes', 'Lenteurs / problèmes de performances', 'Interface vieillissante', 'Fonctionnalités manquantes', 'Problèmes de sécurité', 'Compatibilité / obsolescence', 'Difficultés d’utilisation', 'Indisponibilités / incidents', 'Dette technique / code difficile à maintenir', 'Manque de documentation'], 'full' => true],
                ['name' => 'details_problemes', 'label' => 'Décrivez les anomalies ou difficultés observées (messages d’erreur, fréquence, circonstances)', 'type' => 'textarea', 'full' => true],
                ['name' => 'anciennete_problemes', 'label' => 'Depuis quand ces difficultés existent-elles ?', 'type' => 'text'],
                ['name' => 'impact', 'label' => 'Impact sur votre activité', 'type' => 'select', 'options' => ['Faible', 'Modéré', 'Important', 'Bloquant / critique']],
            ],
        ],
        [
            'title' => '4. Maintenance attendue',
            'fields' => [
                ['name' => 'maintenance', 'label' => 'Sélectionnez les interventions nécessaires', 'type' => 'checkboxes', 'options' => ['Maintenance corrective', 'Maintenance préventive', 'Mises à jour techniques et dépendances', 'Sécurité et correctifs', 'Optimisation des performances', 'Compatibilité navigateurs et appareils', 'Refactoring / amélioration du code', 'Sauvegardes et restauration', 'Surveillance et supervision', 'Documentation technique'], 'full' => true],
                ['name' => 'autres_maintenance', 'label' => 'Autres opérations de maintenance souhaitées', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '5. Évolutions fonctionnelles souhaitées',
            'fields' => [
                ['name' => 'nouvelles_fonctionnalites', 'label' => 'Quelles nouvelles fonctionnalités souhaitez-vous ajouter ?', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Décrivez chaque besoin, les utilisateurs concernés et le résultat attendu…'],
                ['name' => 'modifications', 'label' => 'Quelles fonctionnalités existantes souhaitez-vous modifier ou supprimer ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'refonte_ui', 'label' => 'Refonte de l’interface / expérience utilisateur ?', 'type' => 'select', 'options' => ['Oui, refonte importante', 'Oui, ajustements ciblés', 'Non', 'À étudier']],
                ['name' => 'automatisations', 'label' => 'Automatisations ou workflows à ajouter', 'type' => 'text'],
                ['name' => 'references', 'label' => 'Exemples de logiciels ou interfaces de référence (facultatif)', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '6. Utilisateurs et usages',
            'fields' => [
                ['name' => 'nb_utilisateurs', 'label' => 'Nombre d’utilisateurs', 'type' => 'text', 'placeholder' => 'Nombre approximatif'],
                ['name' => 'profils', 'label' => 'Profils d’utilisateurs', 'type' => 'text', 'placeholder' => 'Administrateurs, employés, clients…'],
                ['name' => 'usage', 'label' => 'Utilisation principale', 'type' => 'select', 'options' => ['Interne à l’entreprise', 'Pour les clients / grand public', 'Partenaires / fournisseurs', 'Mixte']],
                ['name' => 'appareils', 'label' => 'Appareils utilisés', 'type' => 'select', 'options' => ['Ordinateurs', 'Mobiles / tablettes', 'Ordinateurs et mobiles', 'Terminaux spécifiques']],
                ['name' => 'roles', 'label' => 'Rôles, droits d’accès ou parcours utilisateurs à prendre en compte', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '7. Environnement technique et intégrations',
            'fields' => [
                ['name' => 'hebergement', 'label' => 'Hébergement actuel', 'type' => 'text', 'placeholder' => 'Serveur, cloud, hébergeur…'],
                ['name' => 'base_donnees', 'label' => 'Base de données', 'type' => 'text', 'placeholder' => 'MySQL, PostgreSQL, MongoDB…'],
                ['name' => 'integrations', 'label' => 'Services et outils connectés', 'type' => 'textarea', 'full' => true, 'placeholder' => 'API, paiement, CRM, ERP, e-mailing, outils tiers…'],
                ['name' => 'contraintes', 'label' => 'Contraintes techniques connues (versions, compatibilité, dépendances)', 'type' => 'textarea', 'full' => true],
                ['name' => 'a_conserver', 'label' => 'Éléments à conserver impérativement (données, fonctions, interfaces, intégrations)', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '8. Données, sécurité et conformité',
            'fields' => [
                ['name' => 'donnees_personnelles', 'label' => 'Le logiciel traite-t-il des données personnelles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À confirmer']],
                ['name' => 'reglementation', 'label' => 'Exigences réglementaires particulières', 'type' => 'text', 'placeholder' => 'RGPD, secteur réglementé…'],
                ['name' => 'auth', 'label' => 'Authentification et gestion des accès', 'type' => 'select', 'options' => ['À conserver en l’état', 'À renforcer', 'À mettre en place', 'À évaluer']],
                ['name' => 'backups', 'label' => 'Sauvegardes existantes', 'type' => 'select', 'options' => ['Quotidiennes', 'Hebdomadaires', 'Occasionnelles', 'Aucune / inconnue']],
                ['name' => 'securite_details', 'label' => 'Exigences de sécurité, confidentialité, audit ou conservation des données', 'type' => 'textarea', 'full' => true],
                ['name' => 'precautions', 'label' => 'Précautions nécessaires pour les tests et la manipulation des données', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '9. Tests, validation et mise en production',
            'fields' => [
                ['name' => 'environnement_test', 'label' => 'Environnement de test disponible ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À créer', 'Je ne sais pas']],
                ['name' => 'validateur', 'label' => 'Qui validera les évolutions ?', 'type' => 'text'],
                ['name' => 'tests', 'label' => 'Tests attendus', 'type' => 'select', 'options' => ['Tests fonctionnels', 'Tests fonctionnels et techniques', 'Tests automatisés et non-régression', 'Tests de charge / sécurité', 'À définir avec le prestataire']],
                ['name' => 'deploiement', 'label' => 'Déploiement souhaité', 'type' => 'select', 'options' => ['En une seule mise à jour', 'Par étapes / lots', 'Sans interruption de service si possible', 'À planifier']],
                ['name' => 'disponibilite', 'label' => 'Contraintes de disponibilité, horaires ou périodes à éviter', 'type' => 'textarea', 'full' => true],
                ['name' => 'rollback', 'label' => 'Procédure de retour arrière ou de restauration souhaitée', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '10. Organisation et suivi de la maintenance',
            'fields' => [
                ['name' => 'accompagnement', 'label' => 'Type d’accompagnement', 'type' => 'select', 'options' => ['Intervention ponctuelle', 'Forfait d’heures', 'Contrat de maintenance récurrent', 'Équipe dédiée / accompagnement continu', 'À conseiller']],
                ['name' => 'frequence_suivi', 'label' => 'Fréquence de suivi souhaitée', 'type' => 'select', 'options' => ['À la demande', 'Mensuelle', 'Trimestrielle', 'Continue']],
                ['name' => 'interlocuteur_technique', 'label' => 'Interlocuteur technique disponible côté client ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À confirmer']],
                ['name' => 'formation', 'label' => 'Documentation / formation souhaitée', 'type' => 'select', 'options' => ['Documentation technique', 'Formation des utilisateurs', 'Transfert de compétences technique', 'Plusieurs de ces éléments', 'Non nécessaire']],
                ['name' => 'sla', 'label' => 'Délais de prise en charge ou niveaux de support attendus', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '11. Planning, budget et priorités',
            'fields' => [
                ['name' => 'date_demarrage', 'label' => 'Date souhaitée de démarrage', 'type' => 'date'],
                ['name' => 'date_limite', 'label' => 'Date limite ou échéance importante', 'type' => 'date'],
                ['name' => 'budget', 'label' => 'Budget estimé pour l’intervention', 'type' => 'select', 'options' => ['Moins de 500 €', '500 à 1 500 €', '1 500 à 3 000 €', '3 000 à 5 000 €', '5 000 à 10 000 €', 'Plus de 10 000 €', 'À définir']],
                ['name' => 'budget_recurrent', 'label' => 'Budget de maintenance récurrente', 'type' => 'select', 'options' => ['Moins de 100 € / mois', '100 à 300 € / mois', '300 à 600 € / mois', '600 à 1 000 € / mois', 'Plus de 1 000 € / mois', 'À définir']],
                ['name' => 'urgence', 'label' => 'Niveau d’urgence', 'type' => 'select', 'options' => ['Normal', 'Prioritaire', 'Urgent', 'Critique / logiciel bloqué']],
                ['name' => 'priorite', 'label' => 'Ordre de priorité des demandes', 'type' => 'select', 'options' => ['Stabiliser et corriger d’abord', 'Sécuriser et mettre à jour', 'Ajouter des fonctionnalités', 'Moderniser / optimiser', 'Tout cadrer par lots']],
                ['name' => 'top_priorites', 'label' => 'Quelles sont les trois améliorations les plus importantes ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '12. Documents et remarques complémentaires',
            'fields' => [
                ['name' => 'documents', 'label' => 'Documents ou informations disponibles', 'type' => 'checkboxes', 'options' => ['Cahier des charges', 'Documentation technique', 'Dépôt de code source', 'Captures d’écran / vidéos', 'Liste des anomalies', 'Maquettes / références UI', 'Schéma technique', 'Accès à un environnement de test'], 'hint' => 'Ne transmettez aucun mot de passe, clé privée, token ou donnée sensible dans ce formulaire. Les accès pourront être échangés par un canal sécurisé.', 'full' => true],
                ['name' => 'remarques', 'label' => 'Autres informations, attentes ou remarques', 'type' => 'textarea', 'full' => true],
                ['name' => 'origine_contact', 'label' => 'Comment avez-vous connu EUNITAL ?', 'type' => 'select', 'options' => ['Recommandation', 'Réseaux sociaux', 'Recherche Internet', 'Ancien client', 'Autre']],
                ['name' => 'accord', 'label' => 'Je confirme que les informations fournies sont exactes et j’accepte d’être recontacté(e) au sujet de cette demande.', 'type' => 'checkboxes', 'required' => true, 'options' => ['on'], 'full' => true],
            ],
        ],
    ],
];
$forms['developpement']['developpement_integration_de_solutions_informatiques'] = [
    'title' => 'Brief — Intégration de solutions informatiques',
    'description' => 'Décrivez les solutions informatiques à intégrer, votre environnement existant et les résultats attendus.',
    'sections' => [
        [
            'title' => '1. Informations sur le client',
            'fields' => [
                ['name' => 'entreprise', 'label' => 'Nom de l’entreprise ou de l’organisation', 'type' => 'text', 'required' => true],
                ['name' => 'representant', 'label' => 'Nom et prénom du représentant', 'type' => 'text', 'required' => true],
                ['name' => 'fonction', 'label' => 'Fonction du représentant', 'type' => 'text'],
                ['name' => 'secteur', 'label' => 'Secteur d’activité', 'type' => 'text'],
                ['name' => 'telephone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true],
                ['name' => 'email', 'label' => 'Adresse e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'ville_pays', 'label' => 'Ville et pays', 'type' => 'text'],
                ['name' => 'site_web', 'label' => 'Site web', 'type' => 'url', 'placeholder' => 'https://'],
            ],
        ],
        [
            'title' => '2. Identification du besoin',
            'fields' => [
                ['name' => 'besoin_principal', 'label' => 'Quel est votre besoin principal ?', 'type' => 'checkboxes', 'required' => true, 'options' => ['Intégration d’un nouveau logiciel dans l’entreprise', 'Installation et configuration de solutions informatiques', 'Connexion de plusieurs logiciels entre eux', 'Intégration d’une solution de gestion d’entreprise', 'Mise en place d’un système de gestion commerciale', 'Intégration d’une solution de gestion comptable', 'Intégration d’une solution de gestion des ressources humaines', 'Intégration d’une solution de gestion des stocks', 'Automatisation des processus de travail', 'Intégration de solutions cloud', 'Interconnexion de systèmes informatiques existants', 'Autre'], 'full' => true],
                ['name' => 'probleme_actuel', 'label' => 'Quel problème rencontrez-vous actuellement dans votre organisation ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'objectifs_integration', 'label' => 'Pourquoi souhaitez-vous intégrer une nouvelle solution informatique ?', 'type' => 'checkboxes', 'options' => ['Améliorer la productivité', 'Réduire les tâches manuelles', 'Centraliser les informations', 'Faciliter la communication entre les services', 'Améliorer le suivi des activités', 'Réduire les erreurs humaines', 'Améliorer la gestion de l’entreprise', 'Moderniser les infrastructures informatiques', 'Autre'], 'full' => true],
            ],
        ],
        [
            'title' => '3. Solutions informatiques à intégrer',
            'fields' => [
                ['name' => 'types_solutions', 'label' => 'Quels types de solutions souhaitez-vous intégrer ?', 'type' => 'checkboxes', 'options' => ['ERP (gestion intégrée de l’entreprise)', 'CRM (gestion de la relation client)', 'Logiciel de comptabilité et de finance', 'Logiciel de gestion commerciale', 'Logiciel de gestion des ressources humaines', 'Logiciel de gestion des stocks', 'Logiciel de gestion de projets', 'Logiciel de gestion documentaire', 'Solution de gestion scolaire', 'Solution de gestion médicale', 'Solution de facturation', 'Solution de paiement électronique', 'Solution de communication et collaboration', 'Solution de sauvegarde et stockage cloud', 'Solution de cybersécurité', 'Solution d’intelligence artificielle', 'Autre'], 'full' => true],
                ['name' => 'solution_identifiee', 'label' => 'Avez-vous déjà identifié la solution que vous souhaitez utiliser ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Je souhaite être conseillé par EUNITAL']],
                ['name' => 'nom_solution', 'label' => 'Si oui, précisez le nom du logiciel ou de la solution', 'type' => 'text'],
                ['name' => 'solution_acquise', 'label' => 'La solution est-elle déjà acquise ou sous licence ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'En cours d’acquisition', 'Je souhaite que EUNITAL m’accompagne dans le choix']],
            ],
        ],
        [
            'title' => '4. Environnement informatique existant',
            'fields' => [
                ['name' => 'infrastructure_existante', 'label' => 'Disposez-vous déjà d’une infrastructure informatique ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Partiellement']],
                ['name' => 'equipements', 'label' => 'Quels équipements utilisez-vous actuellement ?', 'type' => 'checkboxes', 'options' => ['Ordinateurs de bureau', 'Ordinateurs portables', 'Serveurs physiques', 'Serveurs virtuels', 'Imprimantes et scanners', 'Équipements réseau', 'Smartphones et tablettes', 'Autres'], 'full' => true],
                ['name' => 'systemes_exploitation', 'label' => 'Quels systèmes d’exploitation utilisez-vous ?', 'type' => 'checkboxes', 'options' => ['Windows', 'Linux', 'macOS', 'Android', 'iOS', 'Autres'], 'full' => true],
                ['name' => 'reseau_interne', 'label' => 'Disposez-vous d’un réseau informatique interne ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Partiellement']],
                ['name' => 'internet_professionnel', 'label' => 'Disposez-vous d’une connexion Internet professionnelle ?', 'type' => 'select', 'options' => ['Oui', 'Non']],
                ['name' => 'description_environnement', 'label' => 'Décrivez votre environnement informatique actuel', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '5. Intégration et interconnexion des systèmes',
            'fields' => [
                ['name' => 'connexion_outils', 'label' => 'Souhaitez-vous connecter plusieurs outils entre eux ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À déterminer']],
                ['name' => 'systemes_a_connecter', 'label' => 'Quels systèmes doivent communiquer entre eux ?', 'type' => 'checkboxes', 'options' => ['Site web et logiciel de gestion', 'Application mobile et serveur', 'Logiciel comptable et logiciel commercial', 'CRM et plateforme de vente', 'ERP et base de données', 'Système de paiement et plateforme commerciale', 'Logiciels internes et services cloud', 'Autres'], 'full' => true],
                ['name' => 'api', 'label' => 'Souhaitez-vous mettre en place des échanges automatiques de données (API) ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Je souhaite être conseillé']],
                ['name' => 'connexions_souhaitees', 'label' => 'Décrivez les échanges de données ou les connexions souhaités', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '6. Automatisation des processus',
            'fields' => [
                ['name' => 'activites_a_automatiser', 'label' => 'Quelles activités souhaitez-vous automatiser ?', 'type' => 'checkboxes', 'options' => ['Gestion des factures', 'Gestion des commandes', 'Gestion des paiements', 'Gestion des stocks', 'Gestion des clients', 'Gestion des rendez-vous', 'Gestion des employés', 'Envoi automatique d’e-mails', 'Génération automatique de rapports', 'Synchronisation des données', 'Notifications et alertes automatiques', 'Traitement automatique de documents', 'Autres'], 'full' => true],
                ['name' => 'taches_manuelles', 'label' => 'Quelles tâches sont actuellement effectuées manuellement et vous font perdre du temps ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'integration_ia', 'label' => 'Souhaitez-vous intégrer des outils d’intelligence artificielle ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À étudier avec EUNITAL']],
                ['name' => 'activites_ia', 'label' => 'Si oui, pour quelles activités ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '7. Utilisateurs et gestion des accès',
            'fields' => [
                ['name' => 'nombre_utilisateurs', 'label' => 'Combien de personnes utiliseront les solutions intégrées ?', 'type' => 'select', 'options' => ['1 à 5 personnes', '6 à 20 personnes', '21 à 50 personnes', '51 à 100 personnes', 'Plus de 100 personnes']],
                ['name' => 'services_utilisateurs', 'label' => 'Quels services utiliseront les solutions ?', 'type' => 'checkboxes', 'options' => ['Direction générale', 'Administration', 'Comptabilité et finance', 'Ressources humaines', 'Service commercial', 'Service informatique', 'Logistique', 'Production', 'Autres'], 'full' => true],
                ['name' => 'droits_acces', 'label' => 'Souhaitez-vous une gestion des droits d’accès selon les fonctions des utilisateurs ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À déterminer']],
            ],
        ],
        [
            'title' => '8. Hébergement, sécurité et sauvegarde',
            'fields' => [
                ['name' => 'hebergement_souhaite', 'label' => 'Où souhaitez-vous que vos solutions informatiques soient hébergées ?', 'type' => 'select', 'options' => ['Sur les équipements de l’entreprise', 'Sur un serveur dédié', 'Sur un serveur cloud', 'Sur une infrastructure hybride', 'Je souhaite être conseillé']],
                ['name' => 'dispositifs_securite', 'label' => 'Quels dispositifs de sécurité souhaitez-vous mettre en place ?', 'type' => 'checkboxes', 'options' => ['Gestion des identifiants et mots de passe', 'Authentification à plusieurs facteurs', 'Gestion des permissions', 'Pare-feu', 'Antivirus et protection des postes', 'Sauvegardes automatiques', 'Chiffrement des données', 'Surveillance des activités', 'Plan de continuité et de reprise après incident', 'Autres'], 'full' => true],
                ['name' => 'politique_securite', 'label' => 'Disposez-vous déjà d’une politique de sécurité informatique ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'En cours d’élaboration']],
            ],
        ],
        [
            'title' => '9. Installation, configuration et accompagnement',
            'fields' => [
                ['name' => 'services_complementaires', 'label' => 'Quels services complémentaires souhaitez-vous ?', 'type' => 'checkboxes', 'options' => ['Installation des logiciels', 'Configuration des systèmes', 'Paramétrage des utilisateurs', 'Configuration des serveurs', 'Configuration du réseau', 'Migration des données', 'Connexion avec les systèmes existants', 'Tests et validation', 'Documentation technique', 'Formation des utilisateurs', 'Assistance après installation', 'Maintenance préventive et corrective', 'Suivi technique régulier'], 'full' => true],
                ['name' => 'maintenance_solutions', 'label' => 'Souhaitez-vous que EUNITAL assure la maintenance des solutions ?', 'type' => 'select', 'options' => ['Oui, contrat mensuel', 'Oui, contrat annuel', 'Intervention à la demande', 'Non', 'À discuter']],
                ['name' => 'type_formation', 'label' => 'Quel type de formation souhaitez-vous ?', 'type' => 'checkboxes', 'options' => ['Formation des administrateurs', 'Formation des employés', 'Formation de la direction', 'Formation complète de tous les utilisateurs', 'Aucune formation nécessaire'], 'full' => true],
            ],
        ],
        [
            'title' => '10. Budget et délais',
            'fields' => [
                ['name' => 'budget_previsionnel', 'label' => 'Quel budget prévisionnel avez-vous prévu pour ce projet ?', 'type' => 'select', 'options' => ['Moins de 100 000 FCFA', '100 000 à 300 000 FCFA', '300 000 à 500 000 FCFA', '500 000 à 1 000 000 FCFA', 'Plus de 1 000 000 FCFA', 'Budget à déterminer après étude']],
                ['name' => 'demarrage', 'label' => 'Quand souhaitez-vous démarrer le projet ?', 'type' => 'select', 'options' => ['Immédiatement', 'Dans les deux semaines', 'Dans un mois', 'Dans deux à trois mois', 'Plus tard']],
                ['name' => 'date_limite', 'label' => 'Quelle est votre date limite de mise en service ?', 'type' => 'date'],
                ['name' => 'budget_valide', 'label' => 'Le budget est-il déjà validé ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'En attente de validation']],
            ],
        ],
        [
            'title' => '11. Informations complémentaires et suivi commercial',
            'fields' => [
                ['name' => 'documents_techniques', 'label' => 'Disposez-vous de documents techniques ou de références ?', 'type' => 'checkboxes', 'options' => ['Documentation des logiciels existants', 'Schéma de l’infrastructure informatique', 'Liste des équipements', 'Contrats de licence', 'Documentation des API', 'Captures d’écran', 'Aucun document disponible'], 'full' => true],
                ['name' => 'precisions_client', 'label' => 'Autres précisions ou exigences du client', 'type' => 'textarea', 'full' => true],
                ['name' => 'prospecteur', 'label' => 'Nom du prospecteur', 'type' => 'text'],
                ['name' => 'date_entretien', 'label' => 'Date de l’entretien', 'type' => 'date'],
                ['name' => 'statut_prospect', 'label' => 'Statut du prospect', 'type' => 'select', 'options' => ['Nouveau contact', 'Besoin identifié', 'Étude technique à réaliser', 'Proposition commerciale à préparer', 'Devis transmis', 'En négociation', 'Projet confirmé', 'À relancer', 'Sans suite']],
                ['name' => 'prochaine_action', 'label' => 'Prochaine action', 'type' => 'textarea', 'full' => true],
                ['name' => 'date_relance', 'label' => 'Date de relance', 'type' => 'date'],
            ],
        ],
        [
            'title' => '12. Analyse technique — réservé à EUNITAL',
            'fields' => [
                ['name' => 'analyse_elements_etude', 'label' => 'Éléments d’étude', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_observations', 'label' => 'Observations', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_solutions', 'label' => 'Solutions à intégrer', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_compatibilite', 'label' => 'Compatibilité des systèmes', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_architecture', 'label' => 'Architecture informatique', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_interconnexions', 'label' => 'Interconnexions et API', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_equipements', 'label' => 'Besoins en équipements', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_hebergement', 'label' => 'Besoins en hébergement', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_securite', 'label' => 'Sécurité et conformité', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_complexite', 'label' => 'Complexité technique', 'type' => 'text'],
                ['name' => 'analyse_ressources', 'label' => 'Ressources nécessaires', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_duree', 'label' => 'Durée estimée', 'type' => 'text'],
                ['name' => 'analyse_cout', 'label' => 'Coût technique estimé', 'type' => 'text'],
                ['name' => 'recommandations', 'label' => 'Recommandations techniques', 'type' => 'textarea', 'full' => true],
                ['name' => 'decision_technique', 'label' => 'Décision technique', 'type' => 'select', 'options' => ['Projet réalisable', 'Projet réalisable sous certaines conditions', 'Étude complémentaire nécessaire', 'Projet à réorienter']],
            ],
        ],
        [
            'title' => '13. Validation du brief',
            'fields' => [
                ['name' => 'nom_representant_client', 'label' => 'Nom du représentant du client', 'type' => 'text'],
                ['name' => 'signature_client', 'label' => 'Signature du client', 'type' => 'text'],
                ['name' => 'nom_representant_eunital', 'label' => 'Nom du représentant EUNITAL', 'type' => 'text'],
                ['name' => 'signature_eunital', 'label' => 'Signature du représentant EUNITAL', 'type' => 'text'],
                ['name' => 'date_validation', 'label' => 'Date', 'type' => 'date'],
            ],
        ],
    ],
];

$forms['developpement']['developpement_automatisation_des_processus_dentreprise'] = [
    'title' => 'Brief — Automatisation des processus d’entreprise',
    'description' => 'Brief de prospection et de cadrage — identifions les tâches répétitives, les flux de travail et les outils à connecter pour concevoir une automatisation adaptée à votre organisation.',
    'sections' => [
        [
            'title' => '1. Informations sur le client',
            'fields' => [
                ['name' => 'nom', 'label' => 'Nom et prénom', 'type' => 'text', 'required' => true],
                ['name' => 'entreprise', 'label' => 'Entreprise / organisation', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Adresse e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'telephone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true],
                ['name' => 'site', 'label' => 'Site web', 'type' => 'url', 'placeholder' => 'https://'],
                ['name' => 'fonction', 'label' => 'Fonction / service', 'type' => 'text'],
                ['name' => 'activite', 'label' => 'Présentez votre activité et votre organisation', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '2. Objectifs de l’automatisation',
            'fields' => [
                ['name' => 'objectifs', 'label' => 'Quels résultats souhaitez-vous obtenir ?', 'type' => 'checkboxes', 'options' => ['Réduire les tâches manuelles et répétitives', 'Gagner du temps et améliorer la productivité', 'Réduire les erreurs de saisie et de traitement', 'Fluidifier la circulation des informations', 'Améliorer le suivi et la traçabilité', 'Accélérer les délais de traitement', 'Connecter les logiciels et outils existants', 'Améliorer l’expérience client ou collaborateur', 'Faciliter le pilotage et le reporting', 'Accompagner la croissance de l’activité'], 'full' => true],
                ['name' => 'resultat_attendu', 'label' => 'Résultat le plus important attendu', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '3. Processus à automatiser',
            'fields' => [
                ['name' => 'processus', 'label' => 'Sélectionnez les activités ou services concernés', 'type' => 'checkboxes', 'options' => ['Gestion commerciale et CRM', 'Prospection et suivi des prospects', 'Devis, commandes et facturation', 'Comptabilité et finance', 'Ressources humaines et recrutement', 'Gestion des stocks et achats', 'Service client et support', 'Marketing et communication', 'Gestion documentaire et archivage', 'Production et opérations', 'Reporting et tableaux de bord', 'Gestion de projets et tâches', 'Réservations et planification', 'Autre processus'], 'full' => true],
                ['name' => 'processus_prioritaire', 'label' => 'Processus prioritaire et service responsable', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '4. Fonctionnement actuel du processus',
            'fields' => [
                ['name' => 'etapes_actuelles', 'label' => 'Étapes actuelles du processus', 'type' => 'textarea', 'full' => true, 'placeholder' => 'Qui fait quoi ? Dans quel ordre ? Quels outils sont utilisés ?'],
                ['name' => 'frequence', 'label' => 'Fréquence d’exécution', 'type' => 'select', 'options' => ['Plusieurs fois par jour', 'Quotidienne', 'Hebdomadaire', 'Mensuelle', 'À la demande / irrégulière', 'Saisonnière']],
                ['name' => 'volume', 'label' => 'Volume traité', 'type' => 'text', 'placeholder' => 'Ex. : 100 dossiers par semaine'],
                ['name' => 'personnes', 'label' => 'Nombre de personnes impliquées', 'type' => 'text'],
                ['name' => 'temps', 'label' => 'Temps moyen consacré', 'type' => 'text', 'placeholder' => 'Ex. : 15 min par dossier'],
                ['name' => 'taches_repetitives', 'label' => 'Étapes répétitives ou chronophages', 'type' => 'textarea', 'full' => true],
                ['name' => 'difficultes', 'label' => 'Difficultés, erreurs ou retards rencontrés', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '5. Déclencheurs, règles et scénarios',
            'fields' => [
                ['name' => 'declencheur', 'label' => 'Événement déclencheur', 'type' => 'select', 'options' => ['Réception d’un e-mail', 'Soumission d’un formulaire', 'Création / modification d’un enregistrement', 'Réception d’un paiement', 'Date ou échéance planifiée', 'Action d’un utilisateur', 'Événement provenant d’une API', 'Autre / à définir']],
                ['name' => 'execution', 'label' => 'Fréquence d’exécution souhaitée', 'type' => 'select', 'options' => ['En temps réel', 'Toutes les quelques minutes', 'À horaires fixes', 'Une fois par jour', 'À la demande']],
                ['name' => 'regles', 'label' => 'Règles métier (conditions, seuils, validations, priorités…)', 'type' => 'textarea', 'full' => true],
                ['name' => 'exceptions', 'label' => 'Scénarios particuliers et exceptions à gérer', 'type' => 'textarea', 'full' => true],
                ['name' => 'validation_humaine', 'label' => 'Actions nécessitant une validation humaine', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '6. Outils, logiciels et intégrations',
            'fields' => [
                ['name' => 'outils', 'label' => 'Quels outils doivent échanger des informations ?', 'type' => 'checkboxes', 'options' => ['CRM', 'ERP / logiciel de gestion', 'Comptabilité / facturation', 'Messagerie e-mail', 'Excel / Google Sheets / tableurs', 'Formulaires en ligne', 'Slack / Teams / communication', 'Gestion de projet / tâches', 'E-commerce / paiement', 'Base de données', 'API / webhooks', 'Logiciel métier spécifique'], 'full' => true],
                ['name' => 'logiciels_details', 'label' => 'Noms des logiciels, éditeurs et versions', 'type' => 'textarea', 'full' => true],
                ['name' => 'api', 'label' => 'Les outils disposent-ils d’API ?', 'type' => 'select', 'options' => ['Oui, API documentées', 'Certains seulement', 'Non', 'Je ne sais pas']],
                ['name' => 'conservation_outils', 'label' => 'Les outils doivent-ils être conservés ?', 'type' => 'select', 'options' => ['Oui, tous', 'Certains peuvent être remplacés', 'Ouvert à de nouvelles solutions']],
                ['name' => 'echanges', 'label' => 'Échanges de données souhaités entre les outils', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '7. Données et documents manipulés',
            'fields' => [
                ['name' => 'types_donnees', 'label' => 'Types de données concernés', 'type' => 'text', 'placeholder' => 'Clients, commandes, factures, RH…'],
                ['name' => 'formats', 'label' => 'Formats de fichiers', 'type' => 'text', 'placeholder' => 'PDF, CSV, XLSX, XML…'],
                ['name' => 'volume_donnees', 'label' => 'Volume approximatif', 'type' => 'text'],
                ['name' => 'donnees_personnelles', 'label' => 'Données personnelles concernées ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À confirmer']],
                ['name' => 'stockage_donnees', 'label' => 'Où les données sont-elles stockées actuellement ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'qualite_donnees', 'label' => 'Règles de qualité, conservation ou transformation des données', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '8. Type d’automatisation et niveau d’intelligence',
            'fields' => [
                ['name' => 'types_automatisation', 'label' => 'Quel type d’automatisation envisagez-vous ?', 'type' => 'checkboxes', 'options' => ['Automatisation par règles et conditions', 'Connexion entre applications', 'Workflows et circuits de validation', 'Automatisation d’actions sur interfaces (RPA)', 'Lecture et traitement de documents', 'Intelligence artificielle / assistance intelligente', 'Notifications et alertes automatiques', 'Rapports et tableaux de bord automatiques'], 'full' => true],
                ['name' => 'outils_envisages', 'label' => 'Outils envisagés', 'type' => 'text', 'placeholder' => 'Make, n8n, Zapier, Power Automate…'],
                ['name' => 'deploiement', 'label' => 'Préférence de déploiement', 'type' => 'select', 'options' => ['Cloud / service hébergé', 'Sur nos propres serveurs', 'Hybride', 'Sans préférence']],
                ['name' => 'solution_envisagee', 'label' => 'Précisions sur la solution ou les technologies envisagées', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '9. Contrôle humain, sécurité et fiabilité',
            'fields' => [
                ['name' => 'autonomie', 'label' => 'Degré d’autonomie souhaité', 'type' => 'select', 'options' => ['Assistance : chaque action est validée', 'Automatique avec validation des étapes sensibles', 'Automatique avec supervision', 'Autonomie complète sur périmètre défini', 'À déterminer']],
                ['name' => 'journalisation', 'label' => 'Journalisation des actions', 'type' => 'select', 'options' => ['Oui, détaillée', 'Oui, actions importantes', 'Non', 'À définir']],
                ['name' => 'erreurs', 'label' => 'Gestion des erreurs', 'type' => 'select', 'options' => ['Alerte simple', 'Nouvelle tentative automatique', 'Mise en attente et intervention humaine', 'Procédure personnalisée', 'À conseiller']],
                ['name' => 'disponibilite', 'label' => 'Disponibilité attendue', 'type' => 'select', 'options' => ['Heures ouvrées', 'Étendue', '24 h/24 et 7 j/7', 'Selon les besoins']],
                ['name' => 'securite', 'label' => 'Contraintes de sécurité, confidentialité et conformité (ex. RGPD)', 'type' => 'textarea', 'full' => true],
                ['name' => 'limites', 'label' => 'Actions interdites, limites ou cas nécessitant une intervention humaine', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '10. Résultats attendus et indicateurs',
            'fields' => [
                ['name' => 'temps_actuel', 'label' => 'Temps actuellement consacré', 'type' => 'text', 'placeholder' => 'Heures par jour / semaine / mois'],
                ['name' => 'gain_temps', 'label' => 'Gain de temps espéré', 'type' => 'text', 'placeholder' => 'Ex. : réduire de 50 %'],
                ['name' => 'reduction_erreurs', 'label' => 'Réduction d’erreurs attendue', 'type' => 'text'],
                ['name' => 'indicateur', 'label' => 'Indicateur principal de réussite', 'type' => 'select', 'options' => ['Temps de traitement', 'Coût opérationnel', 'Taux d’erreur', 'Volume traité', 'Satisfaction client / équipe', 'Traçabilité / conformité', 'Autre']],
                ['name' => 'criteres', 'label' => 'Objectifs chiffrés ou critères d’acceptation', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '11. Déploiement, accompagnement et maintenance',
            'fields' => [
                ['name' => 'mode_deploiement', 'label' => 'Mode de déploiement', 'type' => 'select', 'options' => ['Prototype / preuve de concept', 'Déploiement pilote sur un service', 'Déploiement progressif', 'Déploiement global', 'À définir']],
                ['name' => 'formation', 'label' => 'Formation des équipes nécessaire ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À évaluer']],
                ['name' => 'documentation', 'label' => 'Documentation souhaitée', 'type' => 'select', 'options' => ['Guide utilisateur', 'Documentation technique', 'Les deux', 'Non nécessaire']],
                ['name' => 'maintenance', 'label' => 'Maintenance après livraison', 'type' => 'select', 'options' => ['Intervention ponctuelle', 'Maintenance régulière', 'Support et supervision', 'À définir']],
                ['name' => 'participants', 'label' => 'Personnes participant aux tests et à la validation', 'type' => 'textarea', 'full' => true],
                ['name' => 'changement', 'label' => 'Contraintes de conduite du changement ou d’adoption', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '12. Planning, budget et validation',
            'fields' => [
                ['name' => 'date_demarrage', 'label' => 'Date souhaitée de démarrage', 'type' => 'date'],
                ['name' => 'date_echeance', 'label' => 'Échéance souhaitée', 'type' => 'date'],
                ['name' => 'budget', 'label' => 'Budget estimé', 'type' => 'select', 'options' => ['Moins de 500 €', '500 à 1 500 €', '1 500 à 3 000 €', '3 000 à 5 000 €', '5 000 à 10 000 €', 'Plus de 10 000 €', 'À définir']],
                ['name' => 'urgence', 'label' => 'Urgence', 'type' => 'select', 'options' => ['Normale', 'Prioritaire', 'Urgente', 'Critique']],
                ['name' => 'priorite', 'label' => 'Processus à traiter en premier / périmètre prioritaire', 'type' => 'textarea', 'full' => true],
                ['name' => 'remarques', 'label' => 'Questions, remarques ou informations complémentaires', 'type' => 'textarea', 'full' => true],
                ['name' => 'origine_contact', 'label' => 'Comment avez-vous connu EUNITAL ?', 'type' => 'select', 'options' => ['Recommandation', 'Réseaux sociaux', 'Recherche Internet', 'Ancien client', 'Autre']],
                ['name' => 'accord', 'label' => 'Je confirme que les informations fournies sont exactes et j’accepte d’être recontacté(e) au sujet de cette demande.', 'type' => 'checkboxes', 'required' => true, 'options' => ['on'], 'full' => true],
            ],
        ],
    ],
];

$forms['developpement']['developpement_developpement_de_logiciels_sur_mesure'] = [
    'title' => 'Brief — Développement de logiciels sur mesure',
    'description' => 'Décrivez le logiciel à concevoir, les besoins de votre organisation, les utilisateurs concernés et vos contraintes afin que l’équipe Développement puisse cadrer le projet.',
    'sections' => [
        [
            'title' => '1. Informations sur le client',
            'fields' => [
                ['name' => 'organisation', 'label' => 'Nom de l’entreprise ou de l’organisation', 'type' => 'text', 'required' => true],
                ['name' => 'representant', 'label' => 'Nom et prénom du représentant', 'type' => 'text', 'required' => true],
                ['name' => 'fonction_representant', 'label' => 'Fonction du représentant', 'type' => 'text'],
                ['name' => 'secteur', 'label' => 'Secteur d’activité', 'type' => 'text'],
                ['name' => 'telephone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => true],
                ['name' => 'email', 'label' => 'Adresse e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'localisation', 'label' => 'Ville et pays', 'type' => 'text'],
                ['name' => 'site', 'label' => 'Site web', 'type' => 'url', 'placeholder' => 'https://'],
            ],
        ],
        [
            'title' => '2. Identification du besoin',
            'fields' => [
                ['name' => 'besoin_principal', 'label' => 'Quel est votre besoin principal ?', 'type' => 'checkboxes', 'required' => true, 'options' => ['Créer un nouveau logiciel métier', 'Remplacer un logiciel existant', 'Créer une application web', 'Créer une application mobile', 'Créer un logiciel de bureau', 'Créer une plateforme SaaS', 'Créer un ERP ou un outil de gestion intégré', 'Créer un CRM ou un outil de gestion client', 'Automatiser des processus de travail', 'Moderniser ou numériser une activité', 'Autre'], 'full' => true],
                ['name' => 'probleme_actuel', 'label' => 'Quel problème métier le logiciel doit-il résoudre ?', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'processus_actuel', 'label' => 'Comment ce besoin est-il traité actuellement ? Décrivez les étapes et les difficultés rencontrées.', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'objectifs_projet', 'label' => 'Quels résultats souhaitez-vous obtenir ?', 'type' => 'checkboxes', 'options' => ['Améliorer la productivité', 'Réduire les tâches manuelles', 'Centraliser les informations', 'Améliorer le suivi des activités', 'Réduire les erreurs', 'Améliorer le service aux clients', 'Faciliter la collaboration', 'Créer une nouvelle activité numérique', 'Autre'], 'full' => true],
            ],
        ],
        [
            'title' => '3. Présentation du logiciel à concevoir',
            'fields' => [
                ['name' => 'nom_projet', 'label' => 'Nom du projet ou du futur logiciel', 'type' => 'text', 'required' => true],
                ['name' => 'type_logiciel', 'label' => 'Quel type de logiciel souhaitez-vous développer ?', 'type' => 'checkboxes', 'required' => true, 'options' => ['Application web', 'Application mobile Android', 'Application mobile iOS', 'Application web et mobile', 'Logiciel de bureau', 'Logiciel de gestion / métier', 'ERP (gestion intégrée)', 'CRM (gestion de la relation client)', 'Plateforme SaaS', 'API / service backend', 'Autre'], 'full' => true],
                ['name' => 'description_logiciel', 'label' => 'Décrivez le logiciel et son fonctionnement attendu', 'type' => 'textarea', 'required' => true, 'full' => true],
                ['name' => 'fonctionnalites_logiciel', 'label' => 'Quelles sont les fonctionnalités prioritaires ?', 'type' => 'textarea', 'required' => true, 'full' => true, 'placeholder' => 'Décrivez les fonctions, les utilisateurs concernés et le résultat attendu.'],
                ['name' => 'references_logiciel', 'label' => 'Exemples de logiciels, maquettes ou interfaces de référence', 'type' => 'textarea', 'full' => true],
                ['name' => 'ia_logiciel', 'label' => 'Souhaitez-vous intégrer des fonctions d’intelligence artificielle ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À étudier avec EUNITAL']],
                ['name' => 'usages_ia', 'label' => 'Si oui, pour quelles activités ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '4. Utilisateurs et gestion des accès',
            'fields' => [
                ['name' => 'nombre_utilisateurs', 'label' => 'Combien de personnes utiliseront le logiciel ?', 'type' => 'select', 'options' => ['1 à 5 personnes', '6 à 20 personnes', '21 à 50 personnes', '51 à 100 personnes', 'Plus de 100 personnes', 'À déterminer']],
                ['name' => 'profils_utilisateurs', 'label' => 'Quels profils ou services utiliseront le logiciel ?', 'type' => 'checkboxes', 'options' => ['Direction générale', 'Administration', 'Comptabilité et finance', 'Ressources humaines', 'Service commercial', 'Service informatique', 'Logistique', 'Production', 'Clients / grand public', 'Partenaires / fournisseurs', 'Autres'], 'full' => true],
                ['name' => 'gestion_acces', 'label' => 'Faut-il gérer des droits d’accès différents selon les utilisateurs ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À déterminer']],
                ['name' => 'roles_utilisateurs', 'label' => 'Décrivez les rôles, permissions ou parcours utilisateurs à prévoir', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '5. Environnement informatique et données',
            'fields' => [
                ['name' => 'infrastructure_existante', 'label' => 'Disposez-vous déjà d’une infrastructure informatique ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'Partiellement']],
                ['name' => 'equipements_existants', 'label' => 'Quels équipements utilisez-vous ?', 'type' => 'checkboxes', 'options' => ['Ordinateurs de bureau', 'Ordinateurs portables', 'Serveurs physiques', 'Serveurs virtuels', 'Équipements réseau', 'Smartphones et tablettes', 'Autres'], 'full' => true],
                ['name' => 'systemes_exploitation', 'label' => 'Quels systèmes d’exploitation ou appareils faut-il prendre en charge ?', 'type' => 'checkboxes', 'options' => ['Windows', 'Linux', 'macOS', 'Android', 'iOS', 'Navigateurs web', 'Autres'], 'full' => true],
                ['name' => 'outils_existants', 'label' => 'Quels logiciels, outils ou fichiers utilisez-vous actuellement ?', 'type' => 'textarea', 'full' => true],
                ['name' => 'donnees_a_migrer', 'label' => 'Des données existantes doivent-elles être reprises dans le nouveau logiciel ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À déterminer']],
                ['name' => 'description_donnees', 'label' => 'Décrivez les données, leur volume et leur format si vous les connaissez', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '6. Interconnexions et automatisation',
            'fields' => [
                ['name' => 'connexion_outils', 'label' => 'Le futur logiciel doit-il communiquer avec d’autres outils ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À déterminer']],
                ['name' => 'systemes_a_connecter', 'label' => 'Quels systèmes ou services devront être connectés ?', 'type' => 'checkboxes', 'options' => ['Site web et logiciel de gestion', 'Application mobile et serveur', 'Comptabilité et gestion commerciale', 'CRM et plateforme de vente', 'ERP et base de données', 'Système de paiement', 'Services cloud', 'E-mail ou SMS', 'Autres'], 'full' => true],
                ['name' => 'connexions_souhaitees', 'label' => 'Décrivez les échanges de données ou les connexions souhaités', 'type' => 'textarea', 'full' => true],
                ['name' => 'activites_a_automatiser', 'label' => 'Quelles activités souhaitez-vous automatiser ?', 'type' => 'checkboxes', 'options' => ['Factures', 'Commandes', 'Paiements', 'Stocks', 'Gestion des clients', 'Rendez-vous', 'Gestion des employés', 'Envoi d’e-mails', 'Rapports', 'Synchronisation des données', 'Notifications et alertes', 'Traitement de documents', 'Autres'], 'full' => true],
                ['name' => 'taches_manuelles', 'label' => 'Quelles tâches manuelles vous font actuellement perdre du temps ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '7. Hébergement, sécurité et sauvegarde',
            'fields' => [
                ['name' => 'hebergement_souhaite', 'label' => 'Où souhaitez-vous héberger le logiciel ?', 'type' => 'select', 'options' => ['Sur les équipements de l’entreprise', 'Sur un serveur dédié', 'Sur un serveur cloud', 'Sur une infrastructure hybride', 'Je souhaite être conseillé']],
                ['name' => 'dispositifs_securite', 'label' => 'Quels dispositifs de sécurité souhaitez-vous ?', 'type' => 'checkboxes', 'options' => ['Gestion des identifiants et mots de passe', 'Authentification à plusieurs facteurs', 'Gestion des permissions', 'Pare-feu', 'Protection des postes', 'Sauvegardes automatiques', 'Chiffrement des données', 'Journalisation et surveillance', 'Plan de continuité et de reprise', 'Autres'], 'full' => true],
                ['name' => 'donnees_personnelles', 'label' => 'Le logiciel traitera-t-il des données personnelles ou sensibles ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'À confirmer']],
                ['name' => 'politique_securite', 'label' => 'Disposez-vous déjà d’une politique de sécurité informatique ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'En cours d’élaboration']],
                ['name' => 'contraintes_conformite', 'label' => 'Exigences réglementaires, confidentialité ou autres contraintes de sécurité', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '8. Réalisation, mise en service et accompagnement',
            'fields' => [
                ['name' => 'services_complementaires', 'label' => 'Quels services souhaitez-vous inclure ?', 'type' => 'checkboxes', 'options' => ['Conception des maquettes et de l’interface', 'Développement du logiciel', 'Création des bases de données', 'Connexion aux systèmes existants', 'Migration des données', 'Tests et validation', 'Installation et mise en service', 'Documentation technique', 'Formation des administrateurs', 'Formation des utilisateurs', 'Assistance après livraison', 'Maintenance préventive et corrective'], 'full' => true],
                ['name' => 'maintenance_solutions', 'label' => 'Quel accompagnement après la mise en service souhaitez-vous ?', 'type' => 'select', 'options' => ['Contrat mensuel', 'Contrat annuel', 'Intervention à la demande', 'Pas de maintenance', 'À discuter']],
                ['name' => 'type_formation', 'label' => 'Quel type de formation souhaitez-vous ?', 'type' => 'checkboxes', 'options' => ['Formation des administrateurs', 'Formation des employés', 'Formation de la direction', 'Formation de tous les utilisateurs', 'Aucune formation nécessaire'], 'full' => true],
            ],
        ],
        [
            'title' => '9. Budget et délais',
            'fields' => [
                ['name' => 'budget_previsionnel', 'label' => 'Quel budget prévisionnel avez-vous prévu ?', 'type' => 'select', 'options' => ['Moins de 100 000 FCFA', '100 000 à 300 000 FCFA', '300 000 à 500 000 FCFA', '500 000 à 1 000 000 FCFA', 'Plus de 1 000 000 FCFA', 'À déterminer après étude']],
                ['name' => 'budget_valide', 'label' => 'Le budget est-il déjà validé ?', 'type' => 'select', 'options' => ['Oui', 'Non', 'En attente de validation']],
                ['name' => 'demarrage', 'label' => 'Quand souhaitez-vous démarrer le projet ?', 'type' => 'select', 'options' => ['Immédiatement', 'Dans les deux semaines', 'Dans un mois', 'Dans deux à trois mois', 'Plus tard']],
                ['name' => 'date_limite', 'label' => 'Quelle est votre date limite de mise en service ?', 'type' => 'date'],
                ['name' => 'fonctionnalites_mvp', 'label' => 'Quelles fonctionnalités sont indispensables pour une première version (MVP) ?', 'type' => 'textarea', 'full' => true],
            ],
        ],
        [
            'title' => '10. Documents et informations disponibles',
            'fields' => [
                ['name' => 'documents_techniques', 'label' => 'Quels documents ou éléments pouvez-vous fournir ?', 'type' => 'checkboxes', 'options' => ['Description des processus métier', 'Exemples de fichiers et données', 'Documentation des logiciels existants', 'Schéma de l’infrastructure', 'Maquettes ou captures d’écran', 'Documentation des API', 'Aucun document disponible', 'Autres'], 'full' => true],
                ['name' => 'precisions_client', 'label' => 'Autres précisions ou exigences concernant le logiciel', 'type' => 'textarea', 'full' => true],
                ['name' => 'note_securite', 'label' => 'Sécurité des accès', 'type' => 'textarea', 'default' => 'Ne transmettez aucun mot de passe, code OTP, clé privée, token ou secret dans ce formulaire.'],
            ],
        ],
        [
            'title' => '11. Informations de prospection EUNITAL',
            'fields' => [
                ['name' => 'prospecteur', 'label' => 'Nom du prospecteur', 'type' => 'text'],
                ['name' => 'date_entretien', 'label' => 'Date de l’entretien', 'type' => 'date'],
                ['name' => 'statut_prospect', 'label' => 'Statut du prospect', 'type' => 'select', 'options' => ['Nouveau contact', 'Besoin identifié', 'Étude technique à réaliser', 'Proposition commerciale à préparer', 'Devis transmis', 'En négociation', 'Projet confirmé', 'À relancer', 'Sans suite']],
                ['name' => 'prochaine_action', 'label' => 'Prochaine action', 'type' => 'textarea', 'full' => true],
                ['name' => 'date_relance', 'label' => 'Date de relance', 'type' => 'date'],
            ],
        ],
        [
            'title' => '12. Analyse technique — réservé à EUNITAL',
            'fields' => [
                ['name' => 'analyse_elements_etude', 'label' => 'Besoins métier et périmètre à étudier', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_observations', 'label' => 'Observations et risques identifiés', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_solutions', 'label' => 'Solution logicielle et fonctionnalités à prévoir', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_compatibilite', 'label' => 'Compatibilité et intégrations à étudier', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_architecture', 'label' => 'Architecture technique envisagée', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_interconnexions', 'label' => 'Interconnexions, API et échanges de données', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_hebergement', 'label' => 'Besoins en hébergement et infrastructure', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_securite', 'label' => 'Sécurité, données et conformité', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_complexite', 'label' => 'Complexité technique', 'type' => 'text'],
                ['name' => 'analyse_ressources', 'label' => 'Ressources nécessaires', 'type' => 'textarea', 'full' => true],
                ['name' => 'analyse_duree', 'label' => 'Durée estimée', 'type' => 'text'],
                ['name' => 'analyse_cout', 'label' => 'Coût technique estimé', 'type' => 'text'],
                ['name' => 'recommandations', 'label' => 'Recommandations techniques', 'type' => 'textarea', 'full' => true],
                ['name' => 'decision_technique', 'label' => 'Décision technique', 'type' => 'select', 'options' => ['Projet réalisable', 'Projet réalisable sous certaines conditions', 'Étude complémentaire nécessaire', 'Projet à réorienter']],
            ],
        ],
        [
            'title' => '13. Validation du brief',
            'fields' => [
                ['name' => 'nom_representant_client', 'label' => 'Nom du représentant du client', 'type' => 'text'],
                ['name' => 'signature_client', 'label' => 'Signature du client', 'type' => 'text'],
                ['name' => 'nom_representant_eunital', 'label' => 'Nom du représentant EUNITAL', 'type' => 'text'],
                ['name' => 'signature_eunital', 'label' => 'Signature du représentant EUNITAL', 'type' => 'text'],
                ['name' => 'date_validation', 'label' => 'Date de validation', 'type' => 'date'],
            ],
        ],
    ],
];

return $forms;
