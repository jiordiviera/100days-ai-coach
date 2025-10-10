<?php

use App\Livewire\Page\DailyChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders onboarding empty state when no challenge run', function (): void {
    $user = User::factory()->create();

    $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    $this->actingAs($user)
        ->get(route('daily-challenge'))
        ->assertOk()
        ->assertSee("Créer mon challenge", false)
        ->assertSee('Rejoindre via un code', false);

    Livewire::actingAs($user)
        ->test(DailyChallenge::class)
        ->assertSet('challengeRunId', null);
});
