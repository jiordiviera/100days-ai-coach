<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SeoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! function_exists('seo')) {
            return;
        }

        $appName = config('app.name', '#100DaysOfCode');
        $defaultDescription = 'Suivez votre progression #100DaysOfCode, partagez vos logs publics et collaborez avec la communauté.';

        seo()
            ->title(default: $appName)
            ->description(default: $defaultDescription)
            ->tag('og:site_name', $appName)
            ->locale(app()->getLocale())
            ->withUrl()
            ->twitter();
    }
}
