<?php

use App\Livewire\Page\ChallengeIndex;
use App\Livewire\Page\ChallengeInsights;
use App\Livewire\Page\ChallengeShow;
use App\Livewire\Page\DailyChallenge;
use App\Livewire\Page\Dashboard;
use App\Livewire\Page\ProjectManager;
use App\Livewire\Page\TaskManager;
use App\Livewire\Page\Welcome;
use App\Models\ChallengeInvitation;
use App\Models\ChallengeRun;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('logout', function () {
        auth()->logout();

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
    Route::get('challenges/invite/{token}', function (string $token) {
        $inv = ChallengeInvitation::with('run')->where('token', $token)->firstOrFail();
        // Expiration
        if ($inv->expires_at && now()->greaterThan($inv->expires_at)) {
            abort(410, 'Invitation expirée');
        }
        if (! auth()->check()) {
            return Redirect::guest(route('login'));
        }

        $run = $inv->run;
        $user = auth()->user();

        $hasAnotherActiveRun = ChallengeRun::query()
            ->where('status', 'active')
            ->where('id', '!=', $run->id)
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn($participantQuery) => $participantQuery->where('user_id', $user->id));
            })
            ->exists();

        if ($hasAnotherActiveRun) {
            return Redirect::route('challenges.index')
                ->with('message', 'Tu participes déjà à un autre challenge actif. Termine-le avant de rejoindre ce run.');
        }

        $exists = $run->participantLinks()->where('user_id', auth()->id())->exists();
        if (! $exists) {
            $run->participantLinks()->create([
                'user_id' => auth()->id(),
                'joined_at' => now(),
            ]);
        }

        if (! $inv->accepted_at) {
            $inv->forceFill(['accepted_at' => now()])->save();
        }

        if (! auth()->user()->profile()->exists()) {
            auth()->user()->profile()->create([
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
