<?php

use App\Models\ChallengeInvitation;
use App\Models\ChallengeRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('creates a profile and attaches the invited user when accepting via link', function () {
    $owner = User::factory()->create();
    $invitee = User::factory()->create([
        'email' => 'invitee@example.test',
    ]);

    $run = ChallengeRun::create([
        'owner_id' => $owner->id,
        'title' => 'Collaborative Challenge',
        'start_date' => Carbon::today(),
        'target_days' => 100,
    ]);

    $invitation = ChallengeInvitation::create([
        'challenge_run_id' => $run->id,
        'inviter_id' => $owner->id,
        'email' => $invitee->email,
        'token' => 'INVITE123',
    ]);

    $this->actingAs($invitee);

    $response = $this->get(route('challenges.accept', ['token' => $invitation->token]));

    $response->assertRedirect(route('challenges.show', ['run' => $run->id]));

    $invitee->refresh();
    $invitation->refresh();

    expect($invitee->profile)->not->toBeNull();
    expect($run->participantLinks()->where('user_id', $invitee->id)->exists())->toBeTrue();
    expect($invitation->accepted_at)->not->toBeNull();
});
