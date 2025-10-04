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
        //        dd($run);
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
            $this->addError('inviteForm.email', 'Adresse e-mail requise.');

            Notification::make()
                ->title('Email manquant')
                ->body('Indiquez l\'adresse e-mail de la personne à inviter.')
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        // Already participant?
        $already = $this->run->participants()->where('email', $email)->exists();
        if ($already) {
            $this->addError('inviteForm.email', 'Cet utilisateur participe déjà.');

            Notification::make()
                ->title('Déjà participant')
                ->body('Cette personne fait déjà partie du challenge.')
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        // Already engaged in another active challenge?
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $this->userHasActiveChallenge($existingUser->id)) {
            $this->addError('inviteForm.email', 'Cette personne participe déjà à un challenge actif.');

            Notification::make()
                ->title('Invitation impossible')
                ->body('Cette personne mène déjà un challenge actif et ne peut pas en rejoindre un nouveau pour le moment.')
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
            $this->addError('inviteForm.email', 'Invitation déjà envoyée.');

            Notification::make()
                ->title('Invitation déjà envoyée')
                ->body('Une invitation en attente existe déjà pour cette adresse e-mail.')
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
                ->title('E-mail non envoyé')
                ->body('Le lien a été généré, mais l\'e-mail n\'a pas pu être envoyé automatiquement.')
                ->warning()
                ->send();
        }
        session()->flash('message', 'Invitation créée. Partagez le lien de participation.');
        Notification::make()
            ->title('Invitation créée')
            ->body('Un lien d\'invitation a été généré. Partagez-le avec la personne invitée.')
            ->success()
            ->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->helperText('Saisissez l\'adresse de la personne à inviter.'),
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
                'percent' => round(min(100, $done / $target * 100), 1),
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
        $todayDay = Carbon::now()->diffInDays(Carbon::parse($start)) + 1;
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
        $participantsCount = max(1, $this->run->participantLinks()->count());
        $totalDone = DailyLog::where('challenge_run_id', $this->run->id)->count();
        $globalPercent = round(min(100, ($totalDone / ($participantsCount * max(1, (int) $this->run->target_days))) * 100), 1);

        // My done days set for calendar
        $myDoneDays = DailyLog::where('challenge_run_id', $this->run->id)
            ->where('user_id', auth()->id())
            ->pluck('day_number')
            ->all();
        $myDoneDays = array_map('intval', $myDoneDays);

        return view('livewire.page.challenge-show', [
            'progress' => $this->progress,
            'pendingInvites' => $pendingInvites,
            'myRecentLogs' => $myRecentLogs,
            'globalPercent' => $globalPercent,
            'participantsCount' => $participantsCount,
            'myDoneDays' => $myDoneDays,
        ]);
    }

    public function removeParticipant(string $participantId): void
    {
        abort_unless(auth()->id() === $this->run->owner_id, 403);
        $link = $this->run->participantLinks()->whereKey($participantId)->firstOrFail();
        // Ne pas retirer l'owner par ce chemin
        if ($link->user_id === $this->run->owner_id) {
            $this->addError('inviteForm.email', "Vous ne pouvez pas retirer l'owner.");

            return;
        }
        $link->delete();
        $this->run->refresh()->load('participantLinks.user');
        session()->flash('message', 'Participant retiré.');
        Notification::make()
            ->title('Participant retiré')
            ->body('L\'utilisateur a été retiré du challenge.')
            ->warning()
            ->send();
    }

    public function leave(): void
    {
        // L'owner ne peut pas quitter via cette action
        abort_if(auth()->id() === $this->run->owner_id, 403);
        $this->run->participantLinks()->where('user_id', auth()->id())->delete();
        session()->flash('message', 'Vous avez quitté le challenge.');
        redirect()->route('challenges.index');
    }

    public function copyLink(string $link): void
    {
        $this->js("
        console.log(navigator);
            if (navigator.clipboard) {
                navigator.clipboard.writeText('{$link}');
            } else {
                alert('La copie automatique n\'est pas supportée sur ce navigateur.');
            }
        ");

        Notification::make()
            ->title('Lien copié')
            ->body('Le lien d\'invitation a été copié dans votre presse-papier. Partagez-le !')
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
                ->title('Invitation révoquée')
                ->body('L\'invitation a été supprimée et le lien ne fonctionnera plus.')
                ->warning()
                ->send();
        }
        // refresh pending
        $this->run->refresh();
    }
}
