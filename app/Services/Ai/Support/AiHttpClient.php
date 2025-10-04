<?php

namespace App\Services\Ai\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AiHttpClient
{
    public function __construct(
        protected string $baseUrl,
        protected array $headers = [],
        protected ?string $apiKey = null,
    ) {}

    public function request(): PendingRequest
    {
        $headers = $this->headers;

        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer '.$this->apiKey;
        }

        return Http::baseUrl($this->baseUrl)
            ->retry(2, 200)
            ->acceptJson()
            ->withHeaders($headers);
    }

    public function measure(callable $callback): array
    {
        $start = microtime(true);
        $response = $callback();
        $latency = (int) round((microtime(true) - $start) * 1000);

        return [$response, $latency];
    }
}
