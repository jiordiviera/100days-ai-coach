<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GitHubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['read:user', 'user:email', 'read:org', 'repo'])
            ->with(['allow_signup' => 'false'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $providerUser = Socialite::driver('github')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')
                ->withErrors(['email' => 'Impossible de se connecter avec GitHub pour le moment.']);
        }

        if (! $providerUser->getEmail()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'GitHub n’a pas fourni d’adresse e-mail vérifiée.']);
        }

        $accessToken = $providerUser->token;
        $refreshToken = $providerUser->refreshToken;
        $expiresIn = $providerUser->expiresIn;

        $user = $this->resolveUser($providerUser, $accessToken, $refreshToken, $expiresIn);

        auth()->login($user, remember: true);

        return redirect()->intended(route('daily-challenge'));
    }

    protected function resolveUser(ProviderUser $providerUser, ?string $accessToken = null, ?string $refreshToken = null, ?int $expiresIn = null): User
    {
        $user = User::whereHas('profile', function ($query) use ($providerUser) {
            $query->where('github_id', $providerUser->getId());
        })->first();

        if (! $user) {
            $user = User::where('email', $providerUser->getEmail())->first();
        }

        if (! $user) {
            $user = User::create([
                'name' => $providerUser->getName() ?: ($providerUser->getNickname() ?: $providerUser->getEmail()),
                'email' => $providerUser->getEmail(),
                'password' => Str::password(40),
            ]);
        }

        $profile = $user->profile;

        $preferredUsername = $providerUser->getNickname() ? Str::of($providerUser->getNickname())->lower()->slug()->value() : null;

        if (! $profile) {
            $profile = $user->profile()->create([
                'join_reason' => 'github_oauth',
                'focus_area' => null,
                'username' => $preferredUsername,
                'github_id' => $providerUser->getId(),
                'github_username' => $providerUser->getNickname(),
                'preferences' => $user->profilePreferencesDefaults(),
            ]);
        } else {
            $updates = [
                'github_id' => $providerUser->getId(),
                'github_username' => $providerUser->getNickname(),
            ];

            if (! $profile->username && $preferredUsername) {
                $updates['username'] = $preferredUsername;
            }

            if (! $profile->preferences) {
                $updates['preferences'] = $user->profilePreferencesDefaults();
            }

            $profile->forceFill($updates)->save();
        }

        $tokenPayload = [];

        if ($accessToken) {
            $tokenPayload['github_access_token'] = $accessToken;
        }

        if ($refreshToken) {
            $tokenPayload['github_refresh_token'] = $refreshToken;
        }

        if ($expiresIn) {
            $tokenPayload['github_token_expires_at'] = Carbon::now()->addSeconds($expiresIn);
        }

        if ($tokenPayload) {
            $profile->forceFill($tokenPayload)->save();
        }

        return $user;
    }
}
