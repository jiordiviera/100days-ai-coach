<?php

use App\Console\Commands\SyncWakaTime;
use App\Jobs\GenerateDailyLogInsights;
use App\Jobs\SyncWakaTimeForUser;
use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('it synchronises daily logs from wakatime summaries', function (): void {
    Carbon::setTestNow('2024-10-05 21:00:00');

    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'preferences' => array_merge($user->profilePreferencesDefaults(), [
            'timezone' => 'Africa/Douala',
            'wakatime' => [
                'hide_project_names' => false,
            ],
        ]),
        'wakatime_api_key' => 'test-key',
        'wakatime_settings' => ['hide_project_names' => false],
    ]);

    $run = ChallengeRun::factory()
        ->for($user, 'owner')
        ->create([
            'start_date' => '2024-10-03',
            'target_days' => 100,
            'status' => 'active',
        ]);

    Http::fake([
        'https://wakatime.com/api/v1/*' => Http::response([
            'data' => [[
                'range' => ['date' => '2024-10-05'],
                'grand_total' => [
                    'total_seconds' => 14_400,
                    'text' => '4 hrs',
                ],
                'projects' => [
                    ['name' => 'SecretApp', 'total_seconds' => 7_200],
                    ['name' => 'Another Project', 'total_seconds' => 7_200],
                ],
                'languages' => [
                    ['name' => 'PHP', 'total_seconds' => 10_800],
                    ['name' => 'Blade', 'total_seconds' => 3_600],
                ],
            ]],
        ]),
    ]);

    Bus::fake();

    $job = new SyncWakaTimeForUser($user->id, '2024-10-05');
    $job->handle(app(App\Services\WakaTime\WakaTimeClient::class));

    $log = DailyLog::where('user_id', $user->id)
        ->where('challenge_run_id', $run->id)
        ->first();

    expect($log)->not()->toBeNull()
        ->and($log->day_number)->toBe(3)
        ->and((float) $log->hours_coded)->toBe(4.0)
        ->and($log->wakatime_synced_at)->not()->toBeNull()
        ->and($log->wakatime_summary['projects'][0]['name'])->toBe('SecretApp')
        ->and($log->completed)->toBeTrue();

    Bus::assertDispatched(GenerateDailyLogInsights::class, function ($dispatched) use ($log) {
        return $dispatched->dailyLogId === $log->id;
    });

    $profile->refresh();
    expect($profile->wakatime_settings['last_error'] ?? null)->toBeNull()
        ->and($profile->wakatime_settings['last_synced_at'])->not()->toBeNull();
});

test('it preserves manual hours and masks project names when requested', function (): void {
    Carbon::setTestNow('2024-10-05 21:00:00');

    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
        'wakatime_api_key' => 'test-key',
        'wakatime_settings' => ['hide_project_names' => true],
    ]);

    $run = ChallengeRun::factory()
        ->for($user, 'owner')
        ->create([
            'start_date' => '2024-10-03',
            'target_days' => 100,
            'status' => 'active',
        ]);

    $log = DailyLog::factory()->for($run, 'challengeRun')->for($user, 'user')->create([
        'day_number' => 3,
        'date' => '2024-10-05',
        'hours_coded' => 1.5,
        'summary_md' => null,
        'coach_tip' => null,
        'share_draft' => null,
    ]);

    Http::fake([
        'https://wakatime.com/api/v1/*' => Http::response([
            'data' => [[
                'range' => ['date' => '2024-10-05'],
                'grand_total' => [
                    'total_seconds' => 9_000,
                    'text' => '2 hrs 30 mins',
                ],
                'projects' => [
                    ['name' => 'Confidential', 'total_seconds' => 9_000],
                ],
                'languages' => [],
            ]],
        ]),
    ]);

    Bus::fake();

    $job = new SyncWakaTimeForUser($user->id, '2024-10-05');
    $job->handle(app(App\Services\WakaTime\WakaTimeClient::class));

    $log->refresh();

    expect((float) $log->hours_coded)->toBe(1.5)
        ->and($log->wakatime_summary['projects'][0]['name'])->toBe('Hidden Project #1')
        ->and($log->wakatime_summary['projects'][0]['total_hours'])->toBe(2.5);

    Bus::assertDispatched(GenerateDailyLogInsights::class);
});

test('wakatime sync command queues jobs for users with api keys', function (): void {
    $user = User::factory()->create();
    $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
        'wakatime_api_key' => 'abc123',
    ]);

    Bus::fake();

    $this->artisan('wakatime:sync --date=2024-10-05')
        ->expectsOutput('1 synchronisations WakaTime en file d\'attente.')
        ->assertExitCode(SyncWakaTime::SUCCESS);

    Bus::assertDispatched(SyncWakaTimeForUser::class);
});

test('wakatime sync command runs immediately when no date is provided', function (): void {
    Carbon::setTestNow('2024-10-05 21:00:00');

    $user = User::factory()->create();
    $user->profile()->create([
        'preferences' => array_merge($user->profilePreferencesDefaults(), [
            'timezone' => 'Africa/Douala',
        ]),
        'wakatime_api_key' => 'live-key',
        'wakatime_settings' => ['hide_project_names' => false],
    ]);

    $run = ChallengeRun::factory()
        ->for($user, 'owner')
        ->create([
            'start_date' => '2024-10-03',
            'target_days' => 100,
            'status' => 'active',
        ]);

    Http::fake([
        'https://wakatime.com/api/v1/*' => Http::response([
            'data' => [[
                'range' => ['date' => '2024-10-05'],
                'grand_total' => [
                    'total_seconds' => 14_400,
                    'text' => '4 hrs',
                ],
                'projects' => [
                    ['name' => 'Synced Project', 'total_seconds' => 14_400],
                ],
                'languages' => [
                    ['name' => 'PHP', 'total_seconds' => 14_400],
                ],
            ]],
        ]),
    ]);

    Bus::fake([GenerateDailyLogInsights::class]);

    $this->artisan('wakatime:sync')
        ->expectsOutput('1 synchronisations WakaTime exécutées.')
        ->assertExitCode(SyncWakaTime::SUCCESS);

    $log = DailyLog::where('user_id', $user->id)
        ->where('challenge_run_id', $run->id)
        ->first();

    expect($log)->not()->toBeNull()
        ->and($log->day_number)->toBe(3)
        ->and((float) $log->hours_coded)->toBe(4.0);

    Bus::assertDispatched(GenerateDailyLogInsights::class);
});

test('waketime sync records errors when the API rejects the key', function (): void {
    Carbon::setTestNow('2024-10-05 21:00:00');

    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'preferences' => $user->profilePreferencesDefaults(),
        'wakatime_api_key' => 'bad-key',
    ]);

    ChallengeRun::factory()->for($user, 'owner')->create([
        'start_date' => '2024-10-03',
        'target_days' => 100,
        'status' => 'active',
    ]);

    Http::fake([
        'https://wakatime.com/api/v1/*' => Http::response([
            'errors' => ['Unauthorized.'],
        ], 401),
    ]);

    Bus::fake([GenerateDailyLogInsights::class]);

    $this->artisan('wakatime:sync')
        ->expectsOutput('1 synchronisations WakaTime exécutées.')
        ->assertExitCode(SyncWakaTime::SUCCESS);

    $profile->refresh();

    expect($profile->wakatime_settings['last_error'] ?? null)
        ->toBe('Invalid WakaTime API key.');

    Bus::assertNotDispatched(GenerateDailyLogInsights::class);
});
