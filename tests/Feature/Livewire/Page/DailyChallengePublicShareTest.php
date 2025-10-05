<?php

use App\Livewire\Page\DailyChallenge;
use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('enables and disables the public share link for the current log', function (): void {
    Carbon::setTestNow('2024-10-05');

    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'title' => 'Autumn Challenge',
        'start_date' => '2024-10-03',
        'status' => 'active',
        'target_days' => 100,
    ]);

    $log = DailyLog::factory()->for($run, 'challengeRun')->for($user, 'user')->create([
        'day_number' => 3,
        'date' => '2024-10-05',
        'summary_md' => 'Day 3 summary',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(DailyChallenge::class)
        ->call('enablePublicShare')
        ->assertSet('publicShare.url', route('logs.share', $log->fresh()->public_token));

    $component->call('disablePublicShare')
        ->assertSet('publicShare', null);
});
