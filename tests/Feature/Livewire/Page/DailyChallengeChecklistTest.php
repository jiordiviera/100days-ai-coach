<?php

use App\Livewire\Page\DailyChallenge;
use App\Models\ChallengeRun;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('marks checklist items after logging and sharing', function (): void {
    $user = User::factory()->create([
        'needs_onboarding' => false,
    ]);

    $profile = $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'start_date' => now()->toDateString(),
    ]);

    $project = Project::create([
        'name' => 'Projet test',
        'user_id' => $user->id,
        'challenge_run_id' => $run->id,
    ]);

    Livewire::actingAs($user)
        ->test(DailyChallenge::class)
        ->set('dailyForm.description', 'Notes du jour')
        ->set('dailyForm.hours_coded', 2)
        ->set('dailyForm.projects_worked_on', [$project->id])
        ->call('saveEntry')
        ->call('enablePublicShare');

    $preferences = $profile->fresh()->preferences;

    expect(data_get($preferences, 'onboarding.checklist.first_log'))->toBeTrue();
    expect(data_get($preferences, 'onboarding.checklist.project_linked'))->toBeTrue();
    expect(data_get($preferences, 'onboarding.checklist.public_share'))->toBeTrue();
});
