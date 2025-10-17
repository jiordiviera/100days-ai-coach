<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class DailyLogAiGenerated
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        public readonly string $dailyLogId,
        public readonly string $model,
        public readonly int $latencyMs,
        public readonly float $costUsd,
        public readonly array $metadata,
    ) {}
}
