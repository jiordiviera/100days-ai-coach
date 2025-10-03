<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Services\Ai\Drivers\LocalAiDriver;
use App\Services\Ai\Support\DailyLogPromptBuilder;
use Illuminate\Support\Facades\Http;

test('local driver defaults cost to zero when not provided', function (): void {
    $run = new ChallengeRun([
        'title' => 'AI Coaching',
        'target_days' => 100,
    ]);

    $log = new DailyLog([
        'day_number' => 7,
        'notes' => 'Running local inference.',
        'projects_worked_on' => ['task-manager'],
        'hours_coded' => 3.0,
    ]);

    $log->setRelation('challengeRun', $run);

    config()->set('services.ai.local', [
        'base_url' => 'http://127.0.0.1:11434/v1',
        'api_key' => null,
        'model' => 'mistral-7b',
        'cost_per_1k_tokens' => 0.0,
    ]);

    Http::fake([
        'http://127.0.0.1:11434/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'summary_md' => '## Day 7 Summary',
                        'tags' => ['local', 'inference'],
                        'coach_tip' => 'Monitor GPU usage for consistent runs.',
                        'share_draft' => 'Day 7/100 — Running local inference!',
                    ]),
                ],
            ]],
            'usage' => [
                'total_tokens' => 600,
            ],
        ], 200),
    ]);

    $driver = new LocalAiDriver(new DailyLogPromptBuilder());

    $result = $driver->generateDailyLogInsights($log);

    expect($result->summary)->toContain('Day 7 Summary')
        ->and($result->tags)->toBe(['local', 'inference'])
        ->and($result->coachTip)->toContain('Monitor GPU usage')
        ->and($result->shareDraft)->toContain('Running local inference')
        ->and($result->model)->toBe('mistral-7b')
        ->and($result->costUsd)->toBe(0.0);
});
