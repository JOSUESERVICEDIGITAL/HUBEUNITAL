# Chambre Marketing EUNITAL

Accès : **Chambres → Marketing → Ouvrir le CRM**, ou `/marketing`.
La page `/chambres/marketing` reste accessible quand le CRM est fermé.
Le statut existant est conservé à l’installation : un superadmin ouvre la
chambre depuis cette page lorsqu’elle est fermée.

## Équipe et droits

Les admins et superadmins accèdent au CRM et gèrent son équipe. Un utilisateur
ordinaire doit être ajouté comme membre actif de **l’équipe Marketing** ; son
appartenance à Ingénierie ne lui donne aucun accès Marketing.

Les membres Marketing peuvent gérer les dossiers et soumettre des contenus
à validation. Les responsables Marketing et administrateurs approuvent les
stratégies et ressources, programment les contenus et consignent les publications.
Seuls les administrateurs suppriment des dossiers. Les liens entre dossiers
protègent contre une suppression en cascade involontaire.

Seul le superadmin ouvre ou ferme la chambre. La fermeture bloque lectures,
écritures, conversion des prospects et exports, sans supprimer les données.
Les historiques sont séparés entre Marketing et Ingénierie.

## Parcours de travail

1. Ajouter les comptes existants dans **Équipe Marketing**.
2. Créer les **Clients** et leurs **Contacts**.
3. Formaliser une **Stratégie de marque** : positionnement, objectifs,
   différenciation, version et charte.
4. Définir les **Audiences**, avec leurs critères et l’origine des données.
5. Préparer la **Campagne** : client, stratégie, audience, objectifs, responsable,
   budget et dates. Les stratégies et audiences doivent appartenir au bon client.
6. Renseigner les **Canaux** : les canaux initiaux sont des entrées de suivi,
   sans compte externe connecté.
7. Créer les **Contenus** et les **Tâches**. Joindre les rendus par URL,
   demander une validation, puis faire approuver et programmer par un responsable.
   Le **Calendrier éditorial** est un agenda mensuel des dates de diffusion prévues.
8. Documenter les **Prospects**, leur source, leur autorisation de contact et
   leur étape. Le **Pipeline** affiche les étapes avec effectifs et valeurs estimées.
   « Convertir en client » crée ou retrouve le dossier client et marque le prospect
   gagné, sans doublon lors d’un second clic. Le client converti peut différer du
   client commanditaire de sa campagne d’acquisition.
9. Consigner les **Échanges & relances**. Une relance sortante planifiée exige
   une autorisation de contact confirmée. Aucun message n’est envoyé par le CRM.
10. Saisir les **Résultats journaliers** par campagne, canal et jour. Le trio est
    unique. Saisir les valeurs du jour, jamais des cumuls de période. Corriger le
    relevé existant en cas de mise à jour. Les résultats futurs sont refusés.
11. Saisir les **Dépenses de production**, hors dépenses média déjà renseignées
    dans les résultats. La **Bibliothèque créative** conserve URL, version, droits
    d’utilisation et validation des ressources.

## Rapports

- CTR : clics / impressions × 100.
- Coût par prospect : dépenses média / prospects mesurés.
- Coût par conversion : dépenses média / conversions mesurées.
- ROAS : revenus attribués / dépenses média, exprimé en multiple.
- Budget disponible : budget global − dépenses média − dépenses de production.

Les divisions sans dénominateur donnent « — ». Les revenus sont attribués aux
campagnes, pas des factures encaissées. Les prospects mesurés dans les relevés
et les fiches du pipeline sont deux suivis distincts : ne pas additionner leurs
totaux. Les rapports n’imposent pas de blocage automatique à un dépassement du
budget ; ils l’affichent. Les devises sont des montants en FCFA.

Les résultats sont saisis manuellement. Aucune synchronisation publicitaire,
publication sur les réseaux sociaux, collecte de prospects ou messagerie externe
n’est activée. Le statut « Publié » est un enregistrement d’une diffusion réelle,
avec date et lien. Les pièces créatives sont référencées par URL, sans dépôt local.

Les listes proposent recherche, filtres, pagination et export CSV respectant
les filtres. Les exports neutralisent les cellules interprétables comme formules.
Chaque fiche propose ses dossiers associés et son historique de modifications.

## Installation

```shell
php artisan migrate
php artisan db:seed --class=MarketingSeeder
php artisan test --filter="MarketingCrmTest|EngineeringCrmTest"
```

Le seeder ajoute les canaux de référence et un article publié dans la catégorie
Marketing, illustré par une URL Unsplash. Il ne crée pas de faux clients,
campagnes ou résultats et ne modifie pas une fermeture décidée par le superadmin.
