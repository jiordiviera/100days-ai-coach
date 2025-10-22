<?php

namespace App\Services\Telegram;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Notifications\Messages\TelegramMessage;
use Throwable;

class TelegramClient
{
    public function __construct(
        private readonly ?string $botToken = null,
        private readonly ?string $baseUrl = null,
    ) {}

    /**
     * @param  string  $chatId
     * @param  TelegramMessage|array|string  $message
     * @param  array<string, mixed>  $options
     *
     * @throws TelegramException
     */
    public function sendMessage(string $chatId, TelegramMessage|array|string $message, array $options = []): void
    {
        $token = $this->botToken ?? config('services.telegram.bot_token');

        if (blank($token)) {
            throw new TelegramException('Telegram bot token is not configured.');
        }

        $payload = $this->normalizeMessagePayload($message, $options);

        if (! isset($payload['text']) || blank($payload['text'])) {
            throw new TelegramException('Telegram message payload requires a non-empty text field.');
        }

        $baseUrl = Str::finish($this->baseUrl ?? config('services.telegram.base_url', 'https://api.telegram.org'), '/');

        try {
            Http::asForm()
                ->baseUrl($baseUrl)
                ->post("bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    ...$payload,
                ])
                ->throw();
        } catch (RequestException $exception) {
            $response = $exception->response;
            $message = $response?->json('description') ?? $exception->getMessage();

            throw new TelegramException($message, previous: $exception);
        } catch (Throwable $exception) {
            throw new TelegramException($exception->getMessage(), previous: $exception);
        }
    }

    /**
     * @param  TelegramMessage|array|string  $message
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function normalizeMessagePayload(TelegramMessage|array|string $message, array $options = []): array
    {
        if (is_string($message)) {
            $payload = ['text' => $message];
        } elseif ($message instanceof TelegramMessage) {
            $payload = $message->toArray();
        } else {
            $payload = $message;
        }

        if (! isset($payload['parse_mode'])) {
            $defaultParseMode = config('services.telegram.parse_mode');

            if ($defaultParseMode) {
                $payload['parse_mode'] = $defaultParseMode;
            }
        }

        return array_filter($payload + $options, static fn ($value) => ! is_null($value));
    }
}
