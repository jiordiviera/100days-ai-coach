<?php

namespace App\Mail;

use App\Models\ChallengeInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChallengeInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public ChallengeInvitation $invitation)
    {
        $this->invitation->loadMissing('run.owner', 'inviter');
    }

    public function build(): self
    {
        $run = $this->invitation->run;
        $ownerName = $this->invitation->inviter?->name ?? $run?->owner?->name;
        $title = $run?->title ?: '100 Days of Code';

        return $this
            ->subject("Invitation à rejoindre le challenge {$title}")
            ->view('emails.challenges.invitation', [
                'invitation' => $this->invitation,
                'run' => $run,
                'ownerName' => $ownerName,
                'link' => route('challenges.accept', ['token' => $this->invitation->token]),
            ]);
    }
}
