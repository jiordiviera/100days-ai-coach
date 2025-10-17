# 100DaysOfCode AI Coach

**Liens rapides :** [Vision](#vision) · [Tech Stack](#tech-stack-mvp) · [Roadmap](#roadmap-work-in-progress) · [Admin Panel](#admin-panel) · [Liste des fonctionnalités](docs/features-list.md)

**Work in progress** — This project is being developed publicly as part of my [#100DaysOfCode](https://www.100daysofcode.com/) journey.

The goal is simple: turn the challenge into an **AI-augmented experience** that makes it easier, more motivating, and more social to complete the 100 days.

---

## Vision

Many developers start #100DaysOfCode but drop out quickly due to lack of motivation, writer’s block, or not knowing what to do next.  
**100DaysOfCode AI Coach** is designed to solve these issues by:

- Automatic summaries of your daily logs (Markdown + tags).
- A small AI-generated challenge suggestion for the next day.
- A draft post for LinkedIn/Twitter to share your progress.
- Motivational badges to celebrate milestones.
- Public pages to share your journey with others.

---

## Tech Stack (MVP)

- **Backend**: Laravel 11, Redis/Horizon, PostgreSQL
- **Frontend**: Livewire + TailwindCSS
- **AI Providers**:
    - [Groq](https://groq.com) (Mixtral) – fast and free
    - [OpenAI GPT-4o-mini](https://openai.com) – high-quality fallback
    - (Optional) [Mistral](https://mistral.ai) self-hosted with `llama.cpp` or TGI

---

## Roadmap (Work in Progress)

### v0.1 — MVP
- [ ] Daily log → AI summary + tags
- [ ] Coach Tip (small next-day challenge)
- [ ] Shareable draft (LinkedIn/Twitter)
- [ ] 7-day badge

### v0.2 — Beta
- [ ] Weekly summaries
- [ ] Leaderboard (streak + active days)
- [ ] Multi-language support (EN/FR)

### v1.0 — Future
- [ ] Integration with [Codepit](https://codepit.jiordiviera.me) for code snippets
- [ ] Public API for third-party apps
- [ ] Easy self-hosting (Docker, Kubernetes)

---

## Contributing

This project is **open source** and still in its early stage.
- Follow the daily progress via my [100DaysOfCode log](https://github.com/jiordiviera/100DaysOfCode).
- Issues are open for feedback, feature ideas, or bug reports.
- Pull requests are welcome once the MVP foundation is in place.

---

## License

[MIT](LICENSE) — free to use, modify, and redistribute.  
Created by [Jiordi Viera](https://github.com/jiordiviera) as part of the #100DaysOfCode challenge.

## Admin Panel

L’application embarque un panneau d’administration Filament accessible depuis `/admin`.

- **Accès** : seuls les utilisateurs possédant `is_admin = true` peuvent s’y connecter.  
  Pour promouvoir un compte local existant :

  ```bash
  php artisan tinker
  >>> App\Models\User::where('email', 'test@example.com')->update(['is_admin' => true]);
  ```

- **Fonctionnalités principales** :
  - gestion des utilisateurs, des challenges, des logs et des notifications via les resources Filament existantes ;
  - consultation des métriques IA (latence/coût, taux d’échec) via le dashboard `viewAiMetrics`;
  - accès aux jobs (Horizon), files et paramètres techniques.

- **Authentification** : utilise la même session que l’app principale ; aucune inscription séparée n’est nécessaire.

Pensez à exécuter `php artisan config:cache` ou à redémarrer Horizon après toute modification de rôles pour refléter les droits côté files et métriques.
