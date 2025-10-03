<?php

namespace App\Services\Ai\Drivers;

use App\Models\DailyLog;
use App\Services\Ai\Contracts\AiDriver;
use App\Services\Ai\Dto\DailyLogAiResult;
use RuntimeException;

class OpenAiAiDriver implements AiDriver
{
    public function generateDailyLogInsights(DailyLog $log): DailyLogAiResult
    {
        throw new RuntimeException('OpenAI driver is not implemented yet.');
    }
}
