<?php

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('user is redirected to onboarding with default preferences after registration', function (): void {
    Livewire::test(Register::class)
        ->set('registerForm.name', 'Ada Lovelace')
        ->set('registerForm.username', 'Ada-L')
        ->set('registerForm.email', 'ada@example.test')
        ->set('registerForm.password', 'secret123')
        ->set('registerForm.password_confirmation', 'secret123')
        ->call('submit')
        ->assertRedirect(route('onboarding.wizard'));

    $user = User::where('email', 'ada@example.test')->first();

    expect($user)->not()->toBeNull();
    expect($user->needs_onboarding)->toBeTrue();
    expect($user->profile)->not()->toBeNull();
    expect($user->profile->username)->toBe('ada-l');
    expect($user->profile->preferences)->toMatchArray([
        'language' => 'en',
        'timezone' => 'Africa/Douala',
        'reminder_time' => '20:30',
        'channels' => [
            'email' => true,
            'slack' => false,
            'push' => false,
        ],
        'notification_types' => [
            'daily_reminder' => true,
            'weekly_digest' => true,
        ],
        'ai_provider' => 'groq',
        'tone' => 'neutral',
        'onboarding' => [
            'tour_completed' => false,
            'checklist' => [
                'first_log' => false,
                'project_linked' => false,
                'reminder_configured' => false,
                'public_share' => false,
            ],
        ],
    ]);
});
