<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected SupportTicket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
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
            ->action('Ouvrir Filament', url('/admin/support-tickets'))
            ->salutation('— 100Days AI Coach');
    }
}
