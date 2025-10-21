<?php

namespace App\Notifications;

use App\Models\ChallengeRun;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Services\Notifications\NotificationChannelResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class DailyReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected ChallengeRun $run,
        protected string $localDate,
        protected array $context = []
    ) {}

    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User) {
            return [];
        }

        $resolver = app(NotificationChannelResolver::class);

        $channels = $resolver->resolve($notifiable, 'daily_reminder');

        return collect($channels)
            ->map(fn (string $channel) => $channel === 'telegram' ? TelegramChannel::class : $channel)
            ->values()
            ->all();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $timezone = $this->context['timezone'] ?? 'Africa/Douala';
        $reminderTime = $this->context['reminder_time'] ?? '20:30';

        $localDate = Carbon::parse($this->localDate, $timezone);
        $startDate = Carbon::parse($this->run->start_date, $timezone);
        $dayNumber = max(1, $startDate->diffInDays($localDate) + 1);
        $targetDays = max(1, (int) $this->run->target_days);

        $statusLine = sprintf('Jour %d/%d · %s (%s)',
            min($dayNumber, $targetDays),
            $targetDays,
            $localDate->translatedFormat('d F Y'),
            $timezone
        );

        $ctaUrl = route('daily-challenge');

        return (new MailMessage)
            ->subject('Daily Reminder — '.$this->run->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Il est bientôt l’heure de consigner ta progression du jour.')
            ->line($statusLine)
            ->line('Tu n’as pas encore créé ton log pour aujourd’hui. Profite de ce rappel pour le faire pendant que ta mémoire est fraîche !')
            ->action('Ouvrir mon journal', $ctaUrl)
            ->line('Ce rappel est programmé pour '.$reminderTime.' dans ton fuseau horaire '.$timezone.'.')
            ->line('Bonne session et continue ta lancée 💪');
    }
}
