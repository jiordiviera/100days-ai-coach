<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
});

afterEach(function (): void {
    Cache::flush();
});

it('requires authentication to link telegram chat', function (): void {
    $token = 'token-123';
    Cache::put('telegram:link-token:'.$token, [
        'chat_id' => '987654',
        'language' => 'en',
    ], now()->addMinutes(10));

    $this->get(route('settings.telegram.link', ['token' => $token]))
        ->assertRedirect(route('login'));

    expect(Cache::has('telegram:link-token:'.$token))->toBeTrue();
});

it('links telegram chat to the authenticated user', function (): void {
    $token = 'token-abc';
    Cache::put('telegram:link-token:'.$token, [
        'chat_id' => '123456',
        'language' => 'fr',
        'username' => 'maker',
    ], now()->addMinutes(10));

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.telegram.link', ['token' => $token]))
        ->assertRedirect(route('settings'))
        ->assertSessionHas('status');

    expect(Cache::has('telegram:link-token:'.$token))->toBeFalse();

    $channel = $user->notificationChannels()->where('channel', 'telegram')->first();
    expect($channel)->not()->toBeNull()
        ->and($channel->value)->toBe('123456')
        ->and($channel->language)->toBe('fr')
        ->and($channel->is_active)->toBeTrue();
});

it('rejects expired or unknown link tokens', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.telegram.link', ['token' => 'missing']))
        ->assertRedirect(route('settings'))
        ->assertSessionHas('error');
});
