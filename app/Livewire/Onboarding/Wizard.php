<?php

namespace App\Livewire\Onboarding;

use App\Models\ChallengeRun;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Wizard extends Component implements HasForms
{
    use InteractsWithForms;

    public int $step = 1;

    public array $data = [];

    public ?string $createdRunId = null;

    public function mount(): void
    {
        $user = auth()->user();

        if (!$user || !$user->needsOnboarding()) {
            $this->redirectRoute('daily-challenge');

            return;
        }

        $profile = $user->profile;
        $preferences = $profile->preferences ?? $user->profilePreferencesDefaults();

        $this->data = [
            'username' => $profile->username,
            'focus_area' => $profile->focus_area,
            'timezone' => $preferences['timezone'] ?? config('app.timezone', 'UTC'),
            'challenge_title' => $user->name ? 'Défi de ' . $user->name : 'Mon défi 100DaysOfCode',
            'challenge_description' => $profile->focus_area,
            'challenge_start_date' => Carbon::today()->toDateString(),
            'challenge_target_days' => 100,
            'reminder_time' => $preferences['reminder_time'] ?? '20:30',
            'channels' => collect($preferences['channels'] ?? ['email' => true])
                ->filter()
                ->keys()
                ->all(),
        ];

        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema(match ($this->step) {
                1 => $this->profileStepSchema(),
                2 => $this->challengeStepSchema(),
                default => $this->reminderStepSchema(),
            });
    }

    public function submit(): void
    {
        $this->form->validate();

        match ($this->step) {
            1 => $this->saveProfileStep(),
            2 => $this->saveChallengeStep(),
            3 => $this->completeOnboarding(),
            default => null,
        };
    }

    public function previous(): void
    {
        if ($this->step > 1) {
            $this->step--;
            $this->form->fill($this->data);
        }
    }

    protected function saveProfileStep(): void
    {
        $user = auth()->user();
        $profile = $user->profile;
        $preferences = $profile->preferences ?? $user->profilePreferencesDefaults();

        $username = $this->data['username'] ?? null;
        if ($username) {
            $username = Str::of($username)->lower()->slug()->value();
        }

        $profile->forceFill([
            'username' => $username,
            'focus_area' => $this->data['focus_area'] ? Str::limit($this->data['focus_area'], 120) : null,
        ])->save();

        $preferences['timezone'] = $this->data['timezone'];

        $profile->forceFill([
            'preferences' => $preferences,
        ])->save();

        $this->data['username'] = $username;
        $this->step = 2;
        $this->form->fill($this->data);
    }

    protected function saveChallengeStep(): void
    {
        $user = auth()->user();

        $payload = [
            'title' => $this->data['challenge_title'],
            'description' => $this->data['challenge_description'] ?: null,
            'start_date' => Carbon::parse($this->data['challenge_start_date'])->toDateString(),
            'target_days' => (int)$this->data['challenge_target_days'],
            'status' => 'active',
            'owner_id' => $user->id,
            'is_public' => false,
        ];

        if ($this->createdRunId) {
            $run = ChallengeRun::find($this->createdRunId);

            if ($run) {
                $run->forceFill($payload)->save();
            }
        } else {
            $run = ChallengeRun::create($payload);
            $this->createdRunId = $run->id ?? null;
        }

        $this->step = 3;
        $this->form->fill($this->data);
    }

    protected function completeOnboarding(): void
    {
        $user = auth()->user();
        $profile = $user->profile;
        $preferences = $profile->preferences ?? $user->profilePreferencesDefaults();

        if (!$this->createdRunId) {
            $this->saveChallengeStep();
        }

        $channels = ['email' => false, 'slack' => false, 'push' => false];
        foreach ($this->data['channels'] ?? [] as $channel) {
            $channels[$channel] = true;
        }

        $preferences['channels'] = $channels;
        $preferences['reminder_time'] = $this->data['reminder_time'];

        $profile->forceFill([
            'preferences' => $preferences,
        ])->save();

        $user->forceFill(['needs_onboarding' => false])->save();

        Notification::make()
            ->title('Onboarding complété')
            ->body('Tout est prêt ! Consigne ton premier log pour lancer la streak.')
            ->success()
            ->send();

        $this->redirectRoute('daily-challenge');
    }

    protected function profileStepSchema(): array
    {
        return [
            TextInput::make('username')
                ->label('Pseudo public')
                ->helperText('Optionnel. Utilisé sur les pages publiques et classements.')
                ->maxLength(32)
                ->alphaDash(),
            Textarea::make('focus_area')
                ->label('Focus principal')
                ->rows(2)
                ->maxLength(160)
                ->placeholder('Ex. Shipping d’un produit IA, apprendre Laravel…'),
            Select::make('timezone')
                ->label('Fuseau horaire')
                ->required()
                ->options($this->timezoneOptions())
                ->searchable(),
        ];
    }

    protected function challengeStepSchema(): array
    {
        return [
            TextInput::make('challenge_title')
                ->label('Titre du challenge')
                ->required()
                ->maxLength(255),
            Textarea::make('challenge_description')
                ->label('Description (optionnel)')
                ->rows(2)
                ->maxLength(255),
            DatePicker::make('challenge_start_date')
                ->label('Date de début')
                ->native(false)
                ->required(),
            TextInput::make('challenge_target_days')
                ->label('Nombre de jours')
                ->numeric()
                ->minValue(1)
                ->maxValue(365)
                ->required(),
        ];
    }

    protected function reminderStepSchema(): array
    {
        return [
            TimePicker::make('reminder_time')
                ->label('Heure du rappel quotidien')
                ->native(false)
                ->required(),
            CheckboxList::make('channels')
                ->label('Canaux de notification')
                ->options([
                    'email' => 'Email',
                    'slack' => 'Slack',
                    'push' => 'Push (bientôt)',
                ])
                ->default(['email'])
                ->columns(3),
        ];
    }

    protected function timezoneOptions(): array
    {
        $preferred = [
            'UTC' => 'UTC',
            'Africa/Douala' => 'Africa/Douala',
            'Europe/Paris' => 'Europe/Paris',
            'America/New_York' => 'America/New_York',
            'America/Los_Angeles' => 'America/Los_Angeles',
            'Asia/Tokyo' => 'Asia/Tokyo',
            'Asia/Singapore' => 'Asia/Singapore',
            'Australia/Sydney' => 'Australia/Sydney',
        ];

        $common = collect(\DateTimeZone::listIdentifiers())
            ->filter(fn($tz) => Str::contains($tz, ['/']))
            ->mapWithKeys(fn($tz) => [$tz => $tz])
            ->all();

        return $preferred + $common;
    }

    public function render(): View
    {
        return view('livewire.onboarding.wizard');
    }
}
