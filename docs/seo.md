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

Pensez à invalider le cache SEO (`Cache::forget('public-…')`) si vous modifiez dynamiquement des contenus affichés sur ces pages.
