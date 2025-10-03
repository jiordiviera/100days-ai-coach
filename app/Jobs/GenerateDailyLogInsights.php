<?php

namespace App\Jobs;

use App\Models\DailyLog;
use App\Services\Ai\AiManager;
use App\Services\Ai\Dto\DailyLogAiResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateDailyLogInsights implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly string $dailyLogId,
        public readonly bool $force = false,
    ) {
        $this->onQueue('ai');
    }

    public function handle(AiManager $manager): void
    {
        $log = DailyLog::with('challengeRun')->find($this->dailyLogId);

        if (! $log) {
            return;
        }

        if (! $this->force && static::isThrottledFor($log)) {
            return;
        }

        $lock = Cache::lock($this->lockKey($log), 15);

        if (! $lock->get()) {
            return;
        }

        try {
            $result = $manager->generateInsights($log, $this->force);

            $this->applyResult($log, $result);

            if (! $this->force) {
                $this->rememberThrottle($log);
            }
        } finally {
            $lock->release();
        }
    }

    protected function applyResult(DailyLog $log, DailyLogAiResult $result): void
    {
        $log->forceFill([
            'summary_md' => $result->summary,
            'tags' => $result->tags,
            'coach_tip' => $result->coachTip,
            'share_draft' => $result->shareDraft,
            'ai_model' => $result->model,
            'ai_latency_ms' => $result->latencyMs,
            'ai_cost_usd' => $result->costUsd,
        ])->save();

        Log::info('ai.daily_log.generated', [
            'daily_log_id' => $log->id,
            'challenge_run_id' => $log->challenge_run_id,
            'user_id' => $log->user_id,
            'model' => $result->model,
            'latency_ms' => $result->latencyMs,
            'cost_usd' => $result->costUsd,
        ]);
    }

    public static function isThrottledFor(DailyLog $log): bool
    {
        return Cache::has(static::throttleKeyFor($log));
    }

    protected function rememberThrottle(DailyLog $log): void
    {
        $ttl = Carbon::now()->endOfDay()->addMinute()->diffInSeconds(now());

        Cache::put(static::throttleKeyFor($log), true, $ttl > 0 ? $ttl : 3600);
    }

    public static function throttleKeyFor(DailyLog $log): string
    {
        return sprintf('daily-log-ai:throttle:%s:%s', $log->id, Carbon::today()->toDateString());
    }

    protected function lockKey(DailyLog $log): string
    {
        return sprintf('daily-log-ai:lock:%s', $log->id);
    }
}
