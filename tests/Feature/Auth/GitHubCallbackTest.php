<?php

use App\Http\Controllers\Auth\GitHubController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::get('/auth/github/callback', [GitHubController::class, 'callback'])->name('auth.github.callback');
});

afterEach(function (): void {
    \Mockery::close();
});

it('stores github tokens on the user profile during callback', function (): void {
    $mockProvider = \Mockery::mock(Provider::class);
    $mockUser = \Mockery::mock(ProviderUser::class);

    $mockUser->shouldReceive('getEmail')->andReturn('octocat@example.test');
    $mockUser->shouldReceive('getId')->andReturn('99999');
    $mockUser->shouldReceive('getName')->andReturn('The Octocat');
    $mockUser->shouldReceive('getNickname')->andReturn('octocat');
    $mockUser->shouldReceive('getAvatar')->andReturn('https://avatars.githubusercontent.com/u/1');
    $mockUser->token = 'access-token';
    $mockUser->refreshToken = 'refresh-token';
    $mockUser->expiresIn = 3600;

    $mockProvider->shouldReceive('user')->andReturn($mockUser);

    Socialite::shouldReceive('driver')->with('github')->andReturn($mockProvider);

    $response = $this->get('/auth/github/callback');

    $response->assertRedirect(route('daily-challenge'));

    $profile = User::first()->profile;

    expect($profile)->not()->toBeNull()
        ->and($profile->github_access_token)->not()->toBeNull()
        ->and($profile->github_refresh_token)->toBe('refresh-token')
        ->and($profile->github_token_expires_at)->not()->toBeNull();
});

it('returns a popup bridge view when authentication succeeds via popup', function (): void {
    $mockProvider = \Mockery::mock(Provider::class);
    $mockUser = \Mockery::mock(ProviderUser::class);

    $mockUser->shouldReceive('getEmail')->andReturn('octocat@example.test');
    $mockUser->shouldReceive('getId')->andReturn('99999');
    $mockUser->shouldReceive('getName')->andReturn('The Octocat');
    $mockUser->shouldReceive('getNickname')->andReturn('octocat');
    $mockUser->shouldReceive('getAvatar')->andReturn('https://avatars.githubusercontent.com/u/1');
    $mockUser->token = 'token-success';
    $mockUser->refreshToken = null;
    $mockUser->expiresIn = null;

    $mockProvider->shouldReceive('user')->andReturn($mockUser);

    Socialite::shouldReceive('driver')->with('github')->andReturn($mockProvider);

    $response = $this->withSession([
        'github_auth_popup' => true,
        'github_auth_return_url' => route('login'),
    ])->get('/auth/github/callback');

    $response->assertOk()
        ->assertSee('github-auth-popup')
        ->assertSee(route('daily-challenge'), false);

    expect(session('auth.github.error'))->toBeNull();
});

it('returns a popup bridge view with error details when authentication fails via popup', function (): void {
    $mockProvider = \Mockery::mock(Provider::class);
    $mockUser = \Mockery::mock(ProviderUser::class);

    $mockUser->shouldReceive('getEmail')->andReturn(null);
    $mockProvider->shouldReceive('user')->andReturn($mockUser);

    Socialite::shouldReceive('driver')->with('github')->andReturn($mockProvider);

    $response = $this->withSession([
        'github_auth_popup' => true,
        'github_auth_return_url' => route('register'),
    ])->get('/auth/github/callback');

    $response->assertOk()
        ->assertSee('github-auth-popup')
        ->assertSee(route('register'), false);

    expect(session('auth.github.error'))
        ->toBe('GitHub n’a pas fourni d’adresse e-mail vérifiée.');
});
