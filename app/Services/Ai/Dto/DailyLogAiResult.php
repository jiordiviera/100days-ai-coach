<?php

namespace App\Services\Ai\Dto;

class DailyLogAiResult
{
    public function __construct(
        public readonly string $summary,
        public readonly array $tags,
        public readonly string $coachTip,
        public readonly string $shareDraft,
        public readonly string $model,
        public readonly int $latencyMs,
        public readonly float $costUsd,
    ) {
    }
}
