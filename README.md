# 100DaysOfCode AI Coach

Un coach numérique pour maintenir la cadence #100DaysOfCode : journal quotidien propulsé par l’IA, gestion de projets, intégrations GitHub/WakaTime et rappels multicanaux (Email, Telegram).  
Construit avec **Laravel 12**, **Livewire 3**, **Filament**, et une surcouche IA Groq/OpenAI.

---

## Sommaire

1. [Fonctionnalités clés](#fonctionnalités-clés)  
2. [Architecture & intégrations](#architecture--intégrations)  
3. [Installation & démarrage](#installation--démarrage)  
4. [Télégram & notifications](#télégram--notifications)  
5. [Synchronisation WakaTime](#synchronisation-wakatime)  
6. [Qualité, tests & formatage](#qualité-tests--formatage)  
7. [Déploiement & opérations](#déploiement--opérations)  
8. [Suivi #100DaysOfCode](#suivi-100daysofcode)  
9. [Contribuer](#contribuer)  
10. [Licence](#licence)

---

## Fonctionnalités clés

- **Journal quotidien intelligent**  
  Formulaire Livewire avec suggestions d’IA (résumé Markdown, hashtags, punchline, brouillons LinkedIn/X). Historique, filtrage et édition rétroactive.

- **Streaks, badges & analytics**  
  Dashboard temps réel : séries, jours complétés, heures totales/cette semaine, badges automatiques, leaderboard et tableau de bord Filament pour l’équipe.

- **Gestion de projets et tâches**  
  Runs privés/publics, assignations, commentaires, templates réutilisables, vue Kanban façon Task Manager avec filtres rapides.

- **Rappels multicanaux**  
  Rappels quotidiens timezone-aware (Email + Telegram) déclenchés lorsqu’aucun log n’est présent avant l’heure choisie.

- **Intégrations productivité**  
  - **GitHub** : OAuth, création d’un repository template, lien direct depuis le dashboard.  
  - **WakaTime** : synchronisation quotidienne, masquage optionnel des noms de projets, monitoring des erreurs.  
  - **Telegram** : onboarding par commande `/signup`, liaison automatique du chat via callback, diffusions quotidiennes possibles.

- **Support & feedback**  
  Page support riche (FAQ, ressources), formulaire Livewire, notifications équipe, création d’issues GitHub en option.

---

## Architecture & intégrations

| Couche | Technologies | Notes |
| ------ | ------------ | ----- |
| **Backend** | Laravel 12, PostgreSQL, Redis, Horizon | Jobs queue (rappels, IA, sync WakaTime), scheduler via cron |
| **Frontend** | Blade, Livewire 3, Tailwind / Flux, Filament | UI admin dans `app/Filament`, pages publiques dans `resources/views/livewire/page/` |
| **IA** | Groq Mixtral (par défaut), OpenAI GPT-4o-mini (secours) | Stratégie configurable via `config/ai.php` |
| **Notifications** | Laravel Notifications, canal Telegram custom, outbox `notifications_outbox` | Daily Reminders, Weekly Digest, Support tickets |
| **Intégrations** | GitHub API, WakaTime API, Telegram Bot API | Services encapsulés dans `app/Services/*` |

---

## Installation & démarrage

```bash
git clone git@github.com:jiordiviera/100days-ai-coach.git
cd 100days-ai-coach
composer install
bun install
cp .env.example .env          # pensez à configurer DB, redis, IA, Telegram, GitHub…
php artisan key:generate
php artisan migrate:fresh --seed
composer run dev              # lance artisan serve + queue + vite + tail pail
```

Commandes utiles :

- `php artisan migrate:fresh --seed` : reset SQLite/PostgreSQL local + données de démo.  
- `php artisan queue:work` : worker horizon local (intégré dans `composer run dev`).  
- `php artisan wakatime:sync` : déclenche une synchronisation WakaTime (voir plus bas).  
- `php artisan telegram:set-webhook` : configure le webhook du bot sur l’URL publique.

---

## Télégram & notifications

1. Créez un bot via [@BotFather](https://t.me/BotFather) et renseignez le token dans `TELEGRAM_BOT_TOKEN`.  
2. Configurez l’URL publique (`APP_URL`) et exécutez `php artisan telegram:set-webhook`.  
3. Les utilisateurs peuvent lier leur chat depuis la page `Paramètres` et via la commande `/signup`.  
4. Les notifications `DailyReminderNotification` utilisent le canal Telegram si une entrée active existe dans `notification_channels`.

Tests associés :  
`tests/Feature/Settings/TelegramLinkControllerTest.php`, `tests/Feature/Console/SendDailyRemindersTest.php`.

---

## Synchronisation WakaTime

- Renseignez la clé API dans `Paramètres > Intégrations`.  
- Les métadonnées (dernière synchro, dernière erreur) sont visibles côté admin (`Filament > Utilisateurs`).  
- Commandes :
  - `php artisan wakatime:sync` : exécute immédiatement pour tous les utilisateurs.  
  - `php artisan wakatime:sync --date=2025-10-15` : queue les jobs pour la date donnée (utile en rattrapage).
- Scheduler recommandé (cron) : `0 20 * * * php /path/artisan wakatime:sync`.

Logique métier : `app/Jobs/SyncWakaTimeForUser.php`, tests `tests/Feature/WakaTimeSyncTest.php`.

---

## Qualité, tests & formatage

- **Tests** : `composer test` (PHPUnit + Pest wrappers).  
  - Suites ciblées : `php artisan test --filter=SendDailyRemindersTest`.  
  - Couverture des flows principaux (enregistrement, onboarding, notifications, WakaTime, support).
- **Formatage JS/CSS** : `bun run format` / `bun run format:check`.  
- **Lint PHP** : suivant PSR-12 via Larastan & Pint (scripts disponibles dans `composer.json`).  
- **Logs journaliers** : documenter l’avancement dans `logs/dayNN.md` pour suivre le défi #100DaysOfCode.

---

## Déploiement & opérations

- Pipeline GitHub Actions (`.github/workflows/ci-deploy.yml`)  
  - Construits sur un VPS Hetzner (Ubuntu, Nginx, PostgreSQL).  
  - Commande `bun` doit être disponible côté serveur (`bun install` préalable).  
  - Étapes artisan intégrées : cache config/routes/views, migrations, assets.
- Ansible orchestre la mise à jour du code, la rotation des logs, la configuration des services de queue.  
- Points à surveiller en production :
  - Horizon en fonctionnement (`php artisan horizon`).  
  - Cron Laravel (`* * * * * php artisan schedule:run`).  
  - Log `storage/logs/laravel.log` et `logs/dayNN.md` pour incidents.

---

## Suivi #100DaysOfCode

- Journaliser chaque journée dans `logs/dayNN.md` (pattern Document → Reflect → Plan).  
- Mentionner les évolutions produit, migrations exécutées, incidents, next steps.  
- Exemple de template :

```md
# Day 37 – 2025-10-26
- ✅ Ajout panneau Filament Utilisateurs (rôle admin, WakaTime status)
- 🧪 Tests `composer test`
- 📌 Suivi : déployer la nouvelle commande Telegram
```

---

## Contribuer

1. **Issues** : créer/mettre à jour les tickets GitHub (labels `feature`, `backend`, `ui`, `priority:P0`, …).  
2. **Branches** : nommer suivant le ticket (`feature/telegram-onboarding`).  
3. **PR** : respecter les commits Conventional (`feat:`, `fix:`…), joindre captures Filament/Livewire quand pertinent, faire référence à l’issue.  
4. **Avant merge** : `composer test` + `bun run format:check` OK, documenter `logs/dayNN.md`, noter les risques connus.

---

## Licence

Ce projet est distribué sous licence [MIT](LICENSE).

