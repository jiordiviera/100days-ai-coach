<?php

namespace App\Support\Onboarding;

use App\Models\ChallengeInvitation;
use App\Models\ChallengeRun;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OnboardUserAction
{
    public function execute(User $user, array $input): array
    {
        $reason = $input['join_reason'] ?? 'explore';

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'join_reason' => $reason,
                'focus_area' => $input['focus_area'] ?? null,
                'preferences' => [
                    'origin' => $input['origin'] ?? null,
                    'invite_code' => $input['invite_code'] ?? null,
                    'public_challenge_id' => $input['public_challenge_id'] ?? null,
                    'make_public' => (bool) ($input['make_public'] ?? false),
                ],
            ]
        );

        $messages = [];
        $createdChallenge = null;
        $joinedChallenge = null;

        switch ($reason) {
            case 'start_new':
                $createdChallenge = $this->createChallenge($user, $input);
                $messages[] = "Votre challenge « {$createdChallenge->title} » a été créé !";
                if ($createdChallenge->is_public) {
                    $messages[] = "Partagez le code public {$createdChallenge->public_join_code} pour inviter vos pairs.";
                }
                break;

            case 'join_code':
                $joinedChallenge = $this->joinViaCode($user, $input['invite_code'] ?? '');
                $messages[] = "Vous avez rejoint le challenge « {$joinedChallenge->title} ».";
                break;

            case 'join_public':
                $joinedChallenge = $this->joinPublic($user, $input['public_challenge_id'] ?? null);
                $messages[] = "Vous avez rejoint le challenge public « {$joinedChallenge->title} ».";
                break;

            default:
                $messages[] = 'Bienvenue ! Explorez votre tableau de bord pour démarrer.';
                break;
        }

        return [
            'messages' => $messages,
            'created_challenge_id' => $createdChallenge?->id,
            'joined_challenge_id' => $joinedChallenge?->id,
            'new_badges' => [],
        ];
    }

    protected function createChallenge(User $user, array $input): ChallengeRun
    {
        $title = trim($input['challenge_title'] ?? '') ?: 'Mon défi 100DaysOfCode';
        $targetDays = (int) ($input['target_days'] ?? 100);
        $targetDays = max(1, $targetDays);

        $isPublic = (bool) ($input['make_public'] ?? false);
        $publicCode = $isPublic ? $this->generatePublicCode() : null;

        return ChallengeRun::create([
            'owner_id' => $user->id,
            'title' => $title,
            'description' => $input['challenge_description'] ?? null,
            'start_date' => Carbon::today(),
            'target_days' => $targetDays,
            'status' => 'active',
            'is_public' => $isPublic,
            'public_join_code' => $publicCode,
        ]);
    }

    protected function joinViaCode(User $user, string $code): ChallengeRun
    {
        $code = trim($code);

        if ($code === '') {
            throw ValidationException::withMessages([
                'invite_code' => 'Merci de fournir un code valide.',
            ]);
        }

        $invitation = ChallengeInvitation::query()
            ->where('token', $code)
            ->with('run')
            ->first();

        if ($invitation) {
            if ($invitation->expires_at && $invitation->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'invite_code' => 'Ce lien d’invitation est expiré.',
                ]);
            }

            $run = $invitation->run;
            $this->attachToRun($user, $run);

            if (! $invitation->accepted_at) {
                $invitation->update(['accepted_at' => Carbon::now()]);
            }

            return $run;
        }

        $run = ChallengeRun::query()
            ->where('public_join_code', $code)
            ->where('is_public', true)
            ->first();

        if (! $run) {
            throw ValidationException::withMessages([
                'invite_code' => 'Aucun challenge ne correspond à ce code.',
            ]);
        }

        $this->attachToRun($user, $run);

        return $run;
    }

    protected function joinPublic(User $user, ?string $challengeId): ChallengeRun
    {
        if (! $challengeId) {
            throw ValidationException::withMessages([
                'public_challenge_id' => 'Sélectionnez un challenge public.',
            ]);
        }

        $run = ChallengeRun::query()
            ->whereKey($challengeId)
            ->where('is_public', true)
            ->first();

        if (! $run) {
            throw ValidationException::withMessages([
                'public_challenge_id' => 'Ce challenge public n’est plus disponible.',
            ]);
        }

        $this->attachToRun($user, $run);

        return $run;
    }

    protected function attachToRun(User $user, ChallengeRun $run): void
    {
        if ($run->owner_id === $user->id) {
            return;
        }

        $already = $run->participantLinks()->where('user_id', $user->id)->exists();

        if (! $already) {
            $run->participantLinks()->create([
                'user_id' => $user->id,
                'joined_at' => Carbon::now(),
            ]);
        }
    }

    protected function generatePublicCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (ChallengeRun::where('public_join_code', $code)->exists());

        return $code;
    }
}

