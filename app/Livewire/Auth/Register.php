<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\Telegram\TelegramClient;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Créer un compte')]
#[Layout('components.layouts.auth', [
    'heroTitle' => 'Bienvenue à bord',
    'heroSubtitle' => 'Créez votre compte pour organiser vos projets, prioriser vos tâches et suivre vos avancées.',
])]
class Register extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $registerForm = [];

    public ?string $telegramToken = null;

    public ?array $telegramPayload = null;

    public function mount(): void
    {
        $this->telegramToken = request()->query('telegram_token');

        if ($this->telegramToken) {
            $payload = Cache::get($this->signupCacheKey($this->telegramToken));

            if ($payload) {
                $this->telegramPayload = $payload;

                $prefill = [];
                if ($name = Arr::get($payload, 'first_name')) {
                    $prefill['name'] = $name;
                }

                if ($username = Arr::get($payload, 'username')) {
                    $prefill['username'] = Str::of($username)->replace('@', '')->slug()->value();
                }

                if (! empty($prefill)) {
                    $this->registerForm = array_merge($prefill, $this->registerForm);
                }
            } else {
                session()->flash('error', __('settings.telegram.link_expired'));
                $this->telegramToken = null;
            }
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('registerForm')
            ->components([
                TextInput::make('name')
                    ->label('Nom complet')
                    ->required()
                    ->minLength(3)
                    ->maxLength(255)
                    ->autocomplete('name'),
                TextInput::make('username')
                    ->label('Pseudo (optionnel)')
                    ->placeholder('Ex. coder123')
                    ->maxLength(32)
                    ->helperText('Affiché dans le journal et les classements.')
                    ->alphaDash()
                    ->autocomplete('username')
                    ->rule('nullable')
                    ->rule('unique:user_profiles,username')
                    ->dehydrateStateUsing(fn ($state) => $state ? Str::of($state)->lower()->slug()->value() : null),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->autocomplete('email')
                    ->rule('unique:users,email'),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->autocomplete('new-password'),
                TextInput::make('password_confirmation')
                    ->label('Confirmation du mot de passe')
                    ->password()
                    ->required()
                    ->same('password')
                    ->autocomplete('new-password'),
            ]);
    }

    public function submit()
    {
        $this->form->validate();
        $data = $this->form->getState();

        $preferences = (new User)->profilePreferencesDefaults();

        $username = null;
        if (! empty($data['username'])) {
            $username = Str::of($data['username'])->lower()->slug()->value();
        }

        $user = User::create([
            'name' => trim($data['name'] ?? ''),
            'email' => strtolower(trim($data['email'] ?? '')),
            'password' => $data['password'] ?? '',
        ]);

        if ($this->telegramPayload) {
            data_set($preferences, 'channels.telegram', true);
        }

        $user->profile()->create([
            'join_reason' => 'self_onboarding',
            'focus_area' => null,
            'username' => $username,
            'preferences' => $preferences,
        ]);

        if ($this->telegramPayload) {
            $this->linkTelegramChannel($user);
            Cache::forget($this->signupCacheKey($this->telegramToken));
        }

        auth()->login($user);

        return redirect()->route('onboarding.wizard');
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }

    protected function linkTelegramChannel(User $user): void
    {
        $chatId = Arr::get($this->telegramPayload, 'chat_id');
        if (! $chatId) {
            return;
        }

        $language = Arr::get($this->telegramPayload, 'language', 'en');
        $username = Arr::get($this->telegramPayload, 'username');

        $user->notificationChannels()->updateOrCreate(
            [
                'channel' => 'telegram',
                'value' => $chatId,
            ],
            [
                'language' => $language,
                'is_active' => true,
                'metadata' => array_filter([
                    'username' => $username ? Str::start($username, '@') : null,
                ]),
            ]
        );

        try {
            app(TelegramClient::class)->sendMessage($chatId, [
                'text' => __('telegram.signup.welcome'),
            ]);
        } catch (\Throwable) {
            // ignore messaging failures
        }
    }

    protected function signupCacheKey(string $token): string
    {
        return "telegram:signup-token:{$token}";
    }
}
