<?php

$f = fn ($label, $type = 'text', $required = false, $extra = []) => compact('label', 'type', 'required') + $extra;
$s = fn ($label, $options, $required = true) => $f($label, 'select', $required, compact('options'));
$r = fn ($label, $table, $required = true, $display = 'name') => $f($label, 'relation', $required, compact('table', 'display'));
$employee = $r('Employé', 'rh_employees');
$notes = $f('Observations', 'textarea');
$file = $f('Pièce jointe confidentielle (PDF, JPG ou PNG, 10 Mo max.)', 'file');
$status = $s('Statut', ['planned' => 'Prévu', 'in_progress' => 'En cours', 'done' => 'Terminé', 'cancelled' => 'Annulé']);
$leaveTypes = ['annual' => 'Annuel', 'sick' => 'Maladie', 'maternity' => 'Maternité', 'permission' => 'Permission'];
$m = fn ($label, $group, $area, $fields, $self = false) => compact('label', 'group', 'area', 'fields', 'self');

return [
    'timezone' => 'Africa/Ouagadougou',
    'groups' => ['personnel' => 'Personnel et organisation', 'time' => 'Temps et assiduité', 'career' => 'Recrutement et carrière', 'administration' => 'Administration et suivi'],
    'modules' => [
        'departments' => $m('Départements et chambres', 'personnel', 'personnel', [
            'name' => $f('Département', required: true), 'chamber_id' => $r('Chambre EUNITAL', 'chambers', false), 'manager' => $f('Responsable métier'), 'notes' => $notes,
        ]),
        'employees' => $m('Tous les employés', 'personnel', 'personnel', [
            'name' => $f('Nom complet', required: true), 'registration' => $f('Matricule unique', required: true), 'user_id' => $r('Compte utilisateur', 'users', false),
            'department_id' => $r('Département / chambre', 'rh_departments'), 'position' => $f('Poste', required: true),
            'profession' => $s('Métier', ['sales' => 'Commercial', 'field' => 'Agent de veille terrain', 'developer' => 'Développeur', 'engineer' => 'Ingénieur', 'designer' => 'Graphiste', 'trainer' => 'Formateur', 'secretary' => 'Secrétaire', 'other' => 'Autre collaborateur']),
            'email' => $f('Email', 'email'), 'phone' => $f('Téléphone'), 'hired_on' => $f('Date d’entrée', 'date', true),
            'status' => $s('Statut', ['onboarding' => 'En intégration', 'active' => 'Actif', 'suspended' => 'Suspendu', 'departed' => 'Parti']), 'notes' => $notes,
        ]),
        'files' => $m('Matricules et dossiers du personnel', 'personnel', 'personnel', [
            'employee_id' => $employee, 'name' => $f('Intitulé de la pièce', required: true), 'reference' => $f('Référence'), 'attachment' => $file, 'notes' => $notes,
        ]),
        'assignments' => $m('Postes et affectations', 'personnel', 'personnel', [
            'employee_id' => $employee, 'department_id' => $r('Département', 'rh_departments'), 'position' => $f('Poste', required: true),
            'starts_on' => $f('Début', 'date', true), 'ends_on' => $f('Fin', 'date'), 'status' => $status, 'notes' => $notes,
        ]),
        'contracts' => $m('Contrats de travail', 'personnel', 'personnel', [
            'employee_id' => $employee, 'name' => $f('Référence du contrat', required: true), 'type' => $s('Nature', ['permanent' => 'CDI', 'fixed' => 'CDD', 'internship' => 'Stage', 'amendment' => 'Avenant', 'renewal' => 'Renouvellement']),
            'starts_on' => $f('Début', 'date', true), 'ends_on' => $f('Fin / renouvellement', 'date'), 'status' => $s('Statut', ['draft' => 'Préparation', 'active' => 'En vigueur', 'ended' => 'Terminé']), 'attachment' => $file, 'notes' => $notes,
        ]),
        'schedules' => $m('Plannings et horaires', 'time', 'time', [
            'employee_id' => $employee, 'name' => $f('Intitulé', required: true), 'starts_at' => $f('Début prévu', 'datetime-local', true),
            'ends_at' => $f('Fin prévue', 'datetime-local', true), 'break_minutes' => $f('Pause non travaillée (minutes)', 'number', true), 'notes' => $notes,
        ], true),
        'attendance' => $m('Pointage des présences', 'time', 'time', [
            'employee_id' => $employee, 'schedule_id' => $r('Horaire prévu', 'rh_schedules'), 'arrived_at' => $f('Arrivée', 'datetime-local', true),
            'left_at' => $f('Départ', 'datetime-local'), 'break_minutes' => $f('Pause effective (minutes)', 'number', true), 'notes' => $notes,
        ], true),
        'adjustments' => $m('Retards et heures supplémentaires', 'time', 'time', [
            'employee_id' => $employee, 'date' => $f('Date', 'date', true), 'type' => $s('Nature', ['late' => 'Retard', 'overtime' => 'Heures supplémentaires']),
            'minutes' => $f('Durée (minutes)', 'number', true), 'status' => $s('Validation', ['pending' => 'À valider', 'approved' => 'Validé', 'rejected' => 'Rejeté']), 'notes' => $notes,
        ]),
        'absences' => $m('Absences et justificatifs', 'time', 'time', [
            'employee_id' => $employee, 'starts_on' => $f('Début', 'date', true), 'ends_on' => $f('Fin', 'date', true),
            'status' => $s('Justification', ['pending' => 'À justifier', 'justified' => 'Justifiée', 'unjustified' => 'Injustifiée', 'authorized' => 'Autorisée']), 'attachment' => $file, 'notes' => $notes,
        ]),
        'balances' => $m('Soldes de congés', 'time', 'time', [
            'employee_id' => $employee, 'year' => $f('Année', 'number', true), 'type' => $s('Type', $leaveTypes), 'entitlement' => $f('Droits accordés (jours)', 'decimal', true), 'notes' => $notes,
        ], true),
        'leaves' => $m('Demandes de congés', 'time', 'time', [
            'employee_id' => $employee, 'type' => $s('Type', $leaveTypes), 'starts_on' => $f('Début', 'date', true), 'ends_on' => $f('Fin', 'date', true),
            'days' => $f('Jours à décompter selon le planning', 'decimal', true), 'status' => $s('Décision', ['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Refusé', 'cancelled' => 'Annulé']), 'attachment' => $file, 'notes' => $notes,
        ], true),
        'jobs' => $m('Offres d’emploi', 'career', 'recruitment', [
            'name' => $f('Intitulé du poste', required: true), 'department_id' => $r('Département', 'rh_departments'), 'closes_on' => $f('Clôture', 'date'),
            'status' => $s('Statut', ['draft' => 'Brouillon', 'open' => 'Ouverte', 'closed' => 'Clôturée']), 'description' => $f('Profil et missions', 'textarea', true),
        ]),
        'applications' => $m('Candidatures et entretiens', 'career', 'recruitment', [
            'name' => $f('Nom du candidat', required: true), 'job_id' => $r('Offre', 'rh_jobs'), 'email' => $f('Email', 'email', true), 'phone' => $f('Téléphone'),
            'interview_at' => $f('Entretien', 'datetime-local'), 'status' => $s('Étape', ['received' => 'Reçue', 'interview' => 'Entretien', 'selected' => 'Sélectionnée', 'hired' => 'Recrutée', 'rejected' => 'Refusée']),
            'attachment' => $file, 'notes' => $notes,
        ]),
        'onboarding' => $m('Intégration des recrues', 'career', 'recruitment', [
            'employee_id' => $employee, 'name' => $f('Étape d’intégration', required: true), 'due_date' => $f('Échéance', 'date'), 'mentor' => $f('Référent'), 'status' => $status, 'notes' => $notes,
        ]),
        'evaluations' => $m('Évaluations de performance', 'career', 'career', [
            'employee_id' => $employee, 'name' => $f('Période / évaluation', required: true), 'date' => $f('Date', 'date', true), 'reviewer' => $f('Évaluateur', required: true),
            'objectives' => $f('Objectifs et résultats', 'textarea'), 'skills' => $f('Compétences et progression', 'textarea'), 'score' => $f('Note / 100', 'number'), 'notes' => $notes,
        ]),
        'training' => $m('Formations internes', 'career', 'career', [
            'employee_id' => $employee, 'name' => $f('Formation / compétence', required: true), 'starts_on' => $f('Début', 'date', true), 'ends_on' => $f('Fin', 'date'), 'trainer' => $f('Formateur'), 'status' => $status, 'notes' => $notes,
        ]),
        'careers' => $m('Promotions et mutations', 'career', 'career', [
            'employee_id' => $employee, 'type' => $s('Évolution', ['promotion' => 'Promotion', 'transfer' => 'Mutation', 'position' => 'Changement de poste']), 'department_id' => $r('Département d’arrivée', 'rh_departments'),
            'position' => $f('Nouveau poste', required: true), 'date' => $f('Date d’effet', 'date', true), 'status' => $status, 'notes' => $notes,
        ]),
        'sales' => $m('Suivi RH des commerciaux', 'administration', 'personnel', [
            'employee_id' => $employee, 'name' => $f('Mission / visite terrain', required: true), 'date' => $f('Date', 'date', true), 'starts_at' => $f('Départ prévu', 'datetime-local'), 'ends_at' => $f('Retour prévu', 'datetime-local'),
            'location' => $f('Lieu / établissements'), 'objectives' => $f('Objectifs individuels et suivi administratif', 'textarea'), 'status' => $status, 'notes' => $notes,
        ]),
        'payroll' => $m('Primes et éléments de paie', 'administration', 'payroll', [
            'employee_id' => $employee, 'period' => $f('Période (AAAA-MM)', required: true), 'type' => $s('Élément', ['bonus' => 'Prime', 'allowance' => 'Indemnité', 'variable' => 'Variable', 'benefit' => 'Avantage']),
            'name' => $f('Libellé', required: true), 'amount' => $f('Montant (FCFA)', 'decimal', true), 'status' => $s('Statut', ['draft' => 'À préparer', 'approved' => 'Validé pour Finance', 'transmitted' => 'Transmis à Finance']), 'notes' => $notes,
        ]),
        'discipline' => $m('Discipline et avertissements', 'administration', 'discipline', [
            'employee_id' => $employee, 'name' => $f('Objet confidentiel', required: true), 'date' => $f('Date', 'date', true),
            'type' => $s('Nature', ['incident' => 'Incident', 'warning' => 'Avertissement', 'sanction' => 'Sanction', 'conflict' => 'Conflit interne', 'wellbeing' => 'Conditions de travail / sécurité / bien-être']),
            'status' => $status, 'attachment' => $file, 'notes' => $notes,
        ]),
        'documents' => $m('Documents et attestations RH', 'administration', 'personnel', [
            'employee_id' => $employee, 'name' => $f('Document / attestation', required: true), 'date' => $f('Date', 'date', true), 'visibility' => $s('Accès employé', ['hr' => 'RH uniquement', 'employee' => 'Consultable par l’employé']), 'attachment' => $file, 'notes' => $notes,
        ], true),
        'departures' => $m('Départs et fins de contrat', 'administration', 'personnel', [
            'employee_id' => $employee, 'date' => $f('Date de départ', 'date', true), 'type' => $s('Motif', ['resignation' => 'Démission', 'end' => 'Fin de contrat', 'retirement' => 'Retraite', 'other' => 'Autre']),
            'handover' => $f('Passation et restitution du matériel', 'textarea'), 'status' => $status, 'attachment' => $file, 'notes' => $notes,
        ]),
    ],
];
