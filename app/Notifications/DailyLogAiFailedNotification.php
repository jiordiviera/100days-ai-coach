<?php

namespace App\Notifications;

use App\Models\DailyLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyLogAiFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected DailyLog $dailyLog,
        protected ?string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $day = $this->dailyLog->day_number ?? 'inconnu';

        return (new MailMessage)
            ->subject('Échec de la génération IA — journal jour '.$day)
            ->greeting('Hello '.$notifiable->name.' !')
            ->line('La génération IA de ton journal du jour '.$day.' n’a pas abouti.')
            ->line($this->message ? 'Détail : '.$this->message : 'Aucun détail supplémentaire n’a été fourni.')
            ->line('L’application a généré un résumé de secours, mais tu peux relancer la génération depuis la page Daily Challenge.')
            ->action('Ouvrir le Daily Challenge', route('daily-challenge'))
            ->salutation('À très vite sur '.config('app.name').' !');
    }
}
