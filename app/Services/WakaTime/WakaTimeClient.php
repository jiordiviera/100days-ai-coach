<?php

namespace App\Services\WakaTime;

use App\Services\WakaTime\Dto\WakaTimeSummary;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

class WakaTimeClient
{
    public function __construct(
        private readonly ?string $baseUri = null,
        private readonly ?int $timeout = null,
    ) {}

    public function summary(string $apiKey, CarbonInterface $date, string $timezone): WakaTimeSummary
    {
        try {
            $response = $this->request($apiKey)
                ->acceptJson()
                ->retry(2, 250, fn ($exception) => $exception instanceof ConnectionException)
                ->get('users/current/summaries', [
                    'start' => $date->format('Y-m-d'),
                    'end' => $date->format('Y-m-d'),
                    'timezone' => $timezone,
                ]);
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $message = $status === 401
                ? 'Invalid WakaTime API key.'
                : ($exception->response?->json('errors.0') ?? $exception->getMessage());

            throw new WakaTimeException($message, previous: $exception);
        } catch (Throwable $exception) {
            throw new WakaTimeException('WakaTime request failed: '.$exception->getMessage(), previous: $exception);
        }

        if ($response->status() === 401) {
            throw new WakaTimeException('Invalid WakaTime API key.');
        }

        if ($response->failed()) {
            throw new WakaTimeException('Failed to fetch WakaTime summary: '.$response->body());
        }

        $payload = $response->json();
        $data = $payload['data'][0] ?? null;

        if (! $data) {
            throw new WakaTimeException('No summary data returned.');
        }

        $grandTotal = $data['grand_total'] ?? [];
        $projects = $data['projects'] ?? [];
        $languages = $data['languages'] ?? [];
        $totalSeconds = (int) ($grandTotal['total_seconds'] ?? 0);

        return new WakaTimeSummary(
            date: $data['range']['date'] ?? $date->format('Y-m-d'),
            timezone: $timezone,
            totalSeconds: $totalSeconds,
            grandTotal: $grandTotal,
            projects: $projects,
            languages: $languages,
            raw: $data,
        );
    }

    protected function request(string $apiKey): PendingRequest
    {
        $uri = $this->baseUri ?? config('services.wakatime.base_uri', 'https://wakatime.com/api/v1/');
        $timeout = $this->timeout ?? config('services.wakatime.timeout', 10);

        return Http::baseUrl(rtrim($uri, '/').'/')
            ->timeout($timeout)
            ->withHeaders([
                'Authorization' => 'Basic '.base64_encode($apiKey.':'),
            ]);
    }
}
