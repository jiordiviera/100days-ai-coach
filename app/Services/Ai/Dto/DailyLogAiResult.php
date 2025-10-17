<?php

namespace App\Services\Ai\Dto;

readonly class DailyLogAiResult
{
    public function __construct(
        public string $summary,
        public array  $tags,
        public string $coachTip,
        public string $shareDraft,
        public string $model,
        public int    $latencyMs,
        public float  $costUsd,
        public array  $metadata = [],
    ) {}
}
