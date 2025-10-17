<?php

namespace App\Listeners;

use App\Events\DailyLogAiFailed;
use App\Events\DailyLogAiGenerated;
use App\Models\AiGenerationMetric;
use App\Models\DailyLog;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class RecordDailyLogAiMetrics
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(DailyLogAiGenerated::class, [self::class, 'handleGenerated']);
        $events->listen(DailyLogAiFailed::class, [self::class, 'handleFailed']);
    }

    public function handleGenerated(DailyLogAiGenerated $event): void
    {
        $date = Carbon::today()->startOfDay();

        $metric = AiGenerationMetric::query()->firstOrNew([
            'date' => $date,
            'model' => $event->model,
        ]);

        $metric->success_count = (int) $metric->success_count + 1;
        $metric->total_latency_ms = (int) $metric->total_latency_ms + $event->latencyMs;
        $metric->total_cost_usd = round((float) $metric->total_cost_usd + $event->costUsd, 3);
        $metric->last_generated_at = Carbon::now();
        $metric->metadata = $this->mergeMetadata($metric->metadata ?? [], $event->metadata);

        $metric->save();
    }

    public function handleFailed(DailyLogAiFailed $event): void
    {
        $log = DailyLog::find($event->dailyLogId);

        $model = $log?->ai_model ?: 'unknown';
        $date = Carbon::today()->startOfDay();

        $metric = AiGenerationMetric::query()->firstOrNew([
            'date' => $date,
            'model' => $model,
        ]);

        $metric->failure_count = (int) $metric->failure_count + 1;
        $metric->last_error_at = Carbon::now();
        $metric->last_error_message = $event->message;
        $metric->metadata = $this->mergeMetadata($metric->metadata ?? [], [
            'last_error' => $event->message,
        ]);

        $metric->save();
    }

    protected function mergeMetadata(array $existing, array $incoming = []): array
    {
        $existing['last'] = $incoming ?: Arr::get($existing, 'last');
        $existing['updated_at'] = Carbon::now()->toIso8601String();

        return $existing;
    }
}
