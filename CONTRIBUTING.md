# Contributing to 100DaysOfCode AI Coach

First off, thank you for taking the time to contribute! 🚀  
This project is being built in public as part of the [#100DaysOfCode](https://www.100daysofcode.com/) challenge.  
All kinds of contributions are welcome: reporting issues, suggesting features, improving documentation, or submitting pull requests.

---

## 📌 How to Contribute

### 1. Fork & Clone
```bash
git clone https://github.com/jiordiviera/100days-ai-coach.git
cd 100days-ai-coach
````

### 2. Setup

Install dependencies and set up the project locally:

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### 3. Branching

Create a feature branch:

```bash
git checkout -b feature/your-feature-name
```

### 4. Commit Style

Follow clear commit messages:

```
feat: add AI summary for daily logs
fix: correct bug in leaderboard query
docs: update README with setup steps
```

### 5. Submit a Pull Request

* Push your branch to your fork.
* Open a PR against the `main` branch.
* Describe clearly what your PR does.

---

## 🐛 Issues

* Use **Issues** to report bugs, suggest features, or request clarifications.
* When reporting a bug, please provide steps to reproduce it and your environment details.

---

## 🧑‍💻 Development Guidelines

* Code style: PSR-12 for PHP, Prettier for JS/Blade.
* Run `php artisan test` before pushing.
* Keep PRs small and focused (1 feature/fix per PR).

---

## 🤝 Community

This project is still in early development.
Feedback, discussions, and ideas are very welcome — feel free to open a discussion or ping in Issues.

---

## 📜 License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
