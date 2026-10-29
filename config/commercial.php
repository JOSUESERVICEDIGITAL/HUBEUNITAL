<?php

$f = fn ($label, $type = 'text', $required = false, $extra = []) => compact('label', 'type', 'required') + $extra;
$s = fn ($label, $options) => $f($label, 'select', true, compact('options'));
$r = fn ($label, $table, $required = true) => $f($label, 'relation', $required, ['table' => $table, 'display' => 'name']);
$employee = $r('Agent (dossier RH)', 'rh_employees');
$client = $r('Client / entreprise', 'com_clients');
$notes = $f('Observations', 'textarea');
$status = $s('Statut', ['planned' => 'Prévu', 'in_progress' => 'En cours', 'done' => 'Terminé', 'cancelled' => 'Annulé']);
$m = fn ($label, $group, $fields, $area = 'sales', $owner = true) => compact('label', 'group', 'fields', 'area', 'owner');

return [
    'groups' => ['pilot' => 'Pilotage commercial', 'clients' => 'Prospection et clientèle', 'sales' => 'Ventes et contrats', 'coordination' => 'Suivi et coordination', 'field' => 'Intelligence terrain'],
    'stages' => ['reported' => 'Signalé', 'verifying' => 'À vérifier', 'validated' => 'Validé', 'study' => 'En étude', 'proposed' => 'Projet proposé', 'development' => 'En développement', 'tested' => 'Testé', 'deployed' => 'Déployé'],
    'modules' => [
        'targets' => $m('Objectifs et performances', 'pilot', [
            'name' => $f('Objectif', required: true), 'employee_id' => $employee, 'period' => $f('Période (AAAA-MM)', required: true), 'amount' => $f('Objectif de ventes (FCFA)', 'decimal', true), 'visits_target' => $f('Objectif de visites', 'number', true), 'notes' => $notes,
        ]),
        'campaigns' => $m('Campagnes de prospection', 'clients', [
            'name' => $f('Campagne', required: true), 'employee_id' => $employee, 'sector' => $f('Secteur'), 'location' => $f('Zone de prospection'), 'starts_on' => $f('Début', 'date', true), 'ends_on' => $f('Fin', 'date'), 'status' => $status, 'notes' => $notes,
        ]),
        'prospects' => $m('Prospects', 'clients', [
            'name' => $f('Organisation / prospect', required: true), 'employee_id' => $employee, 'campaign_id' => $r('Campagne', 'com_campaigns', false), 'sector' => $f('Secteur'), 'location' => $f('Localité'), 'email' => $f('Email professionnel', 'email'), 'phone' => $f('Téléphone professionnel'),
            'status' => $s('Étape', ['new' => 'À contacter', 'contacted' => 'Contacté', 'qualified' => 'Qualifié', 'converted' => 'Converti en client', 'lost' => 'Non retenu']), 'notes' => $notes,
        ]),
        'clients' => $m('Clients et entreprises', 'clients', [
            'name' => $f('Raison sociale / client', required: true), 'employee_id' => $employee, 'email' => $f('Email', 'email'), 'phone' => $f('Téléphone'), 'location' => $f('Localité'), 'status' => $s('Statut', ['active' => 'Actif', 'archived' => 'Archivé']), 'notes' => $notes,
        ]),
        'contacts' => $m('Contacts et interlocuteurs', 'clients', [
            'name' => $f('Nom de l’interlocuteur', required: true), 'employee_id' => $employee, 'client_id' => $client, 'position' => $f('Fonction'), 'email' => $f('Email professionnel', 'email'), 'phone' => $f('Téléphone professionnel'), 'notes' => $notes,
        ]),
        'interactions' => $m('Échanges et comptes rendus', 'coordination', [
            'name' => $f('Objet de l’échange', required: true), 'employee_id' => $employee, 'client_id' => $client, 'date' => $f('Date', 'date', true), 'type' => $s('Canal', ['call' => 'Appel', 'email' => 'Email', 'meeting' => 'Réunion', 'other' => 'Autre']), 'summary' => $f('Compte rendu et prochaine action', 'textarea', true),
        ]),
        'appointments' => $m('Rendez-vous commerciaux', 'clients', [
            'name' => $f('Objet', required: true), 'employee_id' => $employee, 'client_id' => $client, 'starts_at' => $f('Rendez-vous', 'datetime-local', true), 'location' => $f('Lieu'), 'status' => $status, 'notes' => $notes,
        ]),
        'visits' => $m('Visites et missions terrain', 'clients', [
            'name' => $f('Mission', required: true), 'employee_id' => $employee, 'client_id' => $r('Client', 'com_clients', false), 'date' => $f('Date', 'date', true), 'location' => $f('Zone / établissements', required: true), 'purpose' => $s('Mission', ['sales' => 'Prospection commerciale', 'observation' => 'Observation des besoins']), 'status' => $status, 'findings' => $f('Besoins identifiés et compte rendu', 'textarea'), 'notes' => $notes,
        ]),
        'catalog' => $m('Catalogue des offres et tarifs', 'sales', [
            'name' => $f('Offre / prestation', required: true), 'chamber_id' => $r('Chambre technique', 'chambers'), 'amount' => $f('Tarif indicatif (FCFA)', 'decimal', true), 'status' => $s('Disponibilité', ['active' => 'Disponible', 'archived' => 'Archivée']), 'description' => $f('Périmètre et conditions', 'textarea'),
        ], owner: false),
        'opportunities' => $m('Opportunités commerciales', 'sales', [
            'name' => $f('Opportunité', required: true), 'employee_id' => $employee, 'client_id' => $client, 'amount' => $f('Valeur estimée (FCFA)', 'decimal', true), 'due_date' => $f('Clôture prévue', 'date'), 'status' => $s('Étape', ['qualified' => 'Qualifiée', 'proposal' => 'Proposition', 'negotiation' => 'Négociation', 'won' => 'Gagnée', 'lost' => 'Perdue']), 'notes' => $notes,
        ]),
        'quotes' => $m('Devis et propositions', 'sales', [
            'name' => $f('Référence du devis', required: true), 'employee_id' => $employee, 'client_id' => $client, 'opportunity_id' => $r('Opportunité', 'com_opportunities', false), 'catalog_id' => $r('Offre', 'com_catalog', false), 'chamber_id' => $r('Chambre technique consultée', 'chambers'), 'amount' => $f('Montant proposé (FCFA)', 'decimal', true), 'valid_until' => $f('Validité', 'date', true), 'status' => $s('Statut', ['draft' => 'Préparation', 'sent' => 'Transmis', 'accepted' => 'Accepté', 'rejected' => 'Refusé']), 'notes' => $notes,
        ]),
        'orders' => $m('Commandes et ventes', 'sales', [
            'name' => $f('Référence de la commande', required: true), 'employee_id' => $employee, 'client_id' => $client, 'quote_id' => $r('Devis accepté', 'com_quotes'), 'date' => $f('Date de vente', 'date', true), 'status' => $s('Statut', ['confirmed' => 'Confirmée', 'cancelled' => 'Annulée']), 'notes' => $notes,
        ]),
        'contracts' => $m('Contrats commerciaux', 'sales', [
            'name' => $f('Référence de l’engagement', required: true), 'employee_id' => $employee, 'client_id' => $client, 'order_id' => $r('Commande', 'com_orders'), 'starts_on' => $f('Début', 'date', true), 'ends_on' => $f('Fin / renouvellement', 'date'), 'status' => $s('Statut', ['draft' => 'Préparation', 'review' => 'À vérifier par Juridique', 'signed' => 'Signé', 'ended' => 'Terminé']), 'terms' => $f('Périmètre et clauses à vérifier', 'textarea', true), 'notes' => $notes,
        ]),
        'portfolios' => $m('Portefeuille des commerciaux', 'coordination', [
            'name' => $f('Affectation / zone', required: true), 'employee_id' => $employee, 'client_id' => $client, 'location' => $f('Zone'), 'status' => $s('Statut', ['active' => 'Actif', 'archived' => 'Archivé']), 'notes' => $notes,
        ]),
        'projects' => $m('Suivi des projets clients', 'coordination', [
            'name' => $f('Projet client', required: true), 'employee_id' => $employee, 'client_id' => $client, 'order_id' => $r('Commande', 'com_orders'), 'chamber_id' => $r('Chambre réalisatrice', 'chambers'), 'due_date' => $f('Échéance', 'date'), 'status' => $status, 'notes' => $notes,
        ]),
        'followups' => $m('Relances et fidélisation', 'coordination', [
            'name' => $f('Objet de la relance', required: true), 'employee_id' => $employee, 'client_id' => $client, 'due_date' => $f('Date de relance', 'date', true), 'status' => $status, 'notes' => $notes,
        ]),
        'invoices' => $m('Suivi des factures et paiements', 'coordination', [
            'name' => $f('Référence Finance', required: true), 'employee_id' => $employee, 'client_id' => $client, 'order_id' => $r('Commande', 'com_orders'), 'issued_on' => $f('Date d’émission', 'date', true), 'due_date' => $f('Échéance', 'date', true), 'amount' => $f('Montant facturé (FCFA)', 'decimal', true), 'status' => $s('Statut', ['issued' => 'Émise', 'cancelled' => 'Annulée']), 'notes' => $notes,
        ], area: 'finance'),
        'commissions' => $m('Commissions et primes commerciales', 'coordination', [
            'name' => $f('Libellé', required: true), 'employee_id' => $employee, 'order_id' => $r('Vente confirmée', 'com_orders'), 'period' => $f('Période de paie (AAAA-MM)', required: true), 'rate' => $f('Taux de commission (%)', 'decimal', true), 'notes' => $notes,
        ], area: 'commission'),
        'reports' => $m('Rapports d’activité', 'coordination', [
            'name' => $f('Rapport', required: true), 'employee_id' => $employee, 'date' => $f('Date', 'date', true), 'summary' => $f('Activités, résultats et difficultés', 'textarea', true), 'notes' => $notes,
        ]),
        'clusters' => $m('Problèmes récurrents', 'field', [
            'name' => $f('Besoin commun', required: true), 'sector' => $f('Secteur', required: true), 'summary' => $f('Synthèse et critères de regroupement', 'textarea', true),
        ], area: 'innovation', owner: false),
        'problems' => $m('Signalements de problèmes', 'field', [
            'name' => $f('Problème observé', required: true), 'employee_id' => $employee, 'sector' => $f('Secteur', required: true), 'location' => $f('Commune / localité approximative', required: true), 'latitude' => $f('Latitude approximative (facultative)', 'decimal'), 'longitude' => $f('Longitude approximative (facultative)', 'decimal'),
            'description' => $f('Difficulté rencontrée', 'textarea', true), 'frequency' => $s('Fréquence', ['once' => 'Ponctuelle', 'weekly' => 'Hebdomadaire', 'daily' => 'Quotidienne', 'recurring' => 'Récurrente']), 'impact' => $f('Conséquences et besoin exprimé', 'textarea', true), 'source_method' => $s('Source', ['observation' => 'Observation ouverte', 'voluntary' => 'Témoignages volontaires', 'public' => 'Information publique']), 'source_summary' => $f('Source ou référence publique — sans identité privée', 'textarea'), 'priority' => $s('Priorité proposée', ['low' => 'Faible', 'medium' => 'Moyenne', 'high' => 'Élevée', 'critical' => 'Critique']),
        ], area: 'field'),
        'observations' => $m('Rapports d’observation', 'field', [
            'name' => $f('Rapport d’observation', required: true), 'employee_id' => $employee, 'problem_id' => $r('Problème', 'com_problems'), 'date' => $f('Observation du', 'date', true), 'summary' => $f('Constats et témoignages anonymisés', 'textarea', true),
        ], area: 'field'),
        'surveys' => $m('Enquêtes et questionnaires', 'field', [
            'name' => $f('Enquête', required: true), 'employee_id' => $employee, 'problem_id' => $r('Problème', 'com_problems'), 'questionnaire' => $f('Questions et méthode de participation volontaire', 'textarea', true), 'participants' => $f('Nombre de participants volontaires', 'number', true), 'findings' => $f('Résultats agrégés — sans données nominatives', 'textarea'), 'date' => $f('Date', 'date', true),
        ], area: 'field'),
        'proposals' => $m('Propositions de projets', 'field', [
            'name' => $f('Proposition de projet', required: true), 'problem_id' => $r('Besoin validé / en étude', 'com_problems'), 'chamber_id' => $r('Chambre technique pressentie', 'chambers'), 'hypothesis' => $f('Solution envisagée', 'textarea', true), 'feasibility' => $f('Faisabilité, contraintes et ressources', 'textarea', true),
        ], area: 'engineering', owner: false),
        'solutions' => $m('Suivi des solutions', 'field', [
            'name' => $f('Solution / expérimentation', required: true), 'problem_id' => $r('Besoin', 'com_problems'), 'proposal_id' => $r('Projet proposé', 'com_proposals'), 'chamber_id' => $r('Chambre réalisatrice', 'chambers'), 'results' => $f('Tests, retours utilisateurs et améliorations', 'textarea'), 'deployed_on' => $f('Date de déploiement', 'date'),
        ], area: 'solution', owner: false),
    ],
];
