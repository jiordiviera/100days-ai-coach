<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('renders a public profile with recent logs', function (): void {
    $user = User::factory()->create(['name' => 'Public Maker']);
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'is_public' => true,
    ]);

    $profile = $user->profile()->create([
        'username' => 'public-maker',
        'is_public' => true,
        'bio' => 'Build in public every day.',
    ]);

    DailyLog::factory()
        ->for($run, 'challengeRun')
        ->for($user, 'user')
        ->count(2)
        ->sequence(
            ['day_number' => 1, 'date' => now()->subDays(1), 'public_token' => (string) Str::ulid()],
            ['day_number' => 2, 'date' => now(), 'public_token' => (string) Str::ulid()]
        )
        ->create();

    $response = $this->get(route('public.profile', ['username' => $profile->username]));

    $response->assertOk()
        ->assertSee('public-maker', false)
        ->assertSee('Jour 2', false)
        ->assertSee('Logs publics', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('name="twitter:title"', false);
});

it('returns 404 when profile is private', function (): void {
    $user = User::factory()->create(['name' => 'Private Dev']);

    $user->profile()->create([
        'username' => 'private-dev',
        'is_public' => false,
    ]);

    $this->get(route('public.profile', ['username' => 'private-dev']))->assertNotFound();
});
