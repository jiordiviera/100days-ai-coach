<?php

use App\Models\NotificationChannel;
use App\Models\User;
use App\Services\Notifications\NotificationChannelResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves email channel by default', function (): void {
    $user = User::factory()->create();

    $user->profile()->create([
        'join_reason' => 'self_onboarding',
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    $resolver = app(NotificationChannelResolver::class);

    $channels = $resolver->resolve($user, 'daily_reminder');

    expect($channels)->toBe(['mail']);
});

it('includes telegram channel when preference and chat are active', function (): void {
    $user = User::factory()->create();

    $preferences = array_replace_recursive($user->profilePreferencesDefaults(), [
        'channels' => [
            'email' => true,
            'telegram' => true,
        ],
    ]);

    $user->profile()->create([
        'join_reason' => 'self_onboarding',
        'preferences' => $preferences,
    ]);

    NotificationChannel::factory()
        ->for($user, 'notifiable')
        ->create([
            'channel' => 'telegram',
            'value' => '123456789',
        ]);

    $resolver = app(NotificationChannelResolver::class);

    $channels = $resolver->resolve($user, 'daily_reminder');

    expect($channels)->toBe(['mail', 'telegram']);
});

it('returns empty list when notification type disabled', function (): void {
    $user = User::factory()->create();

    $preferences = array_replace_recursive($user->profilePreferencesDefaults(), [
        'notification_types' => [
            'daily_reminder' => false,
        ],
    ]);

    $user->profile()->create([
        'join_reason' => 'self_onboarding',
        'preferences' => $preferences,
    ]);

    $resolver = app(NotificationChannelResolver::class);

    $channels = $resolver->resolve($user, 'daily_reminder');

    expect($channels)->toBeEmpty();
});
