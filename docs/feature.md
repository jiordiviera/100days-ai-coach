# Spécification Fonctionnalités — 100DaysOfCode·AI Coach (priorisées)

## Connaissance

* **Stack** : Laravel 12, Redis/Horizon, PostgreSQL.
* **IA** : Groq (par défaut), OpenAI (fallback), Local Mistral (option).
* **Schéma** : `daily_logs` enrichi (IA), `user_profiles` pour profil, `notifications_outbox` pour journalisation des envois.

---

## P0 — MVP

### F1. Saisie du Daily Log + Génération IA

**Description**
Création d’un log quotidien → job IA asynchrone génère `summary_md`, `tags`, `coach_tip`, `share_draft`.

**Règles**

* 1 log/jour/utilisateur (`unique(challenge_run_id, user_id, day_number)`).
* 1 régénération/jour/log.

**Données**

* `daily_logs.summary_md` TEXT NULL
* `daily_logs.tags` JSON NULL
* `daily_logs.coach_tip` TEXT NULL
* `daily_logs.share_draft` TEXT NULL
* `daily_logs.ai_model` VARCHAR(64) NULL
* `daily_logs.ai_latency_ms` INT NULL
* `daily_logs.ai_cost_usd` DECIMAL(6,3) DEFAULT 0

**Critères d’acceptation**

* < 30 s après création du log, les 4 champs IA sont persistés.
* En cas d’échec IA, le log reste valide et réessayable.

**Métriques**

* Latence p95 du job IA, taux d’échec, coût estimé/jour.

---

### F2. Onboarding minimal + Premier log immédiat

**Description**
Inscription rapide (email + password + pseudo facultatif), création possible du Jour 1 immédiatement.

**Règles**

* Préférences par défaut en `user_profiles.preferences` :

  ```json
  {
    "language": "en",
    "timezone": "Africa/Douala",
    "reminder_time": "20:30",
    "channels": { "email": true, "slack": false, "push": false },
    "notification_types": { "daily_reminder": true, "weekly_digest": true },
    "ai_provider": "groq",
    "tone": "neutral"
  }
  ```

**Données**

* `user_profiles.preferences` JSON (cast array)

**Critères d’acceptation**

* Un nouvel utilisateur peut créer un log en < 60 s après signup.

---

### F3. UI — Affichage progressif + Copier le partage

**Description**
Placeholders tant que l’IA n’a pas répondu. Bouton pour copier `share_draft`.

**Critères d’acceptation**

* Aucun blocage de l’UI si IA lente.
* Bouton Copier restitue `share_draft` sans perte de format.

---

### F4. Badges — Streak 7 jours

**Description**
Attribution d’un badge à 7 jours consécutifs avec punchline IA.

**Données**

* `user_badges` (`user_id`, `badge_key='streak_7'`, `meta` JSON)

**Critères d’acceptation**

* Badge et punchline visibles dès J+7 consécutif.

---

### F5. Notifications — Rappel quotidien (Laravel Notifications + Outbox)

**Description**
Rappel email quotidien TZ-aware aux utilisateurs sans log du jour. Traçabilité via outbox.

**Règles**

* max 1 rappel/jour/user
* pas d’envoi si log du jour existe
* envoi à `preferences.reminder_time` selon `preferences.timezone`
* respect des opt-in `preferences.notification_types.daily_reminder` et `preferences.channels.email`

**Données**

* Table `notifications_outbox` :

    * `id` ULID (PK)
    * `user_id` FK → users (cascade)
    * `type` string (`daily_reminder`, `weekly_digest`, …)
    * `channel` string (`mail`, `slack`, `push`)
    * `payload` JSON NULL
    * `scheduled_at` timestamptz NULL
    * `sent_at` timestamptz NULL
    * `status` string default `queued` (`queued|sent|failed|skipped`)
    * `error` TEXT NULL
    * index `(user_id, status, scheduled_at)`

**Critères d’acceptation**

* Rappel émis à l’heure locale définie, jamais plus d’un en 24 h.
* Outbox reflète le statut réel (`sent|failed`), avec `error` en cas d’échec.

