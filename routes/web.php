<?php

use App\Http\Controllers\PublicChallengeController;
use App\Http\Controllers\PublicDailyLogController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\PublicSitemapController;
use App\Livewire\Onboarding\Wizard as OnboardingWizard;
use App\Livewire\Page\ChallengeIndex;
use App\Livewire\Page\ChallengeInsights;
use App\Livewire\Page\ChallengeShow;
use App\Livewire\Page\DailyChallenge;
use App\Livewire\Page\Dashboard;
use App\Livewire\Page\Leaderboard;
use App\Livewire\Page\ProjectManager;
use App\Livewire\Page\Support;
use App\Livewire\Page\TaskManager;
use App\Livewire\Page\Welcome;
use App\Models\ChallengeInvitation;
use App\Models\ChallengeRun;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('home');
Route::get('share/{token}', [PublicDailyLogController::class, 'show'])->name('logs.share');
Route::get('profiles/{username}', PublicProfileController::class)->name('public.profile');
Route::get('challenges/public/{slug}', PublicChallengeController::class)->name('public.challenge');
Route::get('sitemap.xml', PublicSitemapController::class)->name('public.sitemap');
Route::get('support', Support::class)->name('support');

Route::middleware('auth')->group(function () {
    Route::get('onboarding', OnboardingWizard::class)->name('onboarding.wizard');
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::post('logout', function () {
        Auth::logout();

        return redirect()->route('home');
    })->name('logout');
    // Routes Livewire pour la gestion des projets et des tâches
    Route::get('projects', ProjectManager::class)->name('projects.index');
    Route::get('projects/{project}/tasks', TaskManager::class)->name('projects.tasks.index');

    // Challenges 100DoC
    Route::get('challenges', ChallengeIndex::class)->name('challenges.index');
    Route::get('challenges/{run}', ChallengeShow::class)->name('challenges.show');
    Route::get('challenges/{run}/insights', ChallengeInsights::class)->name('challenges.insights');
    Route::get('challenge/daily', DailyChallenge::class)->name('daily-challenge');
    Route::get('leaderboard', Leaderboard::class)->name('leaderboard');
    Route::get('challenges/invite/{token}', function (string $token) {
        $inv = ChallengeInvitation::with('run')->where('token', $token)->firstOrFail();
        // Expiration
        if ($inv->expires_at && now()->greaterThan($inv->expires_at)) {
            abort(410, 'Invitation expirée');
        }
        if (! Auth::check()) {
            return Redirect::guest(route('login'));
        }

        $run = $inv->run;
        $user = Auth::user();

        $hasAnotherActiveRun = ChallengeRun::query()
            ->where('status', 'active')
            ->where('id', '!=', $run->id)
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn ($participantQuery) => $participantQuery->where('user_id', $user->id));
            })
            ->exists();

        if ($hasAnotherActiveRun) {
            return Redirect::route('challenges.index')
                ->with('message', 'Tu participes déjà à un autre challenge actif. Termine-le avant de rejoindre ce run.');
        }

        $exists = $run->participantLinks()->where('user_id', Auth::id())->exists();
        if (! $exists) {
            $run->participantLinks()->create([
                'user_id' => Auth::id(),
                'joined_at' => now(),
            ]);
        }

        if (! $inv->accepted_at) {
            $inv->forceFill(['accepted_at' => now()])->save();
        }

        if (! Auth::user()->profile()->exists()) {
            Auth::user()->profile()->create([
                'join_reason' => 'invited',
                'focus_area' => null,
                'preferences' => [
                    'origin' => 'invitation-link',
                    'invitation_id' => $inv->id,
                    'challenge_run_id' => $run->id,
                ],
            ]);
        }

        return Redirect::route('challenges.show', ['run' => $run->id])
            ->with('message', 'Vous avez rejoint le challenge !');
    })->name('challenges.accept');
});

require __DIR__.'/auth.php';
