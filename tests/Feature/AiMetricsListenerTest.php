<?php

use App\Events\DailyLogAiFailed;
use App\Events\DailyLogAiGenerated;
use App\Listeners\RecordDailyLogAiMetrics;
use App\Models\AiGenerationMetric;
use App\Models\DailyLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records metrics when generation succeeds', function () {
    $listener = new RecordDailyLogAiMetrics;

    $event = new DailyLogAiGenerated('log-id', 'gpt-4o-mini', 250, 0.045, ['request' => ['model' => 'gpt-4o-mini']]);

    $listener->handleGenerated($event);

    $metric = AiGenerationMetric::first();

    expect($metric)->not->toBeNull()
        ->and($metric->model)->toBe('gpt-4o-mini')
        ->and($metric->success_count)->toBe(1)
        ->and((float) $metric->total_cost_usd)->toEqualWithDelta(0.045, 0.0001);
});

it('records failures when generation fails', function () {
    $listener = new RecordDailyLogAiMetrics;

    $log = DailyLog::factory()->create([
        'public_token' => null,
        'ai_model' => 'gpt-fail',
    ]);

    $event = new DailyLogAiFailed($log->id, 'Rate limited');

    $listener->handleFailed($event);

    $metric = AiGenerationMetric::where('model', 'gpt-fail')->first();

    expect($metric)->not->toBeNull()
        ->and($metric->failure_count)->toBe(1)
        ->and($metric->last_error_message)->toBe('Rate limited');
});
