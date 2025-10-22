<?php
namespace App\Services\Telegram;

use App\Notifications\Messages\TelegramMessage;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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
        $payload = $this->normalisePayload($message, $options);

        if (! isset($payload['text']) || blank($payload['text'])) {
            throw new TelegramException('Telegram message payload requires a non-empty text field.');
        }

        $this->post('sendMessage', [
            'chat_id' => $chatId,
            ...$payload,
        ]);
    }

    /**
     * Acknowledge a callback query to stop Telegram's loading spinner.
     *
     * @throws TelegramException
     */
    public function answerCallbackQuery(string $callbackId, ?string $text = null, bool $showAlert = false): void
    {
        $payload = array_filter([
            'callback_query_id' => $callbackId,
            'text' => $text,
            'show_alert' => $showAlert,
        ], static fn ($value) => ! is_null($value));

        $this->post('answerCallbackQuery', $payload);
    }

    /**
     * @param  TelegramMessage|array|string  $message
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function normalisePayload(TelegramMessage|array|string $message, array $options = []): array
    {
        if (is_string($message)) {
            $payload = ['text' => $message];
        } elseif ($message instanceof TelegramMessage) {
            $payload = $message->toArray();
        } else {
            $payload = $message;
        }

        $payload += $options;

        if (! isset($payload['parse_mode'])) {
            $defaultParseMode = config('services.telegram.parse_mode');

            if ($defaultParseMode) {
                $payload['parse_mode'] = $defaultParseMode;
            }
        }

        if (isset($payload['reply_markup']) && is_array($payload['reply_markup'])) {
            $payload['reply_markup'] = json_encode($payload['reply_markup'], JSON_THROW_ON_ERROR);
        }

        return array_filter($payload, static fn ($value) => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws TelegramException
     */
    protected function post(string $method, array $payload): void
    {
        $token = $this->botToken ?? config('services.telegram.bot_token');

        if (blank($token)) {
            throw new TelegramException('Telegram bot token is not configured.');
        }

        $baseUrl = Str::finish($this->baseUrl ?? config('services.telegram.base_url', 'https://api.telegram.org'), '/');

        try {
            Http::asForm()
                ->baseUrl($baseUrl)
                ->post("bot{$token}/{$method}", $payload)
                ->throw();
        } catch (RequestException $exception) {
            $response = $exception->response;
            $message = $response?->json('description') ?? $exception->getMessage();

            throw new TelegramException($message, previous: $exception);
        } catch (Throwable $exception) {
            throw new TelegramException($exception->getMessage(), previous: $exception);
        }
    }
}
