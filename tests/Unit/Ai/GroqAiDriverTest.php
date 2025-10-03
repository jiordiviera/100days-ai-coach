<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Services\Ai\Drivers\GroqAiDriver;
use App\Services\Ai\Support\DailyLogPromptBuilder;
use Illuminate\Support\Facades\Http;

test('groq driver parses structured payload and returns result', function (): void {
    $run = new ChallengeRun([
        'title' => 'AI Coaching',
        'target_days' => 100,
    ]);

    $log = new DailyLog([
        'day_number' => 5,
        'notes' => 'Built the async AI pipeline and wrote tests.',
        'projects_worked_on' => ['task-manager'],
        'hours_coded' => 3.5,
    ]);

    $log->setRelation('challengeRun', $run);

    config()->set('services.ai.groq', [
        'base_url' => 'https://api.groq.com/openai/v1',
        'api_key' => 'test-key',
        'model' => 'mixtral-8x7b-32768',
        'cost_per_1k_tokens' => 0.002,
    ]);

    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'summary_md' => '## Day 5 Summary',
                        'tags' => ['laravel', 'testing'],
                        'coach_tip' => 'Keep iterating on your async pipeline.',
                        'share_draft' => "Day 5/100 — Async AI ready!\n\n- Pipeline built\n- Tests written",
                    ]),
                ],
            ]],
            'usage' => [
                'total_tokens' => 1500,
            ],
        ], 200),
    ]);

    $driver = new GroqAiDriver(new DailyLogPromptBuilder());

    $result = $driver->generateDailyLogInsights($log);

    expect($result->summary)->toContain('Day 5 Summary')
        ->and($result->tags)->toBe(['laravel', 'testing'])
        ->and($result->coachTip)->toBe('Keep iterating on your async pipeline.')
        ->and($result->shareDraft)->toContain('Async AI ready')
        ->and($result->model)->toBe('mixtral-8x7b-32768')
        ->and($result->latencyMs)->toBeGreaterThanOrEqual(0)
        ->and($result->costUsd)->toBe(0.003);
});
