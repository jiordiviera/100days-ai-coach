<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Services\Ai\Drivers\OpenAiAiDriver;
use App\Services\Ai\Support\DailyLogPromptBuilder;
use Illuminate\Support\Facades\Http;

test('openai driver parses structured payload', function (): void {
    $run = new ChallengeRun([
        'title' => 'AI Coaching',
        'target_days' => 100,
    ]);

    $log = new DailyLog([
        'day_number' => 6,
        'notes' => 'Documented the new drivers.',
        'projects_worked_on' => ['task-manager'],
        'hours_coded' => 2.5,
    ]);

    $log->setRelation('challengeRun', $run);

    config()->set('services.ai.openai', [
        'base_url' => 'https://api.openai.com/v1',
        'api_key' => 'openai-key',
        'model' => 'gpt-4o-mini',
        'cost_per_1k_tokens' => 0.006,
    ]);

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => '```json'.PHP_EOL.json_encode([
                        'summary_md' => '## Day 6 Summary',
                        'tags' => ['laravel', 'ai'],
                        'coach_tip' => 'Share the knowledge with your team.',
                        'share_draft' => 'Day 6/100 — Expanded our AI integrations!',
                    ]).PHP_EOL.'```',
                ],
            ]],
            'usage' => [
                'total_tokens' => 800,
            ],
        ], 200),
    ]);

    $driver = new OpenAiAiDriver(new DailyLogPromptBuilder);

    $result = $driver->generateDailyLogInsights($log);

    expect($result->summary)->toContain('Day 6 Summary')
        ->and($result->tags)->toBe(['laravel', 'ai'])
        ->and($result->coachTip)->toBe('Share the knowledge with your team.')
        ->and($result->shareDraft)->toContain('Expanded our AI integrations')
        ->and($result->model)->toBe('gpt-4o-mini')
        ->and($result->costUsd)->toBe(0.005);
});
