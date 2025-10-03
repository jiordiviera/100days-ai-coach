<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('persists daily log AI fields and casts tags to an array', function (): void {
    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create();

    $log = DailyLog::create([
        'challenge_run_id' => $run->id,
        'user_id' => $user->id,
        'day_number' => 1,
        'date' => now()->subDay(),
        'hours_coded' => 3.5,
        'projects_worked_on' => ['task-manager'],
        'notes' => 'Initial scaffolding',
        'learnings' => 'Configured Livewire',
        'challenges_faced' => 'None',
        'completed' => true,
        'summary_md' => "## Recap\n- Set up AI prompts",
        'tags' => ['laravel', 'ai'],
        'coach_tip' => 'Ship the daily recap before midnight.',
        'share_draft' => 'Today I focused on the AI coach pipeline.',
        'ai_model' => 'groq/llama-3.1-mini',
        'ai_latency_ms' => 240,
        'ai_cost_usd' => 1.234,
    ]);

    $fresh = $log->fresh();

    expect($fresh->tags)->toBe(['laravel', 'ai'])
        ->and($fresh->ai_cost_usd)->toBe('1.234')
        ->and($fresh->ai_latency_ms)->toBe(240);
});

it('generates and persists a public token when requested', function (): void {
    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create();

    $log = DailyLog::factory()->for($run, 'challengeRun')->for($user, 'user')->create([
        'day_number' => 42,
        'public_token' => null,
    ]);

    expect($log->public_token)->toBeNull();

    $token = $log->ensurePublicToken();

    expect($token)->toHaveLength(26)
        ->and($token)->toBe($log->fresh()->public_token);
});

it('stores notifications outbox entries with queued status and supporting index', function (): void {
    $user = User::factory()->create();

    $id = (string) Str::ulid();

    DB::table('notifications_outbox')->insert([
        'id' => $id,
        'user_id' => $user->id,
        'type' => 'daily_reminder',
        'channel' => 'mail',
        'payload' => json_encode(['log_id' => 'test-log']),
        'scheduled_at' => now()->addHour(),
        'sent_at' => null,
        'error' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $entry = DB::table('notifications_outbox')->where('id', $id)->first();

    expect($entry)->not()->toBeNull()
        ->and($entry->status)->toBe('queued');

    $connection = DB::connection();
    $driver = $connection->getDriverName();

    $indexExists = match ($driver) {
        'mysql' => ! empty($connection->select(
            'SHOW INDEX FROM notifications_outbox WHERE Key_name = ?',
            ['notifications_outbox_user_status_schedule_index']
        )),
        'sqlite' => ! empty($connection->select(
            "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'notifications_outbox' AND name = ?",
            ['notifications_outbox_user_status_schedule_index']
        )),
        'pgsql' => ! empty($connection->select(
            'SELECT indexname FROM pg_indexes WHERE schemaname = ? AND tablename = ? AND indexname = ?',
            [$connection->getConfig('schema') ?? 'public', 'notifications_outbox', 'notifications_outbox_user_status_schedule_index']
        )),
        'sqlsrv' => ! empty($connection->select(
            'SELECT i.name FROM sys.indexes i JOIN sys.tables t ON i.object_id = t.object_id WHERE t.name = ? AND i.name = ?',
            ['notifications_outbox', 'notifications_outbox_user_status_schedule_index']
        )),
        default => false,
    };

    expect($indexExists)->toBeTrue();
});
