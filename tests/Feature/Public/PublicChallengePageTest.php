<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('renders a public challenge page', function (): void {
    $owner = User::factory()->create(['name' => 'Coach']);
    $run = ChallengeRun::factory()->for($owner, 'owner')->create([
        'is_public' => true,
        'public_join_code' => 'JOINME',
    ]);

    $owner->profile()->create([
        'username' => 'coach',
        'is_public' => true,
    ]);

    $participant = User::factory()->create(['name' => 'Member']);
    $participant->profile()->create([
        'username' => 'member',
        'is_public' => true,
    ]);

    $run->participantLinks()->create([
        'user_id' => $participant->id,
        'joined_at' => now(),
    ]);

    DailyLog::factory()->for($run, 'challengeRun')->for($participant, 'user')->create([
        'day_number' => 1,
        'date' => now(),
        'public_token' => (string) Str::ulid(),
    ]);

    $response = $this->get(route('public.challenge', ['slug' => $run->public_slug]));

    $response->assertOk()
        ->assertSee($run->title, false)
        ->assertSee('JOINME', false)
        ->assertSee('member', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('name="twitter:title"', false);
});

it('returns 404 for a private challenge', function (): void {
    $owner = User::factory()->create();
    $run = ChallengeRun::factory()->for($owner, 'owner')->create([
        'is_public' => false,
    ]);

    $this->get(route('public.challenge', ['slug' => $run->public_slug]))->assertNotFound();
});
