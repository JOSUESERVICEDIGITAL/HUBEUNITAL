# Commerciale et Intelligence terrain

## Mise en service

```sh
php artisan migrate
php artisan db:seed --class=CommercialSeeder
```

Le seeder ajoute la Chambre Commerciale aux douze chambres précédentes, son département RH, les profils de coordination et un article de présentation. Il conserve les dossiers et les statuts d’ouverture existants ; il ne crée aucun commercial fictif.

Le superadmin ouvre **Chambres → Commerciale**, puis **Autorisations**. Chaque profil est attribué à un collaborateur actif déjà enregistré dans la RH et lié à un compte utilisateur. Les profils Spatie utilisent `CommercialAccessAccount` pour préserver les rôles généraux de l’application.

## Menu et périmètres

Le menu comprend les 19 entrées commerciales demandées : tableau de bord, objectifs, statistiques, prospects, clients, campagnes, rendez-vous, visites, opportunités, devis, commandes, contrats, catalogue, portefeuille, projets clients, relances, suivi des factures, commissions et rapports d’activité.

La rubrique Intelligence terrain ajoute huit entrées : signalements, observations, cartographie, enquêtes, problèmes récurrents, propositions de projets, transmission à l’Innovation et suivi des solutions. Les autorisations et l’historique sont accessibles séparément aux profils habilités.

| Profil | Périmètre |
| --- | --- |
| Responsable commercial | Dossiers de vente et de terrain, affectations, catalogue, objectifs, exports et historique |
| Agent commercial | Ses clients, prospects, dossiers commerciaux, objectifs et signalements ; catalogue partagé en consultation |
| Agent de veille | Ses missions terrain, problèmes, observations et enquêtes ; suivi des projets liés à ses besoins |
| Innovation | Besoins transmis, regroupements et validation des besoins |
| Ingénierie | Besoins transmis, étude de faisabilité et propositions de projets |
| Technique | Solutions, expérimentation, résultats des tests et déploiement |
| Juridique | Contrats soumis à vérification et avis juridiques |
| Finance | Émission des factures, encaissements et validation financière des commissions ayant reçu l’accord RH |
| RH | Validation RH des commissions ; aucun accès implicite aux autres modules commerciaux |

Le superadmin conserve l’accès global. Les profils partenaires dépendent de l’ouverture de leur chambre : Innovation, Ingénierie, Juridique, Finance ou RH. Le profil technique suit la chambre d’affectation du dossier RH ; sans chambre liée, il dépend de Développement. Ils peuvent traiter leurs dossiers partagés même si la Commerciale est fermée. Les agents commerciaux dépendent de l’ouverture de la Commerciale. Un dossier RH inactif, suspendu ou parti coupe leur accès commercial.

## Vente et coordination

- Un prospect qualifié est converti une seule fois en client ; les deux dossiers restent liés.
- Une vente reprend le montant du devis accepté et valide. Les liens agent/client/devis sont contrôlés ; un devis déjà utilisé est verrouillé.
- Les conditions actuelles d’un contrat nécessitent un avis Juridique favorable avant signature. Modifier les conditions ou les dates remet l’avis à vérifier.
- Les factures et paiements sont des dossiers partagés dont Finance est propriétaire. La Commerciale les consulte selon les affectations ; elle ne les émet pas et ne modifie pas les encaissements. Le total facturé ne dépasse pas la commande et un paiement ne dépasse pas le solde. Ces dossiers ne constituent pas une comptabilité générale.
- La commission est calculée sur une vente confirmée, avec le taux proposé par le responsable. La RH puis une autre personne habilitée côté Finance la valident. La deuxième validation crée un seul élément de paie RH lié à la commission. Aucun paiement ni message externe n’est effectué.
- **Disponibilités RH et avis sur les congés**, depuis le tableau de bord, affiche la présence ou l’indisponibilité des agents sans motifs médicaux, justificatifs, salaires ou dossiers disciplinaires. Le responsable commercial donne un avis sur les demandes de son équipe ; la RH prend la décision finale et gère les soldes. Modifier une demande en attente invalide l’avis précédent.

