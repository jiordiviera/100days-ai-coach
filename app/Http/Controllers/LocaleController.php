<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $available = config('app.available_locales', ['en', 'fr']);

        $data = $request->validate([
            'locale' => ['required', 'in:'.implode(',', $available)],
        ]);

        $locale = $data['locale'];

        session(['locale' => $locale]);

        if (Auth::check()) {
            $user = $request->user();
            $profile = $user->profile;

            if ($profile) {
                $preferences = $profile->preferences ?? $user->profilePreferencesDefaults();
                Arr::set($preferences, 'language', $locale);

                $profile->forceFill(['preferences' => $preferences])->save();
            }
        }

        return back();
    }
}
