<?php

namespace App\Livewire\Page;

use App\Models\ChallengeRun;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Challenges')]
#[Layout('components.layouts.app')]
class ChallengeIndex extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $challengeForm = [];

    public bool $hasActiveChallenge = false;

    public function mount(): void
    {
        $this->form->fill([
            'title' => '',
            'start_date' => now()->toDateString(),
            'target_days' => 100,
            'is_public' => false,
        ]);

        $this->refreshEligibility();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('challengeForm')
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label(__('Title'))
                    ->placeholder('100 Days of Code')
                    ->default(__('My 100DaysOfCode challenge'))
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('Description (optional)'))
                    ->rows(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->label(__('Start date'))
                    ->native(false)
                    ->required()
                    ->default(now()->toDateString()),
                TextInput::make('target_days')
                    ->label(__('Number of days'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(365)
                    ->required(),
                Toggle::make('is_public')
                    ->label(__('Make it public'))
                    ->inline(false)
                    ->columnSpanFull(),
            ]);
    }

    public function create()
    {
        $activeRun = $this->fetchActiveRun();

        if ($activeRun) {
            $this->hasActiveChallenge = true;

            $message = $activeRun->owner_id === auth()->id()
                ? __('Finish your current challenge before starting a new one.')
                : __('You already participate in an active challenge. Finish it before starting another.');

            Notification::make()
                ->title(__('Challenge already in progress'))
                ->body($message)
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        $this->form->validate();
        $data = $this->form->getState();

        $run = ChallengeRun::create([
            'owner_id' => auth()->id(),
            'title' => $data['title'],
            'start_date' => $data['start_date'],
            'target_days' => (int) $data['target_days'],
            'status' => 'active',
            'is_public' => (bool) ($data['is_public'] ?? false),
        ]);

        $this->form->fill([
            'title' => '',
            'start_date' => now()->toDateString(),
            'target_days' => 100,
            'is_public' => false,
        ]);

        $this->hasActiveChallenge = true;

        Notification::make()
            ->title(__('Challenge launched!'))
            ->body(__('Good luck for the next :days days.', ['days' => $run->target_days]))
            ->success()
            ->persistent()
            ->send();

        return redirect()->route('challenges.show', ['run' => $run->id]);
    }

    public function render(): View
    {
        $activeRun = $this->fetchActiveRun();
        $this->hasActiveChallenge = (bool) $activeRun;

        $user = auth()->user();
        $owned = $user->challengeRunsOwned()->with('owner:id,name')->latest()->get();
        $joined = $user->challengeRuns()
            ->with('owner:id,name')
            ->whereNotIn('challenge_runs.id', $owned->pluck('id'))
            ->latest()
            ->get();

        return view('livewire.page.challenge-index', [
            'owned' => $owned,
            'joined' => $joined,
            'activeRun' => $activeRun,
            'hasActiveChallenge' => $this->hasActiveChallenge,
        ]);
    }

    protected function ownerHasActiveChallenge(): bool
    {
        return ChallengeRun::query()
            ->where('owner_id', auth()->id())
            ->where('status', 'active')
            ->exists();
    }

    protected function refreshEligibility(): void
    {
        $this->hasActiveChallenge = $this->userHasActiveChallenge();
    }

    protected function userHasActiveChallenge(): bool
    {
        $user = auth()->user();

        return ChallengeRun::query()
            ->where('status', 'active')
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn ($participantQuery) => $participantQuery->where('user_id', $user->id));
            })
            ->exists();
    }

    protected function fetchActiveRun(): ?ChallengeRun
    {
        $user = auth()->user();

        return ChallengeRun::query()
            ->with('owner:id,name')
            ->where('status', 'active')
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn ($participantQuery) => $participantQuery->where('user_id', $user->id));
            })
            ->latest('start_date')
            ->first();
    }
}
