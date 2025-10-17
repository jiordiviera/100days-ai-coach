# 100DaysOfCode AI Coach · Guide d’utilisation (développeurs)

Ce document résume les parcours clés à tester le jour du lancement. Il s’adresse aux développeurs qui disposent déjà d’un environnement local fonctionnel (`composer install`, `bun install`, `php artisan migrate:fresh --seed`, `composer run dev`).

---

## 1. Connexion & Onboarding

- **Compte seedé** : `test@example.com` / `password`.  
  Après connexion, le tableau de bord s’ouvre directement (l’onboarding guidé est désactivé par défaut via `needs_onboarding = false`).
- **Rejoindre un challenge** : si aucun run actif, la page *Daily Journal* propose un bouton “Explore challenges” et un champ “Join with a code”. La commande `php artisan db:seed --class=ChallengeSeeder` (si disponible) permet d’amorcer un run pour les tests.

## 2. Tableau de bord

- **Header** : CTA vers le *Daily log*, *Projects* et *Challenges*.  
- **Widget GitHub** : composant Livewire `partials.github-template-setup`. Il charge la liste des organisations GitHub au premier focus (appel différé), propose la création de repo via le template configuré (`config/services.php`).  
- **Statistiques rapides** :
  - *Streak* (jours consécutifs),
  - *Tasks completed* (taux d’achèvement),
  - *Challenges* (progression sur le run actif).
- **Daily recap cards** : reflètent `dailyProgress` (heures, logs, dernier billet).
- **Onboarding checklist** : visible tant que `all_completed` est faux. Chaque item contient un lien interne à suivre.  
- **Section Expériences** : widgets pour WakaTime, AI tips, etc. Tester le comportement avec/ sans données (charger des fakes via seeders si besoin).

## 3. Journal quotidien (Daily Challenge)

Parcours principal pour saisir la journée :

1. **Header** : navigation jour précédent/suivant, réouverture du tutoriel (événement Livewire `daily-challenge-tour-open`).  
2. **Statistiques** : streak, nombre de logs, heures cumulées, focus du run.  
3. **Formulaire d’entrée** :
   - champs *What did you ship?*, *Learnings*, *Challenges faced*, *Hours coded* ;
   - attacher un projet (`projects` seedés) ;
   - bouton *Save today’s entry* → déclenche les jobs AI (résumé, tags, drafts) si configurés.  
4. **AI coach** :
   - bloc “AI insights” affiche l’état de la génération ;  
   - actions *Regenerate AI*, *Copy LinkedIn draft*, *Copy X draft*.  
5. **Historique récent** : dernière semaine, badges, chrono. Vérifier l’affichage lorsque peu de données sont présentes (grâce aux states par défaut dans la Livewire component).

## 4. Gestion des projets

### 4.1 Project Manager (`/projects`)

- **Hero** : stats projets / tâches / membres.  
- **Création de projet** : formulaire avec nom, description, template (si des `ProjectTemplate` existent).  
- **Assignation** : combos dynamiques selon le run actif.  
- **Liste des projets** : cartes avec actions *View tasks*, *Edit*, *Delete*.  
- **Templates** : appliquer un template peuple immédiatement les tâches ; tester le rendu de l’alerte de confirmation.

### 4.2 Task Manager (`/projects/{project}/tasks`)

- **Header** : métriques (tâches ouvertes, complétées, collaborateurs).  
- **Board** :
  - cartes tâches avec statut, auteur, assigné, timestamp ;  
  - boutons *Mark as completed*, *Edit*, *Delete* (tous Livewire).  
  - sélection d’assignation inline (`assignmentBuffer`).  
  - formulaire d’édition inline (titre + assigné).  
- **Commentaires** : liste chronologique, formulaire Livewire `commentDrafts`.  
- **Sidebar** : création de tâche + rappel sur la gestion des membres.

## 5. Challenges & Communauté

- **Challenge Index** : catalogue des runs disponibles, boutton pour créer le sien (formulaire modal Livewire).  
- **Challenge Show** : récap participants, leaderboard (streak, shipments), onglet *Insights* pour la synthèse quotidienne (vérifier les graphiques/slots Flux).  
- **Invitations** : page Daily Journal sans run → liste des invitations pendantes avec actions accepter/refuser (`joinWithCode`, `acceptInvite`, `rejectInvite`).

## 6. Notifications & Flux

- **Filament notifications** : utilisées pour les actions (création repo GitHub, erreurs API, succès Livewire).  
- **Outbox** : la table `notifications_outbox` (seeded) agit comme tampon pour les envois asynchrones ; lancer `php artisan queue:work` pour valider les jobs.  
- **Filament/Flux UI** : plusieurs composants partagent le même design (cards arrondies, sections border, badges). Vérifier la cohérence visuelle lors des tests.

## 7. Intégrations

- **GitHub** :
  - besoin d’un token personnel dans `profiles.github_access_token` (pour un vrai test).  
  - sinon, le widget affiche un message invitant à connecter GitHub.  
  - après création, la carte montre l’URL du repo et la visibilité.
- **WakaTime** :
  - section dans *Settings* → saisir une clé pour activer la synchronisation.  
  - vérifier le comportement sans clé (message d’aide).
- **AI Providers** :
  - config dans `.env` (`OPENAI_API_KEY`, `GROQ_API_KEY`).  
  - si absents, les boutons restent visibles mais les jobs peuvent lever des exceptions ; utiliser `APP_DEBUG=true` pour observer les logs pendant la démo.

## 8. Flux quotidiens à démontrer

1. **Démarrer la journée** : consulter le dashboard, lancer le *Daily log*.  
2. **Rédiger un log** : remplir les champs, sauvegarder, attendre la génération AI, copier un draft.  
3. **Mettre à jour un projet** : dans *Project Manager*, créer un projet, appliquer un template, assigner des membres.  
4. **Gérer les tâches** : passer sur le *Task Manager*, ajouter/compléter une tâche, laisser un commentaire.  
5. **Synchroniser GitHub** : dans le widget, créer un repo (si token dispo) ou vérifier le message d’erreur contrôlée.  
6. **Consulter les challenges** : ouvrir la page Challenge, rejoindre via code, observer le leaderboard.  
7. **Clore la journée** : revenir au dashboard, vérifier la mise à jour des statistiques.

## 9. Dépannage rapide

- **Reset data** : `php artisan migrate:fresh --seed`.  
- **Tests automatisés** : `composer test` ou `php artisan test`.  
- **Formatage front** : `bun run format`.  
- **Queue bloquée** : `php artisan queue:restart`.  
- **Problèmes AI** : vérifier `.env` et la table `failed_jobs`.

---

Ce guide peut être partagé aux développeurs qui tiennent le rôle de démonstrateurs / QA lors du lancement. Ajustez selon les features activées sur votre instance (certains modules restent expérimentaux). Bon lancement !