Les factures et encaissements des anciens CRM métiers restent conservés dans leurs espaces historiques ; cette évolution n’effectue pas de migration de ces données vers le nouveau suivi commercial.

## Du problème au projet

Le circuit contrôlé est : **signalé → à vérifier → validé → en étude → projet proposé → en développement → testé → déployé**.

L’agent transmet à l’Innovation, qui valide et regroupe. L’Ingénierie engage l’étude et rédige la proposition avant de transmettre le projet. Une solution liée à cette proposition est nécessaire pour démarrer la réalisation ; les résultats des tests et une date atteinte de déploiement sont nécessaires aux dernières étapes. Un agent peut suivre les décisions concernant son propre signalement sans s’auto-attribuer une validation technique.

Le module Propositions de projets constitue le dossier partagé de conception issu du besoin. Il ne crée pas artificiellement un client payant ou un projet dans les anciens CRM métiers. Les suivis techniques restent reliés au besoin et à la proposition dans la banque commune.

Les enquêtes contiennent leurs questions, méthode volontaire, nombre de participants et résultats agrégés. Elles ne publient pas de formulaire externe. Les sources restent des observations ouvertes, témoignages volontaires ou références publiques ; les champs invitent à exclure les identités privées et informations confidentielles.

La cartographie présente les localités et, si fournies, leurs coordonnées arrondies à deux décimales dans une représentation relative sans service cartographique externe. Les besoins sans coordonnées restent accessibles par localité. Les compteurs et cartes respectent le périmètre de chaque utilisateur ; aucune donnée d’exemple n’alimente les statistiques réelles.

## Contrôles

```sh
php artisan test --filter=CommercialCrmTest
```

Les écritures, conversions, étapes et validations sont transactionnelles et tracées dans `chamber_audits`. Les identifiants et filtres ne permettent pas de sortir du périmètre affecté. Les exports respectent les mêmes règles et protègent les cellules contre l’interprétation comme formules.

## Outils de travail du CRM

- **Pipeline des ventes** : opportunités regroupées par qualification, proposition, négociation, gagnées ou perdues. Les changements d’étape sont contrôlés et tracés.
- **Agenda et relances** : rendez-vous, visites et relances ouvertes sur une semaine, avec navigation entre les semaines et respect des affectations.
- **Fiche client** : accès aux contacts, échanges, devis, commandes, contrats, projets, factures, relances et autres dossiers liés. Les actions « Ajouter » préremplissent uniquement les relations autorisées ; les formulaires restent validés à l’enregistrement.
- **Devis détaillés** : jusqu’à 100 lignes avec prestation, quantité et prix unitaire. Le serveur recalcule chaque ligne et le total ; un montant envoyé par le navigateur ne peut pas remplacer ce calcul. Les devis historiques sans lignes restent utilisables. Une commande reprend le montant du devis accepté, qui est ensuite verrouillé.
- **Impression** : devis, commandes, factures et engagements disposent d’un document imprimable, avec enregistrement PDF depuis le navigateur. Les factures partielles n’affichent pas les lignes de la commande entière comme si elles avaient toutes été facturées.
- **Pièces privées** : PDF/JPG/PNG jusqu’à 10 Mo, stockés sur le disque `commercial_private`, sans URL publique. Les droits sont vérifiés sur le dossier parent et la pièce. Les fichiers doivent être sauvegardés avec la base.
- **Archivage réversible** : les responsables peuvent archiver les dossiers de support et les restaurer. Les données et pièces restent conservées ; les dossiers archivés ne figurent plus dans les listes actives et ne sont plus modifiables. Les ventes, contrats, factures, commissions et besoins suivent leurs propres statuts métier plutôt qu’un archivage libre.

Le tableau de bord présente les actions ouvertes, les relances en retard, les ventes et les montants encaissés ou restants selon le périmètre autorisé. Les compteurs ne sont pas des données de démonstration.

```sh
php artisan test --filter='CommercialCrmTest|CommercialWorkspaceTest'
```
