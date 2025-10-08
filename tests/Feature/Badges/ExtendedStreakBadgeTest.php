<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use App\Support\BadgeEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow('2024-10-20');
});

function seedLogs(User $user, ChallengeRun $run, int $days, string $endDate = '2024-10-20'): void
{
    $date = Carbon::parse($endDate);
    foreach (range(0, $days - 1) as $offset) {
        DailyLog::factory()->for($user, 'user')->for($run, 'challengeRun')->create([
            'day_number' => $offset + 1,
            'date' => $date->copy()->subDays($days - 1 - $offset)->toDateString(),
        ]);
    }
}

it('awards extended streak badges at 14/30/50/100 days', function (): void {
    $evaluator = new BadgeEvaluator;

    $user = User::factory()->create();
    $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'start_date' => '2024-09-01',
        'target_days' => 100,
        'status' => 'active',
    ]);

    $check = function (int $days, string $badgeKey) use ($user, $run, $evaluator): void {
        DailyLog::query()->delete();
        seedLogs($user, $run, $days);

        $badges = $evaluator->computeBadges($run, $user);
        expect(collect($badges)->pluck('id'))->toContain($badgeKey);
    };

    $check(14, 'streak_14');
    $check(30, 'streak_30');
    $check(50, 'streak_50');
    $check(100, 'streak_100');
});

it('detects comeback badge after significant break', function (): void {
    $evaluator = new BadgeEvaluator;

    $user = User::factory()->create();
    $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'start_date' => '2024-09-01',
        'target_days' => 100,
        'status' => 'active',
    ]);

    // Older streak
    seedLogs($user, $run, 5, '2024-10-10');

    // Break of 9 days (11-19), then new streak on 19-20
    DailyLog::factory()->for($user, 'user')->for($run, 'challengeRun')->create([
        'day_number' => 7,
        'date' => '2024-10-19',
    ]);

    DailyLog::factory()->for($user, 'user')->for($run, 'challengeRun')->create([
        'day_number' => 8,
        'date' => '2024-10-20',
    ]);

    $badges = $evaluator->computeBadges($run, $user);

    expect(collect($badges)->pluck('id'))->toContain('comeback');
});

it('does not award comeback badge without a two-day return streak', function (): void {
    $evaluator = new BadgeEvaluator;

    $user = User::factory()->create();
    $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'start_date' => '2024-09-01',
        'target_days' => 100,
        'status' => 'active',
    ]);

    seedLogs($user, $run, 5, '2024-10-10');

    DailyLog::factory()->for($user, 'user')->for($run, 'challengeRun')->create([
        'day_number' => 7,
        'date' => '2024-10-15',
    ]);

    DailyLog::factory()->for($user, 'user')->for($run, 'challengeRun')->create([
        'day_number' => 8,
        'date' => '2024-10-20',
    ]);

    $badges = $evaluator->computeBadges($run, $user);

    expect(collect($badges)->pluck('id'))->not()->toContain('comeback');
});
