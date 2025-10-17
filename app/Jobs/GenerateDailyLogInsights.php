<?php

namespace App\Jobs;

use App\Events\DailyLogAiFailed;
use App\Events\DailyLogAiGenerated;
use App\Models\DailyLog;
use App\Services\Ai\AiManager;
use App\Services\Ai\Dto\DailyLogAiResult;
use App\Support\SocialShareTemplateBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

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
    ) {}

    public function handle(AiManager $manager): void
    {
        $log = DailyLog::with(['challengeRun', 'user.profile'])->find($this->dailyLogId);

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
        } catch (Throwable $exception) {
            report($exception);

            $result = $this->buildFallbackResult($log, $exception);

            Log::warning('ai.daily_log.fallback', [
                'daily_log_id' => $log->id,
                'challenge_run_id' => $log->challenge_run_id,
                'user_id' => $log->user_id,
                'exception' => $exception->getMessage(),
            ]);

            event(new DailyLogAiFailed($log->id, $exception->getMessage()));
        }

        try {
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
        $log->loadMissing(['challengeRun', 'user.profile']);

        $templates = app(SocialShareTemplateBuilder::class)->build($log, [
            'summary' => $result->summary,
            'tags' => $result->tags,
            'share_draft' => $result->shareDraft,
        ]);

        $shareDraft = $templates['linkedin'] ?? $result->shareDraft;

        $metadata = $this->buildMetadataPayload($result);

        $log->forceFill([
            'summary_md' => $result->summary,
            'tags' => $result->tags,
            'coach_tip' => $result->coachTip,
            'ai_model' => $result->model,
            'ai_latency_ms' => $result->latencyMs,
            'ai_cost_usd' => $result->costUsd,
            'ai_metadata' => $metadata ?: null,
            'share_draft' => $shareDraft,
            'share_templates' => $templates ?: null,
        ])->save();

        Log::info('ai.daily_log.generated', [
            'daily_log_id' => $log->id,
            'challenge_run_id' => $log->challenge_run_id,
            'user_id' => $log->user_id,
            'model' => $result->model,
            'latency_ms' => $result->latencyMs,
            'cost_usd' => $result->costUsd,
        ]);

        event(new DailyLogAiGenerated(
            dailyLogId: $log->id,
            model: $result->model,
            latencyMs: $result->latencyMs,
            costUsd: $result->costUsd,
            metadata: $metadata,
        ));

        if ($profileUsername = optional($log->user->profile)->username) {
            Cache::forget("public-profile:{$profileUsername}");
        }

        if ($log->challengeRun?->public_slug) {
            Cache::forget('public-challenge:'.$log->challengeRun->public_slug);
        }
    }

    protected function buildMetadataPayload(DailyLogAiResult $result): array
    {
        $metadata = $result->metadata ?? [];

        $payload = [
            'generated_at' => Carbon::now()->toIso8601String(),
            'force' => $this->force,
            'request' => $metadata['request'] ?? null,
            'response' => $metadata['response'] ?? null,
        ];

        if (! empty($metadata['fallback'])) {
            $payload['fallback'] = true;
        }

        if (! empty($metadata['error'])) {
            $payload['error'] = $metadata['error'];
        }

        return array_filter($payload, fn ($value) => $value !== null && $value !== []);
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

    protected function buildFallbackResult(DailyLog $log, ?Throwable $exception = null): DailyLogAiResult
    {
        $dayNumber = (int) ($log->day_number ?? 1);
        $targetDays = (int) ($log->challengeRun?->target_days ?? max($dayNumber, 100));
        $dayLabel = sprintf('Jour %d/%d', max(1, $dayNumber), max($dayNumber, $targetDays));

        $notes = Str::of($log->notes ?? '')
            ->stripTags()
            ->squish();

        $learnings = Str::of($log->learnings ?? '')
            ->stripTags()
            ->squish();

        $highlightSource = $notes->isNotEmpty() ? $notes : $learnings;
        $body = $highlightSource->isNotEmpty()
            ? Str::limit($highlightSource->value(), 280)
            : 'Pas de résumé IA disponible. Voici un rappel de garder la cadence quotidienne.';

        $summaryLines = [
            "### {$dayLabel}",
            $body,
        ];

        $projects = collect($log->projects_worked_on ?? [])
            ->map(fn ($project) => '- Projet : '.Str::of((string) $project)->squish()->limit(80))
            ->all();

        if (! empty($projects)) {
            $summaryLines[] = implode("\n", $projects);
        }

        $summary = implode("\n\n", array_filter($summaryLines));

        $tagCandidates = collect($log->tags ?? [])
            ->map(fn ($tag) => Str::of((string) $tag)->stripTags()->squish()->value())
            ->filter();

        if ($tagCandidates->isEmpty() && $projects) {
            $tagCandidates = collect($projects)->map(fn ($line) => Str::of($line)->replace('- Projet : ', '')->value());
        }

        $tags = $tagCandidates->take(3)->values()->all();

        $coachTip = 'Continue d’écrire tes logs même sans résumé IA — ta régularité fait la différence.';

        $builder = app(SocialShareTemplateBuilder::class);
        $templates = $builder->build($log, [
            'summary' => $summary,
            'tags' => $tags,
        ]);

        $shareDraft = $templates['linkedin'] ?? sprintf('%s — garder la cadence, même sans IA !', $dayLabel);

        return new DailyLogAiResult(
            summary: $summary,
            tags: $tags,
            coachTip: $coachTip,
            shareDraft: $shareDraft,
            model: 'ai.fallback.offline',
            latencyMs: 0,
            costUsd: 0.0,
            metadata: [
                'fallback' => true,
                'error' => $exception?->getMessage(),
            ],
        );
    }
}
