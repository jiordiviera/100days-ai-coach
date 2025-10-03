<?php

use App\Livewire\Page\ChallengeShow;
use App\Mail\ChallengeInvitationMail;
use App\Models\ChallengeInvitation;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('envoie un e-mail lors de la création dune invitation', function () {
    Mail::fake();

    $owner = User::factory()->create();

    $run = ChallengeRun::create([
        'owner_id' => $owner->id,
        'title' => '100 Days of Code',
        'start_date' => Carbon::now()->subDay(),
        'target_days' => 100,
    ]);

    $this->actingAs($owner);

    $email = 'invited@example.test';

    Livewire::test(ChallengeShow::class, ['run' => $run])
        ->set('inviteForm.email', $email)
        ->call('sendInvite')
        ->assertHasNoErrors();

    expect(ChallengeInvitation::where('email', $email)->exists())->toBeTrue();

    Mail::assertQueued(ChallengeInvitationMail::class, function (ChallengeInvitationMail $mail) use ($email, $run) {
        return $mail->hasTo($email)
            && $mail->invitation->challenge_run_id === $run->id;
    });
});
