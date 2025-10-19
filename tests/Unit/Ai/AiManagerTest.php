<?php

use App\Models\DailyLog;
use App\Services\Ai\AiManager;
use App\Services\Ai\Drivers\FailingDriver;
use App\Services\Ai\Drivers\SuccessfulDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    Http::fake();
    Cache::flush();
});

test('manager falls back to secondary driver on failure', function (): void {
    $log = new DailyLog([
        'day_number' => 1,
        'notes' => 'Falling back.',
    ]);

    config()->set('ai.default', 'groq');
    config()->set('ai.fallback', 'openai');
    config()->set('ai.drivers', [
        'groq' => FailingDriver::class,
        'openai' => SuccessfulDriver::class,
    ]);

    $manager = app(AiManager::class);

    $result = $manager->generateInsights($log);

    expect($result->shareDraft)->toBe('fallback-success');
});
