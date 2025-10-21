<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Messages\TelegramMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected SupportTicket $ticket) {}

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return array_values(array_filter([
                $notifiable->routeNotificationFor('mail') ? 'mail' : null,
                $notifiable->routeNotificationFor('telegram') ? TelegramChannel::class : null,
            ]));
        }

        $channels = ['mail'];

        if (method_exists($notifiable, 'routeNotificationForTelegram') && $notifiable->routeNotificationForTelegram($this)) {
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Support] Nouveau feedback — '.$this->ticket->category)
            ->greeting('Hey équipe support,')
            ->line('Un nouveau ticket vient d’être soumis depuis la landing page.')
            ->line('Nom : '.$this->ticket->name)
            ->line('Email : '.$this->ticket->email)
            ->line('Catégorie : '.ucfirst($this->ticket->category))
            ->line('Message :')
            ->line($this->ticket->message)
            ->action('Ouvrir Filament', route('filament.admin.resources.support-tickets.index'))
            ->salutation('— 100Days AI Coach');
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $category = ucfirst($this->ticket->category);

        $messageBody = e($this->ticket->message);
        $messageBody = str_replace(["\r\n", "\n", "\r"], '<br>', $messageBody);

        $content = implode('<br>', array_filter([
            '<b>📥 Nouveau ticket support</b>',
            'Catégorie : '.e($category),
            'Auteur : '.e($this->ticket->name).' ('.e($this->ticket->email).')',
            $this->ticket->user_id ? 'Utilisateur ID : '.e((string) $this->ticket->user_id) : null,
            'Message :',
            $messageBody,
            '<a href="'.e(route('filament.admin.resources.support-tickets.index')).'">Ouvrir dans Filament</a>',
        ]));

        return TelegramMessage::make()
            ->content($content)
            ->parseMode('HTML')
            ->disableWebPagePreview();
    }
}
