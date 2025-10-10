# Couche SEO

Ce projet utilise [archtechx/laravel-seo](https://github.com/ArchTechX/laravel-seo) pour centraliser les balises meta (OpenGraph, Twitter, etc.).

## Principes

- Les valeurs par défaut sont définies dans `App\Providers\SeoServiceProvider` (titre, description, url canonique, extensions Twitter).
- Le layout principal inclut désormais `<x-seo::meta />` et se base sur `@seo('title')` pour le `<title>`.
- Appelez le helper `seo()` depuis vos composants Livewire, contrôleurs ou jobs pour personnaliser les metas d’une page.

```php
seo()
    ->title('Titre de la page')
    ->description('Description optimisée (≤ 160 caractères)')
    ->tag('og:type', 'article');
```

- Pour les vues Blade autonomes (ex: pages publiques), assurez-vous d’inclure `@seo` si vous devez surcharger le titre manuellement.

## Pages couvertes

- Accueil, partages publics (`/share/{token}`)
- Profils/challenges publics (`/profiles/{username}`, `/challenges/public/{slug}`)

Les caches correspondants (`public-profile:{username}`, `public-challenge:{slug}`) sont invalidés automatiquement lors des opérations critiques (mise à jour challenge, génération de log). Si vous ajoutez d’autres surfaces publiques, pensez à purger les clés concernées.

Des tests d’intégration (`tests/Feature/Public`) vérifient la présence des balises SEO principales sur ces routes afin d’éviter les régressions.
