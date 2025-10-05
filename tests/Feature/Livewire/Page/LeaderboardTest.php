<?php

use App\Livewire\Page\Leaderboard;
use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('displays users ordered by streak then days active', function (): void {
    Carbon::setTestNow('2024-10-10');

    $users = collect([
        User::factory()->create(['name' => 'User Alpha']),
        User::factory()->create(['name' => 'User Bravo']),
        User::factory()->create(['name' => 'User Charlie']),
    ]);
    $run = ChallengeRun::factory()->for($users->first(), 'owner')->create([
        'start_date' => '2024-09-20',
        'status' => 'active',
        'target_days' => 100,
    ]);

    // User A: streak 3, 5 days active
    foreach ([0, 1, 2, 5, 7] as $offset) {
        DailyLog::factory()->for($users[0], 'user')->for($run, 'challengeRun')->create([
            'date' => Carbon::parse('2024-10-10')->subDays($offset)->toDateString(),
            'day_number' => $offset + 1,
        ]);
    }

    // User B: streak 4, 4 days active
    foreach ([0, 1, 2, 3] as $offset) {
        DailyLog::factory()->for($users[1], 'user')->for($run, 'challengeRun')->create([
            'date' => Carbon::parse('2024-10-10')->subDays($offset)->toDateString(),
            'day_number' => $offset + 1,
        ]);
    }

    // User C: streak 2, 6 days total
    foreach ([0, 1, 4, 7] as $offset) {
        DailyLog::factory()->for($users[2], 'user')->for($run, 'challengeRun')->create([
            'date' => Carbon::parse('2024-10-10')->subDays($offset)->toDateString(),
            'day_number' => $offset + 1,
        ]);
    }

    $this->actingAs($users[0]);

    Livewire::test(Leaderboard::class)
        ->assertSee($users[1]->name)
        ->assertSee($users[0]->name)
        ->assertSee($users[2]->name)
        ->assertSeeInOrder([$users[1]->name, $users[0]->name, $users[2]->name]);
});

it('filters leaderboard by challenge', function (): void {
    Carbon::setTestNow('2024-10-10');

    $user = User::factory()->create();
    $this->actingAs($user);

    $runA = ChallengeRun::factory()->for($user, 'owner')->create([
        'title' => 'Run A',
        'start_date' => '2024-09-20',
        'status' => 'active',
    ]);

    $runB = ChallengeRun::factory()->for($user, 'owner')->create([
        'title' => 'Run B',
        'start_date' => '2024-09-25',
        'status' => 'active',
    ]);

    DailyLog::factory()->for($user, 'user')->for($runA, 'challengeRun')->create([
        'day_number' => 1,
        'date' => '2024-10-09',
    ]);

    Livewire::test(Leaderboard::class)
        ->set('challengeFilter', (string) $runB->id)
        ->assertDontSee($user->name);
});
