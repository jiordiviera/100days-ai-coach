<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class WeeklyDigestNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Carbon $weekStart,
        protected Carbon $weekEnd,
        protected array $metrics,
        protected array $ai,
        protected string $timezone
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $durationLabel = sprintf('%s – %s (%s)',
            $this->weekStart->translatedFormat('d F'),
            $this->weekEnd->translatedFormat('d F Y'),
            $this->timezone
        );

        $message = (new MailMessage)
            ->subject('Digest hebdomadaire #100DaysOfCode')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Voici un aperçu de ta semaine de code :')
            ->line('Période : '.$durationLabel)
            ->line($this->metrics['total_hours'].' h cumulées · '.$this->metrics['log_count'].' entrées');

        if (! empty($this->ai['summary'])) {
            $message->line('Résumé IA :')
                ->line($this->ai['summary']);
        }

        if (! empty($this->ai['tags'])) {
            $message->line('Points clés : '.implode(', ', $this->ai['tags']));
        }

        if (! empty($this->ai['coach_tip'])) {
            $message->line('Conseil pour la semaine prochaine :')
                ->line($this->ai['coach_tip']);
        }

        if (! empty($this->ai['share_draft'])) {
            $message->line('Idée de post :')
                ->line($this->ai['share_draft']);
        }

        $message->action('Compléter mon journal', route('daily-challenge'))
            ->line('Continue sur ta lancée et vise encore plus haut la semaine prochaine !');

        return $message;
    }
}
