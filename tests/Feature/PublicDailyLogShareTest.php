<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('renders the public daily log view with limited data', function (): void {
    $user = User::factory()->create(['name' => 'Jane Developer']);
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'title' => 'Autumn Challenge',
        'start_date' => now()->subDays(5),
    ]);

    $log = DailyLog::factory()
        ->for($user, 'user')
        ->for($run, 'challengeRun')
        ->create([
            'day_number' => 5,
            'summary_md' => "# Highlights\n- Built the sharing feature",
            'tags' => ['laravel', 'livewire'],
            'coach_tip' => 'Plan un thread Twitter demain.',
            'share_draft' => 'Jour 5/100 — LinkedIn draft content',
            'share_templates' => [
                'linkedin' => 'Jour 5/100 — LinkedIn draft content',
                'x' => 'Day 5/100: Shipping update #100DaysOfCode',
            ],
            'public_token' => Str::ulid(),
        ]);

    $response = $this->get(route('logs.share', $log->public_token));

    $response->assertOk()
        ->assertSee('Jane Developer', escape: false)
        ->assertSee('Highlights', escape: false)
        ->assertSee('laravel', escape: false)
        ->assertSee('LinkedIn', escape: false)
        ->assertSee('Day 5/100: Shipping update', escape: false)
        ->assertSee('property="og:title"', false)
        ->assertSee('name="twitter:title"', false)
        ->assertDontSee('email');
});

it('returns 404 when the token is invalid', function (): void {
    $response = $this->get(route('logs.share', 'invalid-token'));

    $response->assertNotFound();
});
