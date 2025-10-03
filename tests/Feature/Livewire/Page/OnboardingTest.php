<?php

use App\Livewire\Page\Onboarding;
use App\Models\ChallengeInvitation;
use App\Models\ChallengeRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows an invited user to accept a pending invitation from onboarding', function () {
    $owner = User::factory()->create();
    $invitee = User::factory()->create([
        'email' => 'invitee@example.test',
    ]);

    $run = ChallengeRun::create([
        'owner_id' => $owner->id,
        'title' => 'Daily Shipping',
        'start_date' => Carbon::today()->subDay(),
        'target_days' => 100,
    ]);

    $invitation = ChallengeInvitation::create([
        'challenge_run_id' => $run->id,
        'inviter_id' => $owner->id,
        'email' => $invitee->email,
        'token' => 'ONBOARD123',
    ]);

    $this->actingAs($invitee);

    Livewire::test(Onboarding::class)
        ->call('acceptInvitation', $invitation->id)
        ->assertRedirect(route('dashboard'));

    $invitee->refresh();
    $invitation->refresh();

    expect($invitee->profile)->not->toBeNull();
    expect($run->participantLinks()->where('user_id', $invitee->id)->exists())->toBeTrue();
    expect($invitation->accepted_at)->not->toBeNull();
});
