<?php

use App\Livewire\Auth\Register;
use App\Models\NotificationChannel;
use App\Models\User;
use App\Services\Telegram\TelegramClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Mockery as M;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('cache.default', 'array');
    Cache::flush();
});

afterEach(function (): void {
    M::close();
    Cache::flush();
});

// test('user is redirected to onboarding with default preferences after registration', function (): void {
//     Livewire::test(Register::class)
//         ->set('registerForm.name', 'Ada Lovelace')
//         ->set('registerForm.username', 'Ada-L')
//         ->set('registerForm.email', 'ada@example.test')
//         ->set('registerForm.password', 'secret123')
//         ->set('registerForm.password_confirmation', 'secret123')
//         ->call('submit')
//         ->assertRedirect(route('onboarding.wizard'));

//     $user = User::where('email', 'ada@example.test')->first();

//     expect($user)->not()->toBeNull();
//     expect($user->needs_onboarding)->toBeTrue();
//     expect($user->profile)->not()->toBeNull();
//     expect($user->profile->username)->toBe('ada-l');
//     expect($user->profile->preferences)->toMatchArray([
//         'language' => 'en',
//         'timezone' => 'Africa/Douala',
//         'reminder_time' => '20:30',
//         'channels' => [
//             'email' => true,
//             'telegram' => false,
//             'slack' => false,
//             'push' => false,
//         ],
//         'notification_types' => [
//             'daily_reminder' => true,
//             'weekly_digest' => true,
//         ],
//         'ai_provider' => 'groq',
//         'tone' => 'neutral',
//         'onboarding' => [
//             'tour_completed' => false,
//             'checklist' => [
//                 'first_log' => false,
//                 'project_linked' => false,
//                 'reminder_configured' => false,
//                 'public_share' => false,
//             ],
//         ],
//     ]);
// });

test('user can sign up via telegram token', function (): void {
    $token = 'token-telegram';

    Cache::put('telegram:signup-token:'.$token, [
        'chat_id' => '424242',
        'language' => 'fr',
        'first_name' => 'Bot Maker',
        'username' => 'botmaker',
    ], now()->addMinutes(10));

    $telegram = M::mock(TelegramClient::class);
    $telegram->shouldReceive('sendMessage')
        ->once()
        ->with('424242', M::on(fn ($payload) => is_array($payload) && str_contains($payload['text'], '🎉')));
    app()->instance(TelegramClient::class, $telegram);

    Livewire::withQueryParams(['telegram_token' => $token])
        ->test(Register::class)
        ->set('registerForm.name', 'Bot Maker')
        ->set('registerForm.username', 'botmaker')
        ->set('registerForm.email', 'bot@example.test')
        ->set('registerForm.password', 'secret123')
        ->set('registerForm.password_confirmation', 'secret123')
        ->call('submit')
        ->assertRedirect(route('onboarding.wizard'));

    $user = User::where('email', 'bot@example.test')->first();

    expect($user)->not()->toBeNull();
    expect($user->profile->preferences['channels']['telegram'])->toBeTrue();

    $channel = NotificationChannel::where('notifiable_id', $user->id)->where('channel', 'telegram')->first();
    expect($channel)->not()->toBeNull()
        ->and($channel->value)->toBe('424242')
        ->and($channel->language)->toBe('fr');

    expect(Cache::has('telegram:signup-token:'.$token))->toBeFalse();
});
