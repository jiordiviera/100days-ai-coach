<?php

namespace App\Services\Ai\Drivers;

use App\Models\DailyLog;
use App\Services\Ai\Contracts\AiDriver;
use App\Services\Ai\Dto\DailyLogAiResult;
use App\Services\Ai\Support\AiHttpClient;
use App\Services\Ai\Support\AiResponseParser;
use App\Services\Ai\Support\DailyLogPromptBuilder;
use Illuminate\Support\Arr;
use RuntimeException;

class OpenAiAiDriver implements AiDriver
{
    public function __construct(
        protected DailyLogPromptBuilder $promptBuilder,
    ) {}

    public function generateDailyLogInsights(DailyLog $log): DailyLogAiResult
    {
        $config = config('services.ai.openai');

        if (empty($config['api_key'])) {
            throw new RuntimeException('Missing OpenAI API key.');
        }

        $client = new AiHttpClient(
            baseUrl: rtrim($config['base_url'], '/'),
            headers: [
                'Content-Type' => 'application/json',
            ],
            apiKey: $config['api_key'],
        );

        $payload = [
            'model' => $config['model'],
            'temperature' => 0.7,
            'messages' => [
                ['role' => 'system', 'content' => $this->promptBuilder->buildSystemPrompt()],
                ['role' => 'user', 'content' => $this->promptBuilder->buildUserPrompt($log)],
            ],
        ];

        [$response, $latency] = $client->measure(fn () => $client->request()->post('chat/completions', $payload));

        $response->throw();

        $body = $response->json();
        $structured = AiResponseParser::extractStructuredPayload($body);

        $tokens = (int) Arr::get($body, 'usage.total_tokens', 0);
        $rate = (float) ($config['cost_per_1k_tokens'] ?? 0);
        $cost = $tokens > 0 && $rate > 0 ? round(($tokens / 1000) * $rate, 3) : 0.0;

        return new DailyLogAiResult(
            summary: (string) ($structured['summary_md'] ?? ''),
            tags: array_values(array_filter((array) ($structured['tags'] ?? []))),
            coachTip: (string) ($structured['coach_tip'] ?? ''),
            shareDraft: (string) ($structured['share_draft'] ?? ''),
            model: (string) $config['model'],
            latencyMs: $latency,
            costUsd: $cost,
        );
    }
}
