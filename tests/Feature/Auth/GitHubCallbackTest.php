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
