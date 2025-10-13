<?php

namespace App\Listeners;

use App\Events\SupportTicketCreated;
use App\Notifications\SupportTicketReceived;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

class SendSupportTicketNotification
{
    public function handle(SupportTicketCreated $event): void
    {
        $recipients = config('support.team_recipients', []);

        if (empty($recipients) && config('mail.from.address')) {
            $recipients = [config('mail.from.address')];
        }

        $recipients = array_filter(array_unique($recipients));

        if (empty($recipients)) {
            return;
        }

        foreach ($recipients as $email) {
            Notification::route('mail', $email)
                ->notify(new SupportTicketReceived($event->ticket));
        }
    }
}
