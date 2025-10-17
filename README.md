# 100DaysOfCode AI Coach

Application Laravel/Livewire qui accompagne un défi #100DaysOfCode : journal quotidien, IA pour résumer, gestion de projets/tasks et intégrations GitHub/WakaTime.

---

## Stack technique

- **Backend** : Laravel 12, Redis, Horizon, PostgreSQL
- **Frontend** : Blade, Livewire, TailwindCSS
- **IA** : Groq (Mixtral) par défaut, OpenAI GPT-4o-mini en secours
- **Intégrations** : GitHub OAuth + template de repository, WakaTime (clé API personnelle)

---

## Fonctionnalités livrées

- **Daily Challenge** : formulaire structuré, génération IA (résumé Markdown, tags, coach tip, brouillons LinkedIn/X), édition du log du jour, notifications visuelles pendant la génération.
- **Streak & stats** : cartes de progression, historique récent, heures totales/cette semaine, avancement vs objectif du challenge.
- **Onboarding** : wizard en trois étapes (profil public, configuration du challenge, rappels quotidiens) + tour guidé interactif sur la page journal.
- **Projets & tâches** : création de projets, application de templates, assignations, commentaires, complétion, vue Task Manager avec filtres rapides.
- **Challenges** : catalogue des runs, création de run privé, invitations par code/lien, leaderboard et panneau Insights.
- **GitHub** : provisionnement d’un repository template, mémorisation du repo lié, actions rapides depuis le dashboard.
- **WakaTime** : synchronisation quotidienne (heures codées, projets), gestion des clés, notifications en cas d’erreur, option de masquage des noms de projets.
- **Rappels quotidiens** : notifications e-mail timezone-aware déclenchées si aucun log n’est créé avant l’heure de rappel.
- **Partage public** : génération/expiration d’un lien en lecture seule pour un log, boutons de copie, drafts réseaux sociaux prêts à diffuser.
- **Support** : page d’aide, formulaire feedback (Livewire) avec création optionnelle d’issues GitHub, notifications utilisateur.

---

## Contribuer

- Consulter ce dépôt public : <https://github.com/jiordiviera/100days-ai-coach>.
- Ouvrir des issues pour feedbacks ou signaler des bugs.
- Proposer des PR après avoir reproduit localement (`composer install`, `bun install`, `php artisan migrate:fresh --seed`, `composer run dev`).

---

## Licence

[MIT](LICENSE)
