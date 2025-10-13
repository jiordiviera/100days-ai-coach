<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnboardingDayZeroMail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $checklist = [
            ['label' => 'Consigner ton premier log', 'description' => 'Rédige ce que tu as shippé aujourd’hui pour lancer ta streak.'],
            ['label' => 'Associer un projet', 'description' => 'Lie ton log à un projet pour suivre tes missions.'],
            ['label' => 'Configurer ton rappel quotidien', 'description' => 'Choisis l’heure idéale pour recevoir un rappel automatique.'],
            ['label' => 'Préparer ton partage public', 'description' => 'Utilise les templates LinkedIn/X pour communiquer ta progression.'],
        ];

        $message = (new MailMessage)
            ->subject('Jour 0 – lance ta streak #100DaysOfCode')
            ->greeting('Hello '.($notifiable->name ?? 'Maker').' !')
            ->line('Ton espace est prêt. Voici les prochaines actions à accomplir pour bien démarrer :');

        foreach ($checklist as $item) {
            $message->line('• '.$item['label'].' — '.$item['description']);
        }

        $message
            ->action('Ouvrir le Daily Challenge', route('daily-challenge'))
            ->line('Besoin d’un rappel ? Tu peux modifier tes préférences à tout moment depuis Paramètres > Notifications.')
            ->salutation('À toi de jouer 💪');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
