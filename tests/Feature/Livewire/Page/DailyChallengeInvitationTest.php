<?php

use App\Livewire\Page\DailyChallenge;
use App\Models\ChallengeInvitation;
use App\Models\ChallengeRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createProfile(User $user): void
{
    $user->profile()->create([
        'join_reason' => 'self_onboarding',
        'focus_area' => null,
        'preferences' => $user->profilePreferencesDefaults(),
    ]);
}

test('daily challenge lists pending invitations and allows acceptance', function (): void {
    $owner = User::factory()->create();
    createProfile($owner);

    $run = ChallengeRun::factory()->for($owner, 'owner')->create([
        'title' => 'Team Sprint',
        'status' => 'active',
        'public_join_code' => 'TEAMCODE',
    ]);

    $invitee = User::factory()->create(['email' => 'guest@example.test']);
    createProfile($invitee);

    $invitation = ChallengeInvitation::create([
        'challenge_run_id' => $run->id,
        'inviter_id' => $owner->id,
        'email' => $invitee->email,
        'token' => 'INVITE123',
    ]);

    Livewire::actingAs($invitee)
        ->test(DailyChallenge::class)
        ->assertSee('Invitations en attente')
        ->call('acceptInvitation', $invitation->id)
        ->assertSet('challengeRunId', $run->id);
});

test('daily challenge join via public code attaches user to run', function (): void {
    $user = User::factory()->create();
    createProfile($user);

    $run = ChallengeRun::factory()->create([
        'title' => 'Public Challenge',
        'is_public' => true,
        'public_join_code' => 'PUBLIC42',
        'status' => 'active',
    ]);

    Livewire::actingAs($user)
        ->test(DailyChallenge::class)
        ->set('inviteCode', 'public42')
        ->call('joinWithCode')
        ->assertSet('challengeRunId', $run->id);
});
