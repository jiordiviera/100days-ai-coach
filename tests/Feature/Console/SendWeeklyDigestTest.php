<?php

use App\Console\Commands\SendWeeklyDigest;
use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\NotificationOutbox;
use App\Models\User;
use App\Notifications\WeeklyDigestNotification;
use App\Services\Ai\AiManager;
use App\Services\Ai\Dto\DailyLogAiResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

afterEach(function (): void {
    \Mockery::close();
});

it('sends weekly digest to opted-in users on sunday', function (): void {
    Carbon::setTestNow('2024-10-13 06:00:00'); // Sunday UTC

    Notification::fake();

    $aiManager = \Mockery::mock(AiManager::class);
    $aiManager->shouldReceive('generateInsights')
        ->andReturn(new DailyLogAiResult(
            summary: 'Résumé de la semaine',
            tags: ['laravel', 'ship'],
            coachTip: 'Continue sur ta lancée demain !',
            shareDraft: 'Week recap incoming!',
            model: 'ai.fake-driver.v2',
            latencyMs: 120,
            costUsd: 0.005
        ));
    $this->instance(AiManager::class, $aiManager);

    $user = User::factory()->create(['name' => 'Weekly Coder']);
    $profile = $user->profile()->create([
        'preferences' => array_replace_recursive($user->profilePreferencesDefaults(), [
            'channels' => ['email' => true, 'telegram' => false, 'slack' => false, 'push' => false],
            'notification_types' => ['weekly_digest' => true, 'daily_reminder' => false],
            'timezone' => 'Europe/Paris',
        ]),
    ]);

    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'start_date' => '2024-09-01',
        'status' => 'active',
        'target_days' => 100,
    ]);

    foreach (range(0, 4) as $offset) {
        DailyLog::factory()->for($user, 'user')->for($run, 'challengeRun')->create([
            'day_number' => $offset + 1,
            'date' => Carbon::parse('2024-10-13')->subDays($offset)->toDateString(),
            'hours_coded' => 2.5,
            'notes' => 'Progress '.$offset,
        ]);
    }

    $this->artisan('digest:weekly')->assertExitCode(SendWeeklyDigest::SUCCESS);

    Notification::assertSentTo($user, WeeklyDigestNotification::class);
    $record = NotificationOutbox::where('user_id', $user->id)->where('type', 'weekly_digest')->first();

    expect($record)->not()->toBeNull()
        ->and($record->status)->toBe('sent')
        ->and($record->payload['week_ending'])->toBe('2024-10-13');
});

it('skips users when not sunday or opt-out', function (): void {
    Carbon::setTestNow('2024-10-12 06:00:00'); // Saturday

    Notification::fake();

    $aiManager = \Mockery::mock(AiManager::class);
    $this->instance(AiManager::class, $aiManager);

    $user = User::factory()->create();
    $user->profile()->create([
        'preferences' => array_replace_recursive($user->profilePreferencesDefaults(), [
            'channels' => ['email' => true, 'telegram' => false],
            'notification_types' => ['weekly_digest' => true],
            'timezone' => 'UTC',
        ]),
    ]);

    $this->artisan('digest:weekly')->assertExitCode(SendWeeklyDigest::SUCCESS);

    expect(NotificationOutbox::where('type', 'weekly_digest')->count())->toBe(0);

    // Opt-out user
    Carbon::setTestNow('2024-10-13 06:00:00');
    $optOut = User::factory()->create();
    $optOut->profile()->create([
        'preferences' => array_replace_recursive($optOut->profilePreferencesDefaults(), [
            'channels' => ['email' => true, 'telegram' => false],
            'notification_types' => ['weekly_digest' => false],
            'timezone' => 'UTC',
        ]),
    ]);

    $this->artisan('digest:weekly')->assertExitCode(SendWeeklyDigest::SUCCESS);

    expect(NotificationOutbox::where('type', 'weekly_digest')->count())->toBe(0);
});

it('does not duplicate digest within the same week', function (): void {
    Carbon::setTestNow('2024-10-13 06:00:00');

    Notification::fake();

    $aiManager = \Mockery::mock(AiManager::class);
    $aiManager->shouldReceive('generateInsights')->andReturn(new DailyLogAiResult(
        summary: 'Résumé court',
        tags: ['laravel'],
        coachTip: 'Ship it',
        shareDraft: 'Week recap ready!',
        model: 'ai.fake-driver.v2',
        latencyMs: 100,
        costUsd: 0.003
    ));
    $this->instance(AiManager::class, $aiManager);

    $user = User::factory()->create();
    $user->profile()->create([
        'preferences' => array_replace_recursive($user->profilePreferencesDefaults(), [
            'channels' => ['email' => true, 'telegram' => false],
            'notification_types' => ['weekly_digest' => true],
            'timezone' => 'UTC',
        ]),
    ]);

    DailyLog::factory()->for($user, 'user')->create([
        'date' => '2024-10-12',
        'hours_coded' => 1.5,
    ]);

    $this->artisan('digest:weekly');
    $this->artisan('digest:weekly');

    expect(NotificationOutbox::where('type', 'weekly_digest')->count())->toBe(1);
});
