<?php

namespace App\Services\Ai\Drivers;

use App\Models\DailyLog;
use App\Services\Ai\Contracts\AiDriver;
use App\Services\Ai\Dto\DailyLogAiResult;
use Illuminate\Support\Str;
use Random\RandomException;

class FakeAiDriver implements AiDriver
{
    /**
     * @throws RandomException
     */
    public function generateDailyLogInsights(DailyLog $log): DailyLogAiResult
    {
        $base = Str::headline($log->notes ?? 'AI Coach Summary');
        $day = $log->day_number;

        $summary = sprintf('Day %d recap: %s', $day, $base ?: 'shipping progress.');
        $tags = collect($log->projects_worked_on ?? [])
            ->take(3)
            ->map(fn ($projectId) => 'project:'.$projectId)
            ->whenEmpty(fn ($collection) => $collection->push('productivity'))
            ->values()
            ->all();

        $coachTip = 'Focus on consistency and log a key learning tomorrow.';
        $shareDraft = sprintf("Day %d/%s — %s\n\nHighlights:\n- %s",
            $day,
            $log->challengeRun?->target_days ?? 100,
            $log->challengeRun?->title ?? '100DoC',
            $base ?: 'Made measurable progress today.'
        );

        return new DailyLogAiResult(
            summary: $summary,
            tags: $tags,
            coachTip: $coachTip,
            shareDraft: $shareDraft,
            model: 'ai.fake-driver.v1',
            latencyMs: random_int(120, 480),
            costUsd: round(random_int(5, 25) / 1000, 3),
        );
    }
}
