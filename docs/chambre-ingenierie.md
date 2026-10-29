# Chambre Ingénierie EUNITAL

## Accéder au CRM

Dans le sidebar du back office : **Chambres → Ingénierie → Ouvrir le CRM**.
L’adresse directe est `/ingenierie`. La page de statut reste accessible à
`/chambres/ingenierie`, y compris lorsque la chambre est fermée.

Le menu répertorie les 12 chambres. Les 11 autres disposent de leur page de
statut ; leurs modules métier restent à développer.

## Droits

- **Superadmin** : accès au CRM, gestion de l’équipe et ouverture / fermeture.
- **Admin** : gestion du CRM et de l’équipe, sans changement d’ouverture.
- **Membre actif** ajouté dans Équipe : consultation, création, modification et
  exports des dossiers d’ingénierie. Le rôle Responsable permet la réception
  des livrables. Les ingénieurs peuvent soumettre leurs rendus à validation.
- **Autre utilisateur** : aucun accès au CRM.

La suppression de dossiers est réservée aux admins et superadmins. Un dossier
référencé par un autre ne peut pas être supprimé. Préférer les statuts Archivé,
Annulé, Terminé ou Suspendu selon la section. Les changements restent tracés.

La fermeture bloque les consultations, écritures et exports du CRM pour tous
les comptes. Elle ne supprime rien et ne dépublie pas les articles du blog.
Seul le superadmin peut rouvrir la chambre.

## Parcours conseillé

1. Dans **Équipe**, ajouter les comptes utilisateurs existants autorisés.
2. Créer un **Client**, puis ses **Contacts**.
3. Qualifier une **Opportunité** : responsable, montant, probabilité, étape et
   prochaine relance. Depuis sa fiche, créer le projet prérempli.
4. Définir le **Projet** : client, chef de projet, budget, dates et périmètre.
5. Décomposer le travail en **Tâches**, avec responsables, priorités et échéances.
6. Ajouter les **Livrables** : projet, tâche, version, document en ligne et
   critères de réception. Passer à « À valider », puis faire accepter par un
   responsable ou administrateur. Un lien de rendu est exigé pour l’acceptation.
7. Établir un **Devis** avec une référence unique, un montant TTC, les prestations
   et conditions dans les notes. Un devis accepté permet de préparer la facture.
8. Émettre la **Facture** avant d’enregistrer ses **Encaissements**. Le solde est
   calculé à partir des paiements. Les dépassements et paiements nuls sont refusés.
9. Consigner les **Dépenses** du projet et les **Échanges & relances** clients.
10. Utiliser les **Rapports** pour suivre tâches, budget, facturation, paiements
    et dépenses. Le solde encaissements moins dépenses ne constitue pas un
    résultat comptable et n’inclut que les montants saisis.

Les fiches comportent des liens vers leurs dossiers associés. Les listes sont
recherchables, filtrables et paginées. L’export CSV respecte les filtres actifs.
Les devis et factures peuvent être imprimés ou enregistrés en PDF via le
navigateur. Les montants sont en FCFA ; les documents utilisent un total TTC
et des notes de détail, sans calcul automatique de TVA ou de lignes tarifaires.
Les documents de livrables sont référencés par URL HTTPS/HTTP ; aucun fichier
n’est envoyé automatiquement à un client.

## Installation et vérification

```shell
php artisan migrate
php artisan db:seed --class=EngineeringArticleSeeder
php artisan test --filter=EngineeringCrmTest
```

Le seeder initialise les chambres et publie un article de présentation dans la
catégorie Ingénierie. Il peut être relancé sans doublons et conserve les
fermetures existantes. Il ne crée aucun faux client, projet ou paiement.
L’image du blog utilise une URL Unsplash. Son affichage dépend de ce service.

Les nouvelles tables `eng_*` sont réservées au CRM Ingénierie. Les opérations
sont enregistrées dans `chamber_audits`. Le contrôle de fermeture est vérifié
à nouveau sous verrou dans les transactions d’écriture.
