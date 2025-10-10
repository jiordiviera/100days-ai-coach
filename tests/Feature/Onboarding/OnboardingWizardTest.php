<?php

use App\Livewire\Onboarding\Wizard;
use App\Models\ChallengeRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('redirects users needing onboarding away from protected pages', function (): void {
    $user = User::factory()->create([
        'needs_onboarding' => true,
    ]);

    $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    $this->actingAs($user)
        ->get(route('daily-challenge'))
        ->assertRedirect(route('onboarding.wizard'));
});

it('completes the onboarding wizard and creates a challenge', function (): void {
    $user = User::factory()->create([
        'needs_onboarding' => true,
        'name' => 'New Maker',
    ]);

    $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    Livewire::actingAs($user)
        ->test(Wizard::class)
        ->set('data.username', 'new-maker')
        ->set('data.focus_area', 'Apprendre Laravel')
        ->set('data.timezone', 'Europe/Paris')
        ->call('submit')
        ->set('data.challenge_title', 'Mon défi IA')
        ->set('data.challenge_description', 'Shipping quotidien')
        ->set('data.challenge_start_date', now()->toDateString())
        ->set('data.challenge_target_days', 50)
        ->call('submit')
        ->set('data.reminder_time', '18:00')
        ->set('data.channels', ['email'])
        ->call('submit')
        ->assertRedirect(route('daily-challenge'));

    $user->refresh();
    $profile = $user->profile->fresh();

    expect($user->needs_onboarding)->toBeFalse();
    expect($profile->username)->toBe('new-maker');
    expect(data_get($profile->preferences, 'timezone'))->toBe('Europe/Paris');
    expect(data_get($profile->preferences, 'reminder_time'))->toBe('18:00');
    expect(data_get($profile->preferences, 'channels.email'))->toBeTrue();

    $run = ChallengeRun::where('owner_id', $user->id)->first();

    expect($run)->not()->toBeNull();
    expect($run->title)->toBe('Mon défi IA');
    expect($run->target_days)->toBe(50);
});