**Métriques**

* Rappels envoyés/jour, taux de retour (log dans les 12 h), taux d’échec envoi.

---

## P1 — Beta courte

### F6. Page publique (token par log)

**Description**
Lien public lecture seule d’un log.

**Données**

* `daily_logs.public_token` CHAR(26) UNIQUE NULL

**Critères d’acceptation**

* Accès public via `/share/{token}` sans data sensible autre que le contenu du log.

---

### F7. Leaderboard mixte (Streak & Jours actifs)

**Description**
Classement par `streak_current` et `days_active_total`.

**Règles**

* streak = jours consécutifs
* days_active_total = jours loggés (non nécessairement consécutifs)

**Critères d’acceptation**

* Tri correct sur les deux colonnes, pas de N+1.

---

### F8. Groupes privés (V1)

**Description**
Création/adhésion à un groupe par code d’invitation, leaderboard interne.

**Critères d’acceptation**

* Invitation par code, visibilité des membres et de leurs stats.

---

### F9. Digest hebdomadaire (IA)

**Description**
Résumé IA de la semaine, envoyé par email si opt-in.

**Critères d’acceptation**

* Envoi le dimanche local, contenu synthétique cohérent (pas d’hallucinations manifestes).

---

## P2 — Itérations utiles

### F10. Préférences utilisateur (UI)

**Description**
Écran paramètres (langue, timezone, reminder_time, ai_provider, tone, toggles notifs).

**Critères d’acceptation**

* Changements appliqués aux envois dès le lendemain.

---

### F11. Multi-providers IA (switch + fallback)

**Description**
`AiClient` avec drivers `groq|openai|local` et fallback automatique.

**Critères d’acceptation**

* Échec provider A → B prend le relais sans 5xx côté utilisateur.

---

### F12. Rétro-complétion limitée

**Description**
Autoriser J-1/J-2, marquage `retro=true`.

**Critères d’acceptation**

* Les streaks et compteurs restent cohérents.

---

## P3 — Différenciants

### F13. Badges étendus + Comeback

**Description**
14/30/50/100 jours, badge “Comeback” après ≥3 jours off.

---

### F14. Intégrations sociales (formatage)

**Description**
Templates prêts à coller pour LinkedIn/X.

---

### F15. Pages publiques profil/groupe (SEO soft)

**Description**
Profil public (streak max, actifs, derniers logs publics) + page groupe.

---

## Données & Modèle — Extensions profil

### Pseudo (username)

**Stockage**

* `user_profiles.username` VARCHAR(32) UNIQUE NULL
* Validation : `alpha_dash`, 3–32, unique (normalisé minuscule/slug)

### Liens sociaux

**Stockage**

* `user_profiles.social_links` JSON NULL (cast array)

  ```json
  {
    "github": "https://github.com/…",
    "twitter": "https://x.com/…",
    "linkedin": "https://www.linkedin.com/in/…",
    "website": "https://…"
  }
  ```
* `user_profiles.avatar_url` VARCHAR NULL
* `user_profiles.bio` VARCHAR(160) NULL

**Critères d’acceptation**

* URLs validées côté serveur, affichage conditionnel si présent.

---

## Index & Intégrité

* `daily_logs`

    * UNIQUE `(challenge_run_id, user_id, day_number)`
    * INDEX `(challenge_run_id, user_id, created_at)`
    * GIN sur `tags` (si requêtes fréquentes)

* `challenge_participants`

    * UNIQUE `(challenge_run_id, user_id)`

* `notifications_outbox`

    * INDEX `(user_id, status, scheduled_at)`

---

## Métriques clés

* Activation J1 ≥ 70%
* Rétention D7 ≥ 35%
* Partages ≥ 25% des logs
* % atteignant streak 7 ≥ 20%
* Coût IA / user / mois ≤ 0,03 $

---

## Risques & Parades

* Latence/échec IA → async + fallback + messages UX clairs
* Abandon J10–J15 → rappels, mini-challenges plus courts, badge Comeback
* Coûts IA → Groq prioritaire, limiter régénérations
* Sécurité → tokens partage, aucune donnée sensible publique
