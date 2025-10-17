<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class DailyLogAiFailed
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        public readonly string $dailyLogId,
        public readonly ?string $message,
    ) {}
}
