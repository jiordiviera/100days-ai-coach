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
use Illuminate\Support\Str;

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
        $supportUrl = url('/admin/support-tickets');

        return (new MailMessage)
            ->subject('[Support] Nouveau feedback — '.$this->ticket->category)
            ->greeting('Hey équipe support,')
            ->line('Un nouveau ticket vient d’être soumis depuis la landing page.')
            ->line('Nom : '.$this->ticket->name)
            ->line('Email : '.$this->ticket->email)
            ->line('Catégorie : '.Str::title($this->ticket->category))
            ->line('Message :')
            ->line($this->ticket->message)
            ->action('Ouvrir Filament', $supportUrl)
            ->salutation('— 100Days AI Coach');
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $category = Str::title($this->ticket->category);

        $messageLines = collect(preg_split("/\r\n|\r|\n/", (string) $this->ticket->message) ?: [])
            ->map(static fn (string $line) => trim($line))
            ->filter()
            ->map(static fn (string $line) => e($line))
            ->all();

        $lines = [
            '<b>📥 Nouveau ticket support</b>',
            '',
            '<b>Catégorie :</b> '.e($category),
            '<b>Auteur :</b> '.e($this->ticket->name).' ('.e($this->ticket->email).')',
        ];

        if ($this->ticket->user_id) {
            $lines[] = '<b>Utilisateur ID :</b> '.e((string) $this->ticket->user_id);
        }

        $lines[] = '';
        $lines[] = '<b>Message :</b>';

        if ($messageLines === []) {
            $lines[] = e('(sans message)');
        } else {
            $lines = array_merge($lines, $messageLines);
        }

        $supportUrl = url('/admin/support-tickets');
        $lines[] = '<a href="'.e($supportUrl).'">Ouvrir dans Filament</a>';

        $content = implode("\n", $lines);

        return TelegramMessage::make()
            ->content($content)
            ->parseMode('HTML')
            ->disableWebPagePreview();
    }
}
