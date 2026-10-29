# Chambre Studio EUNITAL

Accès : **Chambres → Studio → Ouvrir le CRM**, ou `/studio`.
Le statut existant est conservé à l’installation. Le superadmin ouvre ou ferme
la chambre depuis `/chambres/studio`. La fermeture bloque le CRM et les exports
sans supprimer les productions, médias ou paiements.

## Équipe et droits

Les admins et superadmins gèrent les dossiers et les accès. Un utilisateur
ordinaire doit être ajouté à **Équipe Studio** comme membre actif. Les équipes
Marketing et Ingénierie n’accordent aucun accès automatique au Studio.

Les techniciens peuvent créer et suivre les productions, séances, médias,
tâches et rendus. Les responsables Studio et administrateurs valident les briefs,
médias et livrables, et modifient les rendus déjà validés. Les suppressions sont
réservées aux administrateurs ; les dossiers liés sont protégés contre la suppression.
Les opérations disposent d’un historique propre au Studio.

## Parcours de production

1. Ajouter les comptes existants dans **Équipe Studio**.
2. Créer le **Client**, ses **Contacts**, puis la **Production** photo, vidéo,
   audio, live ou mixte. Renseigner le périmètre, le budget et la date de livraison.
3. Préparer un **Brief / scénario**, sa version, ses spécifications et les droits
   de diffusion. Le faire approuver par un responsable.
4. Enregistrer les **Lieux & plateaux** réellement utilisables et les équipements
   individuels dans **Matériel**, avec un code d’inventaire unique par unité.
   Aucun plateau ou équipement fictif n’est créé par le seeder.
5. Planifier les **Séances & tournages** : production, lieu, dates et horaires,
   responsable et feuille de service. Tous les horaires utilisent l’heure de
   Casablanca (`Africa/Casablanca`), y compris les saisies et le planning mensuel.
6. Créer les **Réservations matériel** et l’**Équipe de tournage** depuis la fiche
   de la séance. Les réservations héritent de ses horaires. Deux plages contiguës
   sont autorisées ; deux plages qui se chevauchent ne le sont pas pour un même
   lieu, équipement ou collaborateur.
7. Suivre le matériel **Réservé → Sorti → Retourné**. Un matériel sorti ne peut
   pas être réaffecté ou annulé sans retour. Une sortie physique reste bloquée
   si une autre séance n’a pas rendu le même équipement, même après son horaire.
8. À la clôture d’une séance, régulariser les réservations et retours. La fin
   marque les missions de l’équipe terminées. Une annulation annule les réservations
   encore en attente et les missions, avec historique ; du matériel sorti doit
   être retourné avant cette annulation.
9. Référencer les **Médias & rushes** : URL, version, sauvegarde, droits et source.
   Les fichiers restent sur l’espace documentaire de votre choix ; le CRM conserve
   leurs liens. Il ne stocke ni ne transcode de vidéos et ne vérifie pas l’accès
   aux liens distants. Les droits saisis sont des déclarations documentées.
10. Organiser les **Tâches** de préparation, montage, son, étalonnage, retouche,
    sous-titrage et export. Soumettre les **Livrables** avec format, version et
    lien du rendu, puis les faire accepter par un responsable.
11. Marquer la production **Livrée** lorsque tous ses livrables sont acceptés,
    ses tâches terminées et ses séances clôturées. Au moins un rendu accepté
    est nécessaire. Une production ne peut pas être annulée avec des séances actives.

Le changement d’horaire d’une séance recontrôle aussi son matériel et son équipe.
Les lieux indisponibles et équipements en maintenance ou retirés ne peuvent pas
être réservés. Il faut régulariser les réservations actives avant de retirer une
ressource du service.

## Gestion commerciale et financière

Créer un **Devis** avec référence unique et montant total TTC en FCFA.
Un devis accepté permet de préparer une **Facture** préremplie.
Seules les factures émises acceptent des **Encaissements** ; le CRM refuse les
paiements nuls ou supérieurs au solde. Une facture encaissée ne peut pas être
annulée, réduite sous ses encaissements ou transférée à un autre client.

Les **Dépenses** sont rattachées à la production. Les rapports comparent budget,
facturation, paiements, dépenses et progression des tâches. Le solde encaissements
moins dépenses n’est pas un résultat comptable. Les devis et factures utilisent
un total TTC et des notes ; il n’y a pas de moteur de TVA ou de lignes tarifaires.
L’annulation d’une production n’annule pas automatiquement ses documents financiers.

Les listes proposent recherche, filtres, pagination et export CSV filtré. Les
devis et factures s’impriment ou s’enregistrent en PDF depuis le navigateur.
Les fiches donnent accès à leurs dossiers associés et à leur historique.

## Installation et tests

```shell
php artisan migrate
php artisan db:seed --class=StudioSeeder
php artisan test --filter="StudioCrmTest|MarketingCrmTest|EngineeringCrmTest"
```

Le seeder publie un article de présentation dans la catégorie Studio avec une
image distante Unsplash. Il peut être relancé sans doublon et conserve le statut
d’ouverture existant. Il ne crée pas de faux clients, productions ou paiements.
