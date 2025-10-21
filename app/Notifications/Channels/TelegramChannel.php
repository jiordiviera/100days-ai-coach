<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Notifications\Messages\TelegramMessage;
use App\Services\Telegram\TelegramClient;
use App\Services\Telegram\TelegramException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TelegramChannel
{
    public function __construct(
        private readonly TelegramClient $client,
    ) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        /**
         * @var User $notifiable
         */
        $chatId = $notifiable->routeNotificationFor('telegram', $notification);

        if (blank($chatId)) {
            return;
        }

        $message = $notification->toTelegram($notifiable);

        if (blank($message)) {
            return;
        }

        try {
            $this->client->sendMessage($chatId, $message instanceof TelegramMessage ? $message : (array) $message);
        } catch (TelegramException $exception) {
            Log::channel('stack')->warning('Failed to send Telegram notification', [
                'chat_id' => $chatId,
                'notification' => $notification::class,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
