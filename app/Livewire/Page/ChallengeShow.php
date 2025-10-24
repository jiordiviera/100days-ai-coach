<?php

namespace App\Livewire\Page;

use App\Mail\ChallengeInvitationMail;
use App\Models\ChallengeInvitation;
use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Title('Challenge')]
#[Layout('components.layouts.app')]
class ChallengeShow extends Component implements HasForms
{
    use InteractsWithForms;

    public ChallengeRun $run;

    public ?array $inviteForm = [];

    public ?string $lastInviteLink = null;

    public function mount(ChallengeRun $run): void
    {
        $this->run = $run->load('participantLinks.user', 'owner');
        abort_unless($this->canView(), 403);

        $this->form->fill();
    }

    protected function canView(): bool
    {
        $user = auth()->user();
        if ($user->id === $this->run->owner_id) {
            return true;
        }

        return $this->run->participantLinks->contains(fn ($p) => $p->user_id === $user->id);
    }

    public function sendInvite(): void
    {
        abort_unless(auth()->id() === $this->run->owner_id, 403);

        $data = $this->form->getState();
        $email = strtolower($data['email'] ?? '');

        if (! $email) {
            $this->addError('inviteForm.email', __('Email address required.'));

            Notification::make()
                ->title(__('Email missing'))
                ->body(__('Provide the email address of the person to invite.'))
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        // Already participant?
        $already = $this->run->participants()->where('email', $email)->exists();
        if ($already) {
            $this->addError('inviteForm.email', __('This user is already part of the challenge.'));

            Notification::make()
                ->title(__('Already a participant'))
                ->body(__('This person is already part of the challenge.'))
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        // Already engaged in another active challenge?
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $this->userHasActiveChallenge($existingUser->id)) {
            $this->addError('inviteForm.email', __('This person already participates in an active challenge.'));

            Notification::make()
                ->title(__('Invitation not available'))
                ->body(__('This person already runs an active challenge and cannot join a new one right now.'))
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        // Existing pending invitation?
        $pending = ChallengeInvitation::where('challenge_run_id', $this->run->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->exists();
        if ($pending) {
            $this->addError('inviteForm.email', __('Invitation already sent.'));

            Notification::make()
                ->title(__('Invitation already sent'))
                ->body(__('A pending invitation already exists for this email address.'))
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        $token = (string) Str::ulid();
        $inv = ChallengeInvitation::create([
            'challenge_run_id' => $this->run->id,
            'inviter_id' => auth()->id(),
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        $this->lastInviteLink = route('challenges.accept', ['token' => $token]);
        $this->form->fill();

        try {
            Mail::to($email)->queue(new ChallengeInvitationMail($inv));
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('Email not sent'))
                ->body(__('The link was generated, but the email could not be sent automatically.'))
                ->warning()
                ->send();
        }
        session()->flash('message', __('Invitation created. Share the invite link.'));
        Notification::make()
            ->title(__('Invitation created'))
            ->body(__('An invitation link was generated. Share it with the invitee.'))
            ->success()
            ->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->helperText(__('Enter the email of the person to invite.')),
            ])
            ->statePath('inviteForm');
    }

    protected function userHasActiveChallenge(string $userId): bool
    {
        $ownsActive = ChallengeRun::query()
            ->where('owner_id', $userId)
            ->where('status', 'active')
            ->exists();

        if ($ownsActive) {
            return true;
        }

        return ChallengeRun::query()
            ->where('status', 'active')
            ->whereHas('participantLinks', fn ($relation) => $relation->where('user_id', $userId))
            ->exists();
    }

    public function getProgressProperty(): array
    {
        $target = max(1, (int) $this->run->target_days);
        $byUser = [];
        foreach ($this->run->participantLinks as $link) {
            $u = $link->user;
            if (! $u) {
                continue;
            }
            $done = DailyLog::where('challenge_run_id', $this->run->id)
                ->where('user_id', $u->id)
                ->count();
            $streak = $this->computeStreak($u->id);
            $byUser[] = [
                'user' => $u,
                'done' => $done,
                'percent' => (int) round(min(100, $done / $target * 100)),
                'streak' => $streak,
            ];
        }

        return $byUser;
    }

    protected function computeStreak(string $userId): int
    {
        // Current streak counted from today's expected day number backwards
        $start = $this->run->start_date;
        if (! $start) {
            return 0;
        }

        $todayDay = (int) (Carbon::parse($start)->startOfDay()->diffInDays(Carbon::now()->startOfDay()) + 1);
        // Build a set of done day_numbers
        $days = DailyLog::where('challenge_run_id', $this->run->id)
            ->where('user_id', $userId)
            ->pluck('day_number')
            ->all();
        $doneSet = array_fill_keys($days, true);
        $streak = 0;
        for ($d = $todayDay; $d >= 1; $d--) {
            if (! isset($doneSet[$d])) {
                break;
            }
            $streak++;
        }

        return $streak;
    }

    public function toggleVisibility(): void
    {
        abort_unless(auth()->id() === $this->run->owner_id, 403);

        $this->run->is_public = ! $this->run->is_public;

        if ($this->run->is_public) {
            $this->run->ensurePublicSlug();

            if (blank($this->run->public_join_code)) {
                $this->run->public_join_code = Str::upper(Str::random(6));
            }
        }

        $this->run->save();
        $this->run->refresh();

        if ($this->run->public_slug) {
            Cache::forget('public-challenge:'.$this->run->public_slug);
        }

        Notification::make()
            ->title($this->run->is_public ? __('Challenge set to public') : __('Challenge set to private'))
            ->body($this->run->is_public ? __('The public page is now accessible.') : __('The public page has been disabled.'))
            ->success()
            ->send();
    }

    public function render(): View
    {
        $pendingInvites = [];
        if (auth()->id() === $this->run->owner_id) {
            $pendingInvites = ChallengeInvitation::where('challenge_run_id', $this->run->id)
                ->whereNull('accepted_at')
                ->latest()
                ->get();
        }

        // Derniers logs de l'utilisateur connecté pour ce challenge
        $myRecentLogs = DailyLog::where('challenge_run_id', $this->run->id)
            ->where('user_id', auth()->id())
            ->latest('day_number')
            ->take(10)
            ->get();
        //        dd($this->run->participantLinks);
        // Global progression
        $participantsCountActual = $this->run->participantLinks()->count();
        $participantsCountForPercent = max(1, $participantsCountActual);
        $totalDone = DailyLog::where('challenge_run_id', $this->run->id)->count();
        $globalPercent = (int) round(min(100, ($totalDone / ($participantsCountForPercent * max(1, (int) $this->run->target_days))) * 100));

        // My done days set for calendar
        $myDoneDays = DailyLog::where('challenge_run_id', $this->run->id)
            ->where('user_id', auth()->id())
            ->pluck('day_number')
            ->all();
        $myDoneDays = array_map('intval', $myDoneDays);

        $activeDayNumber = null;
        $daysRemaining = null;
        $startDate = $this->run->start_date;
        if ($startDate) {
            $activeDayNumber = (int) (Carbon::parse($startDate)->startOfDay()->diffInDays(Carbon::now()->startOfDay()) + 1);
            $activeDayNumber = max(1, min($this->run->target_days, $activeDayNumber));
            $daysRemaining = max(0, $this->run->target_days - $activeDayNumber);
        }

        $myStreak = $this->computeStreak(auth()->id());

        return view('livewire.page.challenge-show', [
            'progress' => $this->progress,
            'pendingInvites' => $pendingInvites,
            'myRecentLogs' => $myRecentLogs,
            'globalPercent' => $globalPercent,
            'participantsCount' => $participantsCountActual,
            'myDoneDays' => $myDoneDays,
            'activeDayNumber' => $activeDayNumber,
            'daysRemaining' => $daysRemaining,
            'myStreak' => $myStreak,
        ]);
    }

    public function removeParticipant(string $participantId): void
    {
        abort_unless(auth()->id() === $this->run->owner_id, 403);
        $link = $this->run->participantLinks()->whereKey($participantId)->firstOrFail();
        // Ne pas retirer l'owner par ce chemin
        if ($link->user_id === $this->run->owner_id) {
            $this->addError('inviteForm.email', __('You cannot remove the owner.'));

            return;
        }
        $link->delete();
        $this->run->refresh()->load('participantLinks.user');
        session()->flash('message', __('Participant removed.'));
        Notification::make()
            ->title(__('Participant removed'))
            ->body(__('The user has been removed from the challenge.'))
            ->warning()
            ->send();
    }

    public function leave(): void
    {
        // L'owner ne peut pas quitter via cette action
        abort_if(auth()->id() === $this->run->owner_id, 403);
        $this->run->participantLinks()->where('user_id', auth()->id())->delete();
        session()->flash('message', __('You left the challenge.'));
        redirect()->route('challenges.index');
    }

    public function copyLink(string $link): void
    {
        $linkJs = json_encode($link);
        $messageJs = json_encode(__('Automatic copy is not supported on this browser.'));

        $this->js(<<<JS
            if (navigator.clipboard) {
                navigator.clipboard.writeText({$linkJs});
            } else {
                alert({$messageJs});
            }
        JS);

        Notification::make()
            ->title(__('Link copied'))
            ->body(__('The invitation link was copied to your clipboard. Share it!'))
            ->success()
            ->send();
    }

    public function revokeInvite(string $inviteId): void
    {
        abort_unless(auth()->id() === $this->run->owner_id, 403);
        $inv = ChallengeInvitation::where('challenge_run_id', $this->run->id)
            ->whereKey($inviteId)
            ->whereNull('accepted_at')
            ->first();
        if ($inv) {
            $inv->delete();
            Notification::make()
                ->title(__('Invitation revoked'))
                ->body(__('The invitation was deleted and the link will no longer work.'))
                ->warning()
                ->send();
        }
        // refresh pending
        $this->run->refresh();
    }
}
