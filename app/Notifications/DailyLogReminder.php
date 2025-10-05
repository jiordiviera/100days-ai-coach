<?php

namespace App\Notifications;

use App\Models\ChallengeRun;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyLogReminder extends Notification
{
    use Queueable;

    public function __construct(public ChallengeRun $run) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->run->title ?? '100 Days of Code';

        return (new MailMessage)
            ->subject("N'oubliez pas votre journal 100DaysOfCode")
            ->greeting('Salut '.$notifiable->name.' !')
            ->line("Vous n'avez pas encore renseigné votre progression pour aujourd'hui dans le challenge \"{$title}\".")
            ->line('Chaque entrée compte pour garder votre streak !')
            ->action('Compléter mon journal', route('daily-challenge'))
            ->line('À très vite 👋');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'challenge_run_id' => $this->run->id,
        ];
    }
}
