<?php

namespace App\Services\Ai\Contracts;

use App\Models\DailyLog;
use App\Services\Ai\Dto\DailyLogAiResult;

interface AiDriver
{
    public function generateDailyLogInsights(DailyLog $log): DailyLogAiResult;
}
