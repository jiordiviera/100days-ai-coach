<?php

use App\Livewire\Page\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createUserWithProfileDefaults(): User
{
    $user = User::factory()->create();

    $user->profile()->create([
        'join_reason' => 'self_onboarding',
        'focus_area' => null,
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    return $user;
}

test('settings page updates user preferences', function (): void {
    $user = createUserWithProfileDefaults();

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->set('data.profile.name', 'Ada Lovelace')
        ->set('data.profile.username', 'ada-heroine')
        ->set('data.profile.focus_area', 'Créer un agent IA')
        ->set('data.profile.bio', 'First programmer, still shipping')
        ->set('data.profile.social_links.github', 'https://github.com/ada')
        ->set('data.profile.social_links.twitter', 'https://x.com/ada')
        ->set('data.profile.social_links.linkedin', 'https://www.linkedin.com/in/ada')
        ->set('data.profile.social_links.website', 'https://ada.dev')
        ->set('data.notifications.timezone', 'Europe/Paris')
        ->set('data.notifications.reminder_time', '18:45')
        ->set('data.notifications.channels', ['email', 'slack'])
        ->set('data.notifications.notification_types', ['daily_reminder'])
        ->set('data.ai.provider', 'openai')
        ->set('data.ai.tone', 'fun')
        ->set('data.ai.share_hashtags', ['#AdaCode', ' buildinpublic '])
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Ada Lovelace')
        ->and($user->profile->username)->toBe('ada-heroine')
        ->and($user->profile->focus_area)->toBe('Créer un agent IA')
        ->and($user->profile->bio)->toBe('First programmer, still shipping')
        ->and($user->profile->social_links)->toMatchArray([
            'github' => 'https://github.com/ada',
            'twitter' => 'https://x.com/ada',
            'linkedin' => 'https://www.linkedin.com/in/ada',
            'website' => 'https://ada.dev',
        ])
        ->and(data_get($user->profile->preferences, 'timezone'))->toBe('Europe/Paris')
        ->and(data_get($user->profile->preferences, 'reminder_time'))->toBe('18:45')
        ->and(data_get($user->profile->preferences, 'channels.email'))->toBeTrue()
        ->and(data_get($user->profile->preferences, 'channels.slack'))->toBeTrue()
        ->and(data_get($user->profile->preferences, 'channels.push'))->toBeFalse()
        ->and(data_get($user->profile->preferences, 'ai_provider'))->toBe('openai')
        ->and(data_get($user->profile->preferences, 'tone'))->toBe('fun')
        ->and(data_get($user->profile->preferences, 'social.share_hashtags'))->toBe(['#AdaCode', '#buildinpublic']);
});
