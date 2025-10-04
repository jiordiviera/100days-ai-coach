<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
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

        $username = null;
        if (! empty($data['username'])) {
            $username = Str::of($data['username'])->lower()->slug()->value();
        }

        $user = User::create([
            'name' => trim($data['name'] ?? ''),
            'email' => strtolower(trim($data['email'] ?? '')),
            'password' => $data['password'] ?? '',
        ]);

        $user->profile()->create([
            'join_reason' => 'self_onboarding',
            'focus_area' => null,
            'username' => $username,
            'preferences' => $user->profilePreferencesDefaults(),
        ]);

        auth()->login($user);

        return redirect()->route('daily-challenge')
            ->with('message', 'Bienvenue ! Complétez votre premier journal pour lancer votre streak.');
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
