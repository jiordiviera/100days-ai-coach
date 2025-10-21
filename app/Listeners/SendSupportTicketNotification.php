<?php

namespace App\Listeners;

use App\Events\SupportTicketCreated;
use App\Notifications\SupportTicketReceived;
use Illuminate\Support\Facades\Notification;

class SendSupportTicketNotification
{
    public function handle(SupportTicketCreated $event): void
    {
        $recipients = config('support.team_recipients', []);

        if (empty($recipients) && config('mail.from.address')) {
            $recipients = [config('mail.from.address')];
        }

        $telegramRecipients = config('support.team_telegram_chat_ids', []);

        $recipients = array_filter(array_unique($recipients));
        $telegramRecipients = array_values(array_filter(array_unique($telegramRecipients)));

        if (empty($recipients) && empty($telegramRecipients)) {
            return;
        }

        foreach ($recipients as $email) {
            Notification::route('mail', $email)
                ->notify(new SupportTicketReceived($event->ticket));
        }

        foreach ($telegramRecipients as $chatId) {
            Notification::route('telegram', $chatId)
                ->notify(new SupportTicketReceived($event->ticket));
        }
    }
}
