<?php

namespace App\Notifications\Channels;

use App\Notifications\Messages\TelegramMessage;
use App\Services\Telegram\TelegramClient;
use App\Services\Telegram\TelegramException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramChannel
{
    public function __construct(
        private readonly TelegramClient $client,
    ) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        $chatId = $notifiable->routeNotificationFor('telegram', $notification);

        if (blank($chatId)) {
            return;
        }

        $message = $notification->toTelegram($notifiable);

        if (blank($message)) {
            return;
        }

        $payload = $message instanceof TelegramMessage ? $message : (array) $message;

        try {
            $this->client->sendMessage((string) $chatId, $payload);
        } catch (TelegramException $exception) {
            Log::warning('Failed to send Telegram notification', [
                'chat_id' => $chatId,
                'notification' => $notification::class,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Unexpected error while sending Telegram notification', [
                'chat_id' => $chatId,
                'notification' => $notification::class,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
