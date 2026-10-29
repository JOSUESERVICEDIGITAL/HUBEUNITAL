# Chambre Graphisme EUNITAL

Accès : **Chambres → Graphisme → Ouvrir le CRM**, ou `/graphisme`.
Le superadmin ouvre ou ferme la chambre depuis `/chambres/graphisme`.
L’installation conserve son statut précédent et n’ajoute aucun faux projet.

## Accès

Les admins et superadmins gèrent les dossiers et l’équipe. Les autres comptes
doivent être ajoutés comme membres actifs de **l’équipe Graphisme**. Une
appartenance à Ingénierie, Marketing ou Studio ne donne pas d’accès Graphisme.

Les designers créent et modifient les dossiers en préparation. Les responsables
Graphisme et administrateurs approuvent briefs, chartes, ressources, maquettes,
versions et livrables. Ils clôturent aussi les retours client. Les suppressions
sont réservées aux administrateurs et sont bloquées sur les dossiers référencés.
Les historiques et exports restent propres à la chambre.

La fermeture bloque lectures, écritures, galerie, planning, rapports et exports,
sans supprimer les données ou dépublier les articles du blog.

## Parcours créatif

1. Ajouter les comptes dans **Équipe Graphisme**, puis créer le **Client** et
   ses **Contacts**.
2. Créer le **Projet créatif** : discipline, périmètre, budget et échéance.
3. Préparer un **Brief créatif** versionné, avec objectifs, public cible,
   contraintes, références et contact client. Le faire approuver.
4. Documenter les **Identités & chartes** : palettes, typographies, licences,
   règles d’usage, version et document de référence.
5. Renseigner les **Ressources & licences** : fichiers, provenance, restrictions
   d’usage et crédits. Ces déclarations ne constituent pas une vérification
   automatique des droits.
6. Créer une **Maquette** par support. Préciser responsable, profil couleur,
   dimensions, spécifications et date de rendu. Les dimensions sont exigées à
   la validation des supports imprimés et packagings.
7. Ajouter une **Version** depuis la fiche de la maquette : identifiant unique
   au sein de la maquette, fichier source versionné, aperçu et notes de changement.
   La **Galerie** affiche les aperçus par projet et statut, lorsqu’une URL d’image
   directe est renseignée.
8. Enregistrer les **Retours & corrections** internes ou clients, leur version
   concernée, la zone à corriger, l’échéance et la réponse. Un responsable doit
   clôturer les retours client, avec une réponse ou justification.
9. Approuver la version puis la maquette. Les retours client ouverts sur la
   version empêchent son approbation ; les corrections ouvertes empêchent
   l’approbation finale de la maquette. Un nouveau retour ouvert ou une version
   soumise à validation remet une maquette approuvée « À valider », avec historique.
10. Depuis la fiche de la version, préparer un **Livrable**. Son projet, sa
    version et son format doivent être cohérents avec la source. La réception
    exige une version et une maquette approuvées, ainsi qu’un lien de livraison.
11. Terminer les **Tâches** et marquer le projet **Livré** quand tous ses livrables
    sont acceptés. Chaque maquette active doit avoir un rendu accepté et aucun
    retour en attente. Une maquette archivée ne fait plus partie du périmètre actif.
12. Rouvrir un projet livré ou annulé avant d’ajouter du travail créatif ou des
    corrections. Les opérations financières restent accessibles après livraison.

## Versions et documents

Les identifiants de version sont uniques par maquette. Dès sa première
approbation, une version conserve ses fichiers, son identifiant et ses notes de
changement : même après retrait de l’approbation, une modification exige une
nouvelle version. L’historique conserve l’auteur et les changements de statut.

Les documents sont référencés par URL HTTP/HTTPS. Le CRM ne dépose pas de fichiers,
ne fabrique pas les exports graphiques et ne vérifie pas le contenu des liens
distants. Utiliser des liens sources effectivement versionnés : le verrouillage
du CRM ne bloque pas une modification effectuée dans l’outil externe.
Les retours client sont consignés par l’équipe ; il n’existe pas de portail
d’approbation client ou d’envoi automatique de messages.

## Gestion et rapports

Le **Planning créatif** rassemble les dates des projets, maquettes, tâches et
livrables. Les listes proposent recherche, filtres, pagination et export CSV
filtré. Les rapports présentent l’avancement des maquettes et tâches, le budget,
les montants facturés, encaissés, restant à encaisser et les dépenses.

Les **Devis** acceptés préparent des **Factures**. Seules les factures émises
reçoivent des **Encaissements** ; les paiements nuls et dépassements sont refusés.
Une facture déjà encaissée ne peut pas être annulée ou réduite sous ses paiements.
Les montants sont des totaux TTC en FCFA, avec détails dans les notes, sans calcul
automatique de TVA ou de lignes tarifaires. L’impression et le PDF utilisent le
navigateur. Le solde encaissements moins dépenses ne constitue pas un bénéfice comptable.

## Installation

```shell
php artisan migrate
php artisan db:seed --class=GraphismeSeeder
php artisan test --filter="GraphismeCrmTest|StudioCrmTest|MarketingCrmTest|EngineeringCrmTest"
```

Le seeder publie un article illustré par une URL Unsplash dans la catégorie
Graphisme, sans doublon lors des relances et sans changer l’ouverture de la chambre.
