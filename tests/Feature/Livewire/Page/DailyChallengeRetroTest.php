<?php

use App\Livewire\Page\DailyChallenge;
use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2024-10-10 09:00:00');
});

it('allows retro completion for J-1 and J-2', function (): void {
    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'start_date' => '2024-10-01',
        'status' => 'active',
        'target_days' => 100,
    ]);

    $this->actingAs($user);

    // J-1
    Livewire::test(DailyChallenge::class)
        ->call('setDate', '2024-10-09')
        ->set('dailyForm.description', 'Retro J-1 log entry')
        ->set('dailyForm.hours_coded', 1.5)
        ->call('saveEntry');

    $logJ1 = DailyLog::where('challenge_run_id', $run->id)
        ->where('user_id', $user->id)
        ->where('day_number', 9)
        ->first();

    expect($logJ1)->not()->toBeNull()
        ->and($logJ1->retro)->toBeTrue();

    // J-2
    Livewire::test(DailyChallenge::class)
        ->call('setDate', '2024-10-08')
        ->set('dailyForm.description', 'Retro J-2 log entry')
        ->set('dailyForm.hours_coded', 2.0)
        ->call('saveEntry');

    $logJ2 = DailyLog::where('challenge_run_id', $run->id)
        ->where('user_id', $user->id)
        ->where('day_number', 8)
        ->first();

    expect($logJ2)->not()->toBeNull()
        ->and($logJ2->retro)->toBeTrue();
});

it('rejects retro completion beyond two days', function (): void {
    $user = User::factory()->create();
    ChallengeRun::factory()->for($user, 'owner')->create([
        'start_date' => '2024-10-01',
        'status' => 'active',
        'target_days' => 100,
    ]);

    $this->actingAs($user);

    Livewire::test(DailyChallenge::class)
        ->call('setDate', '2024-10-07')
        ->set('dailyForm.description', 'J-3 attempt log entry')
        ->set('dailyForm.hours_coded', 1.0)
        ->call('saveEntry');

    $exists = DailyLog::where('user_id', $user->id)
        ->where('day_number', 7)
        ->exists();

    expect($exists)->toBeFalse();
});
