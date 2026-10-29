# Chambre RH EUNITAL

Le menu `/rh` réunit les 24 entrées demandées : tableau de bord, 22 modules de gestion et rapports. Le personnel des chambres existantes et futures est centralisé dans `rh_employees` ; un département peut être relié à une chambre EUNITAL ou être indépendant.

## Installation et ouverture

```sh
composer install
php artisan migrate
php artisan db:seed --class=RhSeeder
```

Le seeder crée les permissions, les quatre profils, les départements des chambres et l’article de présentation. Il ne crée aucun dossier employé fictif et n’attribue pas de droits à des utilisateurs existants. Il conserve le statut d’ouverture de la chambre.

Le superadmin ouvre **Chambres → RH**, puis **Autorisations RH** pour choisir l’utilisateur, le profil et son statut d’accès. Un employé doit également avoir son compte utilisateur relié à son dossier RH. Le matricule et le compte utilisateur sont uniques parmi les dossiers employés.

## Profils Spatie

| Profil | Accès |
| --- | --- |
| `rh-responsable` | Tous les domaines RH, dossiers sensibles, rapports, historique et transmission Finance |
| `rh-assistant` | Personnel, temps, recrutement, carrière et leurs rapports ; aucune paie, discipline ou consultation de l’historique confidentiel |
| `rh-finance` | Consultation et export des éléments de paie validés ou transmis ; enregistrement de la transmission |
| `rh-employe` | Ses horaires, présences, congés, soldes et documents explicitement publiés pour lui ; création et modification de ses demandes de congés en attente |

Seul le superadmin attribue les profils. Un administrateur général ne reçoit pas automatiquement les droits RH. Les autorisations Spatie sont portées par `RhAccessAccount`, lié à `users`, afin de préserver le champ `User::role` et la méthode `User::hasRole` déjà utilisés par les autres chambres.

Les restrictions s’appliquent aux listes, filtres, identifiants directs, formulaires, pièces et exports. Les statistiques respectent les domaines autorisés. Fermer la chambre bloque tous ses accès tout en conservant les dossiers.

## Parcours et règles opérationnelles

- Une candidature sélectionnée peut être intégrée une seule fois. L’action crée un employé en intégration et une première tâche d’accueil. La RH complète ensuite son métier, son compte et son contrat.
- Une affectation ou une mutation terminée met à jour le département et le poste de l’employé. Une opération future reste prévue jusqu’à sa date d’effet.
- Le pointage référence un horaire du même employé. Il calcule les minutes travaillées, de retard et de dépassement, en déduisant les pauses. Le dépassement calculé reste soumis à la validation RH ; aucune conversion automatique en rémunération n’est appliquée. Le module de régularisations permet de suivre cette validation séparément.
- Les droits à congés sont renseignés par employé, année et type. La RH indique les jours à décompter selon le planning, par demi-journée. L’approbation contrôle le solde et les chevauchements ; elle débite le solde une seule fois, et l’annulation le rétablit. Une demande couvrant deux années doit être séparée. Un responsable ne décide pas son propre congé.
- Les éléments de paie sont des variables, primes, indemnités et avantages en FCFA. Ils ne constituent pas un calcul complet de paie, de retenues ou de cotisations. Finance reçoit le CSV des éléments validés. La transmission enregistrée verrouille l’élément ; elle ne réalise ni paiement ni envoi externe.
- La clôture d’un départ archive le dossier employé, termine ses contrats actifs et désactive son compte d’accès RH. Elle ne supprime pas le compte général ni les historiques des autres chambres. La révocation des autres habilitations reste du ressort des responsables de ces espaces.

Les modifications RH et leurs effets métier sont regroupés en transactions et enregistrés dans `chamber_audits`. Les téléchargements et exports sont également tracés.

## Pièces confidentielles

Les pièces PDF/JPG/PNG sont limitées à 10 Mo et stockées sur le disque `rh_private`, dans `storage/app/rh-private`, sans URL publique. Seule la route de téléchargement RH peut les servir après contrôle des droits. Les réponses RH sont marquées `private, no-store`.

Lors du remplacement d’une pièce, l’ancienne version reste dans le stockage privé pour préserver les données. Les pièces sont à inclure dans les sauvegardes avec la base de données. Leur purge et les durées de conservation relèvent de la politique de l’entreprise.

## Vérification

```sh
php artisan test --filter=RhCrmTest
```

Les tests couvrent les profils, l’accès au seul dossier personnel, les pièces privées, les soldes et validations de congés, les pointages, la paie, le recrutement, les départs et la répétition du seeder.
