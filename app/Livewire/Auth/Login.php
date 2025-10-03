<?php

namespace App\Livewire\Auth;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Connexion')]
#[Layout('components.layouts.auth', [
    'heroTitle' => 'Reprenez votre productivité',
    'heroSubtitle' => 'Connectez-vous pour gérer vos tâches, suivre vos progrès et rester concentré sur vos objectifs.',
])]
class Login extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $loginForm = [];

    public function mount(): void
    {
        $this->form->fill([
            'email' => '',
            'password' => '',
            'remember' => false,
        ]);

        if ($status = session('status')) {
            Notification::make()
                ->title($status)
                ->success()
                ->persistent()
                ->send();
        }
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
                    ->autocomplete('email')
                    ->helperText('Utilisez l\'adresse associée à votre compte.'),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->required()
                    ->revealable()
                    ->maxLength(255)
                    ->autocomplete('current-password')
                    ->helperText('Votre mot de passe doit contenir au moins 6 caractères.'),
                Checkbox::make('remember')
                    ->label('Se souvenir de moi')
                    ->default(false)
                    ->helperText('Restez connecté sur cet appareil.'),
            ])
            ->statePath('loginForm');
    }

    public function submit()
    {
        $this->form->validate();
        $data = $this->form->getState();

        $credentials = [
            'email' => strtolower(trim($data['email'] ?? '')),
            'password' => $data['password'] ?? '',
        ];

        if (Auth::attempt($credentials, (bool) ($data['remember'] ?? false))) {
            session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        $this->addError('loginForm.email', 'Email ou mot de passe incorrect.');
        $this->form->fill(array_merge($data, ['password' => '']));

        Notification::make()
            ->title('Connexion échouée')
            ->body('Email ou mot de passe incorrect.')
            ->danger()
            ->persistent()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
