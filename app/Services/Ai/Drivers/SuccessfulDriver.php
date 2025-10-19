<?php

declare(strict_types=1);

namespace App\Services\Ai\Drivers;

use App\Models\DailyLog;
use App\Services\Ai\Contracts\AiDriver;
use App\Services\Ai\Dto\DailyLogAiResult;

class SuccessfulDriver implements AiDriver
{
    public function generateDailyLogInsights(DailyLog $log): DailyLogAiResult
    {
        return new DailyLogAiResult(
            summary: 'ok',
            tags: ['fallback'],
            coachTip: 'trust fallback',
            shareDraft: 'fallback-success',
            model: 'stub',
            latencyMs: 10,
            costUsd: 0.0,
        );
    }
}
