<?php

use App\Livewire\Onboarding\DailyChallengeTour;
use App\Models\ChallengeRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('marks onboarding tour as completed', function (): void {
    $user = User::factory()->create([
        'needs_onboarding' => false,
    ]);

    $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    ChallengeRun::factory()->for($user, 'owner')->create([
        'status' => 'active',
    ]);

    Livewire::actingAs($user)
        ->test(DailyChallengeTour::class)
        ->set('visible', true)
        ->call('finish')
        ->assertSet('visible', false);

    expect(data_get($user->profile->fresh()->preferences, 'onboarding.tour_completed'))->toBeTrue();
});
