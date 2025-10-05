<?php

use App\Livewire\Page\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('persists wakatime api key and preferences', function (): void {
    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    $this->actingAs($user);

    Livewire::test(Settings::class)
        ->set('data.integrations.wakatime_api_key', 'waka_'.Str::random(10))
        ->set('data.integrations.wakatime_hide_project_names', false)
        ->call('save');

    $profile->refresh();

    expect($profile->wakatime_api_key)->not()->toBeNull()
        ->and($profile->wakatime_settings['hide_project_names'])->toBeFalse()
        ->and($profile->preferences['wakatime']['hide_project_names'])->toBeFalse();
});

it('removes wakatime api key when requested', function (): void {
    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
        'wakatime_api_key' => 'existing-key',
        'wakatime_settings' => ['hide_project_names' => true],
    ]);

    $this->actingAs($user);

    Livewire::test(Settings::class)
        ->set('data.integrations.wakatime_remove_key', true)
        ->call('save');

    $profile->refresh();

    expect($profile->wakatime_api_key)->toBeNull();
});
