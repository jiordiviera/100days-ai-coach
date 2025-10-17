<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = config('app.available_locales', ['en', 'fr']);
        $default = config('app.locale', 'en');

        $locale = session('locale', $default);

        if (Auth::check()) {
            $preferences = Auth::user()->profile?->preferences ?? [];
            $preferred = Arr::get($preferences, 'language');

            if ($preferred && in_array($preferred, $available, true)) {
                $locale = $preferred;
                session(['locale' => $locale]);
            }
        }

        if (! session()->has('locale') && ! Auth::check()) {
            $browserLocale = $request->getPreferredLanguage($available);
            if ($browserLocale) {
                $locale = $browserLocale;
                session(['locale' => $browserLocale]);
            }
        }

        if (! in_array($locale, $available, true)) {
            $locale = $default;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
