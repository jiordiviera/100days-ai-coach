<?php

namespace App\Services\Ai\Drivers;

use App\Models\DailyLog;
use App\Services\Ai\Contracts\AiDriver;
use App\Services\Ai\Dto\DailyLogAiResult;
use RuntimeException;

class GroqAiDriver implements AiDriver
{
    public function generateDailyLogInsights(DailyLog $log): DailyLogAiResult
    {
        throw new RuntimeException('Groq AI driver is not implemented yet.');
    }
}
