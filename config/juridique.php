<?php

$field = fn ($label, $type = 'text', $required = false, $extra = []) => compact('label', 'type', 'required') + $extra;
$status = $field('Statut', 'select', true, ['options' => ['draft' => 'À préparer', 'in_progress' => 'En cours', 'review' => 'À valider', 'referred' => 'Transmis au professionnel habilité', 'done' => 'Terminé', 'cancelled' => 'Annulé']]);
$relation = fn ($label, $table, $required = false) => $field($label, 'relation', $required, ['table' => $table, 'display' => 'name']);
$common = [
    'name' => $field('Objet du dossier', 'text', true),
    'scope' => $field('Bénéficiaire', 'select', true, ['options' => ['internal' => 'EUNITAL — interne', 'client' => 'Client du Hub']]),
    'client_id' => $relation('Client', 'jur_clients'),
    'assigned_to' => $relation('Responsable opérationnel', 'users'),
    'status' => $status,
    'due_date' => $field('Échéance', 'date'),
    'partner_id' => $relation('Professionnel habilité', 'jur_partners'),
    'requires_professional' => $field('Intervention réservée à un professionnel habilité', 'select', true, ['options' => ['no' => 'Non — assistance documentaire et administrative', 'yes' => 'Oui — orientation et coordination avec un professionnel habilité']]),
    'description' => $field('Description et pièces à réunir', 'textarea'),
    'notes' => $field('Suivi et observations', 'textarea'),
];
$modules = [
    'clients' => ['label' => 'Clients et entreprises', 'fields' => [
        'name' => $field('Nom / raison sociale', 'text', true), 'email' => $field('Email', 'email'), 'phone' => $field('Téléphone'), 'registration' => $field('RCCM / référence'),
        'status' => $field('Statut', 'select', true, ['options' => ['active' => 'Actif', 'archived' => 'Archivé']]), 'notes' => $field('Observations', 'textarea'),
    ]],
    'partners' => ['label' => 'Professionnels habilités', 'fields' => [
        'name' => $field('Nom / cabinet', 'text', true), 'profession' => $field('Profession', 'select', true, ['options' => ['lawyer' => 'Avocat', 'notary' => 'Notaire', 'other' => 'Autre professionnel habilité']]),
        'email' => $field('Email', 'email'), 'phone' => $field('Téléphone'), 'qualification' => $field('Habilitation / référence professionnelle', 'text', true), 'notes' => $field('Coordination', 'textarea'),
    ]],
];
foreach ([
    'contracts' => ['Contrats et engagements', ['commercial' => 'Commercial', 'service' => 'Prestation de services', 'partnership' => 'Partenariat', 'nda' => 'Confidentialité (NDA)', 'employment' => 'Travail', 'subcontract' => 'Sous-traitance']],
    'formalities' => ['Création et formalisation', ['creation' => 'Création d’entreprise', 'rccm' => 'Immatriculation RCCM', 'statutes' => 'Modification statutaire', 'director' => 'Changement de dirigeant', 'legal_form' => 'Orientation sur la forme juridique']],
    'compliance' => ['Conformité et veille', ['ohada' => 'Veille Burkina Faso / OHADA', 'data' => 'Données personnelles', 'digital' => 'Activité commerciale et numérique', 'policy' => 'Procédures et confidentialité', 'obligation' => 'Obligation réglementaire']],
    'intellectual_property' => ['Protection et propriété intellectuelle', ['trademark' => 'Marque / logo — OAPI', 'copyright' => 'Droits d’auteur', 'license' => 'Licence logicielle', 'assignment' => 'Cession de droits', 'dispute' => 'Prévention / coordination de litige']],
    'administration' => ['Démarches et institutions', ['authorization' => 'Autorisation', 'accreditation' => 'Agrément', 'customs' => 'Documentation douanière', 'institution' => 'Dossier institutionnel', 'other' => 'Autre démarche administrative']],
    'assistance' => ['Accompagnement et formations', ['information' => 'Information juridique générale', 'awareness' => 'Sensibilisation', 'training' => 'Formation droit des affaires / numérique', 'documentation' => 'Assistance documentaire', 'referral' => 'Orientation vers un professionnel']],
] as $key => [$label, $types]) {
    $modules[$key] = ['label' => $label, 'fields' => $common + ['type' => $field('Nature du dossier', 'select', true, ['options' => $types])]];
}
$modules['team'] = ['label' => 'Équipe et accès', 'fields' => [
    'user_id' => $relation('Utilisateur', 'users', true), 'position' => $field('Fonction', 'select', true, ['options' => ['manager' => 'Responsable opérationnel', 'jurist' => 'Juriste', 'assistant' => 'Assistant administratif', 'partner' => 'Partenaire juridique']]),
    'status' => $field('Statut', 'select', true, ['options' => ['active' => 'Actif', 'inactive' => 'Inactif']]),
]];
return ['modules' => $modules];
