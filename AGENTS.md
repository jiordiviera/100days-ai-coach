# Agent Handbook

## Etat d'avancement

### Ce qui a ete fait
- Initialisation du repo #100DaysOfCode, structure des dossiers et demarrage du projet Laravel `task-manager` avec routes et base de donnees connectees.
- Mise en place des flux d'authentification (Livewire + Filament), corrections UI responsive et adoption progressive des composants Flux/Filament sur dashboard, auth et journal quotidien.
- Refonte du domaine Challenge: nouveaux modeles (ChallengeRun, DailyLog, ChallengeParticipant, ChallengeInvitation), migrations associees et vues Livewire pour creation, progression, invitations et calendrier sur 100 jours.
- Modernisation de la gestion des taches et du dashboard avec composants Filament, rafraichissements post-actions et verifications UX completes.
- Renforcement des regles metier autour des challenges: blocage des runs concurrents, alertes UI et validation stricte des invitations.

### A faire
- Ajouter le calcul des streaks (courant vs record) et l'afficher clairement dans le dashboard et les vues challenges.
- Offrir une gestion avancee des invitations (renvoi, revocation) et un selecteur de challenge actif pour les participants multi-runs.
- Generaliser Flux/Filament sur les ecrans restants et maintenir une coherence UI/UX.
- Etendre la couverture de tests automatises (Livewire, validations, invitations) et documenter les scenarios critiques.
- Planifier une campagne QA ciblee sur les flux auth, filtrage des projets et experience journaliere avant de livrer.

## Tech Stack Snapshot

- `logs/` stores the daily #100DaysOfCode journal; keep filenames in the `dayNN.md` pattern so entries stay chronological.
- `laravel-projects/task-manager/` is the active Laravel 12 app. Domain logic sits in `app/`, UI assets in `resources/`, database artifacts in `database/`, and automated checks in `tests/`.
- `nextjs-projects/`, `misc-projects/`, and `resources/` are placeholders—spin up a subfolder per project and add a README when work begins.

## Build, Test, and Development Commands

- First-time setup: `cd laravel-projects/task-manager && composer install && bun install` to sync PHP and frontend dependencies.
- Local dev stack: `composer run dev` launches the PHP server, queue listener, log tail (Pail), and Vite watcher together.
- Database refresh: `php artisan migrate:fresh --seed` resets the bundled SQLite database in `database/database.sqlite`.

## Coding Style & Naming Conventions

- PHP code follows PSR-12 with 4-space indentation; run `php vendor/bin/pint` before committing.
- Frontend assets rely on Prettier; keep `bun run format` and `bun run format:check` green.
- Livewire components live under `App\\Livewire\\` in StudlyCase (`ChallengeDashboard`); Blade views stay lowercase-kebab (`resources/views/challenges/show.blade.php`).
- Daily logs begin with `# Day X/100` and concise bullet summaries to match existing entries.

## Testing Guidelines

- Feature and unit tests live in `tests/Feature` and `tests/Unit`; keep filenames suffixed with `Test.php` and method names descriptive (`test_user_can_start_challenge`).
- Run the suite with `composer test` (clears cached config) or `php artisan test` for quick feedback, leaning on Pest conventions.
- Cover new Livewire components and validation rules to protect the challenge tracker workflow.

## Commit & Pull Request Guidelines

- Follow the existing Git history: prefix summaries with a conventional type (`feat:`, `fix:`, `chore:`) and add body bullets when touching several areas.
- Reference related log updates or issues in the PR description and include screenshots or clips for UI tweaks.
- Ensure formatters and tests pass locally before opening a PR to keep review cycles tight.

## Documentation & Knowledge Sharing

- Update `logs/dayNN.md` alongside functional work so the challenge narrative stays synchronized.
- Before ending the day, append an English entry to `logs/dayNN.md` that captures the day's challenge progress and reflection.
