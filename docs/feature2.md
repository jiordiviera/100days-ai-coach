# 100DaysOfCode IA Coach — Spécifications Fonctionnelles Globales

## 1. Contexte & Objectifs

**Contexte :** 100DaysOfCode IA Coach est une application visant à accompagner les développeurs dans leur défi de 100 jours de code grâce à une combinaison de suivi automatisé (WakaTime), d’IA pour résumer et coacher, et d’intégrations sociales comme GitHub pour la structure des journaux.

**Objectifs :**

* Automatiser la collecte de données de codage réelles (temps, langages, projets).
* Permettre un suivi quotidien simple et motivant.
* Offrir un accompagnement IA personnalisé (résumés, conseils, mini-défis, posts prêts à partager).
* Proposer un onboarding rapide via GitHub pour démarrer avec un template de repository.
* Favoriser la gamification et la motivation par badges, streaks et partages sociaux.

---

## 2. Personas Utilisateurs

1. **Développeur individuel** : veut suivre son progrès et rester motivé.
2. **Étudiant** : découvre le code et veut être guidé par un coach virtuel.
3. **Team challengers** : plusieurs développeurs veulent partager et comparer leurs progrès.

---

## 3. Fonctionnalités Clés

### 3.1 Intégration WakaTime

* **Connexion via clé API (MVP)** : saisie d’une clé API personnelle dans le profil utilisateur.
* **Option OAuth (évolution)** : connexion simplifiée et sécurisée pour éviter la gestion manuelle des clés.
* **Collecte des données** :

  * Temps de codage journalier.
  * Langages utilisés.
  * Projets/éditeurs principaux.
* **Synchronisation** : job planifié (cron) qui interroge l’API WakaTime chaque jour.
* **Confidentialité** : option de masquer noms des projets.

### 3.2 Daily Logs & IA Coach

* Variables entrantes :

  * Jour N du challenge.
  * Données WakaTime (ou entrée manuelle si absent).
  * Notes utilisateur.
  * Streak actuel.
* Sorties IA :

  * Résumé du jour (summary_md).
  * Tags techniques.
  * Coach_tip (mini-défi < 1h).
  * Share_draft (brouillon réseaux sociaux).
* Gestion des états :

  * Avec WakaTime (succès).
  * Sans WakaTime (manuel).
  * Erreur (clé invalide, API down).
  * Mixte (partiellement dispo).

### 3.3 Gamification

* **Streaks** : suivi de la constance.
* **Badges** :

  * Régularité (7 jours, 30 jours, etc.).
  * Diversité (multi-langages, multi-projets).
  * Longévité (temps total).
* **Alertes** : encouragements si baisse d’activité.

### 3.4 Onboarding GitHub Template

* **Connexion OAuth GitHub (obligatoire en production)** : l’inscription et la connexion ne se font qu’avec GitHub pour simplifier et centraliser l’authentification.
* **Clonage d’un template public** :

  * README d’introduction.
  * Dossiers par jour (`day-01`, `day-02`, ...).
  * Templates d’issues GitHub (Day N log).
  * Workflows Actions optionnels (vérification automatique des logs).
* **Flow utilisateur** :

  * Première connexion → proposer création d’un repo à partir du template.
  * Personnalisation : nom du repo, visibilité, org ou perso.
  * Post-création : CTA vers le repo et option de synchronisation avec l’app.

---

## 4. Flux Utilisateurs

### 4.1 Onboarding

1. Connexion via GitHub (obligatoire en production).
2. Proposition d’importer un repo template.
3. Ajout clé API WakaTime (optionnel mais recommandé).
4. Premier daily log généré.

### 4.2 Utilisation quotidienne

1. Synchronisation automatique WakaTime.
2. Génération IA des résumés et tips.
3. Validation/édition par l’utilisateur.
4. Partage possible sur GitHub, Twitter, LinkedIn.

---

## 5. Données & Modèles

### 5.1 Utilisateur

* id, name, email, provider_id (GitHub), wakatime_api_key (encrypté), timezone.

### 5.2 DailyLog

* id, user_id, day_number.
* summary_md, tags, coach_tip, share_draft.
* ai_metadata (modèle, latence, coût).
* wakatime_data (json : temps, langages, projets).

### 5.3 RepoTemplate

* id, user_id, repo_url, created_at.

---

## 6. Cas Limites & Gestion des erreurs

* WakaTime absent → fallback manuel.
* Clé invalide → message clair et réessai.
* API down → conservation du streak par note manuelle.
* Repo GitHub déjà existant → renommer ou ignorer.
* Si l’utilisateur tente une inscription hors GitHub en production → bloqué, message expliquant que seule la connexion GitHub est supportée.

---

## 7. Confidentialité & Sécurité

* Clés API chiffrées en base.
* Jamais loggées ni affichées.
* Suppression volontaire possible.
* Option masquer noms de projets dans IA/partages.

---

## 8. Roadmap par Phases

**Phase 1 (MVP)** :

* Daily logs + IA coach.
* Intégration WakaTime via clé API.
* Template GitHub clonable.
* Auth obligatoire GitHub en production.

**Phase 2** :

* Gamification (badges, streaks).
* Partages sociaux automatiques.
* Tableaux de bord full Laravel stack (Blade, Livewire, Alpine, Tailwind).

**Phase 3** :

* OAuth WakaTime + GitHub avancé.
* Coach collaboratif (équipes, leaderboard).
* Historique complet et import rétroactif.

---

## 9. Critères d’acceptation

* Un utilisateur peut créer un compte uniquement via GitHub (en production).
* Il peut cloner le template de repo.
* Il peut saisir sa clé WakaTime et voir ses temps de code apparaître.
* L’IA génère chaque jour un résumé, tags, tip et draft.
* Le système fonctionne avec ou sans WakaTime.
* Les données sont sécurisées et l’expérience motivante.
