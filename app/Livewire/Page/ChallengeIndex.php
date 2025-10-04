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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Mes Challenges')]
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
                    ->label('Titre')
                    ->placeholder('100 Days of Code')
                    ->default('Mon défi 100DaysOfCode')
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Description (optionnel)')
                    ->rows(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->label('Date de début')
                    ->native(false)
                    ->required()
                    ->default(now()->toDateString()),
                TextInput::make('target_days')
                    ->label('Nombre de jours')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(365)
                    ->required(),
                Toggle::make('is_public')
                    ->label('Rendre public')
                    ->inline(false)
                    ->columnSpanFull(),
            ]);
    }

    public function create()
    {
        if ($this->ownerHasActiveChallenge()) {
            $this->hasActiveChallenge = true;

            Notification::make()
                ->title('Challenge déjà en cours')
                ->body('Terminez votre challenge actuel avant d\'en démarrer un nouveau.')
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
            ->title('Challenge lancé !')
            ->body('Bon courage pour ces '.$run->target_days.' prochains jours.')
            ->success()
            ->persistent()
            ->send();

        return redirect()->route('challenges.show', ['run' => $run->id]);
    }

    public function render(): View
    {
        $this->refreshEligibility();

        $user = auth()->user();
        $owned = $user->challengeRunsOwned()->latest()->get();
        $joined = $user->challengeRuns()->whereNotIn('challenge_runs.id', $owned->pluck('id'))->latest()->get();

        return view('livewire.page.challenge-index', [
            'owned' => $owned,
            'joined' => $joined,
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
        $this->hasActiveChallenge = $this->ownerHasActiveChallenge();
    }
}
