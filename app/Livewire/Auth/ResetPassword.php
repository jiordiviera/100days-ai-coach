<?php

namespace App\Livewire\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Réinitialiser le mot de passe')]
#[Layout('components.layouts.auth', [
    'heroTitle' => 'Choisissez un mot de passe sécurisé',
    'heroSubtitle' => 'Définissez un nouveau mot de passe pour revenir à vos projets.',
])]
class ResetPassword extends Component implements HasForms
{
    use InteractsWithForms;

    public string $token;

    public ?array $resetForm = [];

    public function mount(Request $request, string $token): void
    {
        $this->token = $token;
        $this->form->fill([
            'email' => strtolower($request->input('email', '')),
            'password' => '',
            'password_confirmation' => '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('resetForm')
            ->components([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->autocomplete('email'),
                TextInput::make('password')
                    ->label('Nouveau mot de passe')
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->autocomplete('new-password'),
                TextInput::make('password_confirmation')
                    ->label('Confirmez le mot de passe')
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

        $status = Password::reset(
            [
                'email' => strtolower(trim($data['email'] ?? '')),
                'password' => $data['password'] ?? '',
                'password_confirmation' => $data['password_confirmation'] ?? '',
                'token' => $this->token,
            ],
            function ($user) use ($data) {
                $user->forceFill([
                    'password' => $data['password'] ?? '',
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', __($status));

            return redirect()->route('login');
        }

        $message = __($status);
        $this->addError('resetForm.email', $message);

        Notification::make()
            ->title(__('Réinitialisation incomplète'))
            ->body($message)
            ->danger()
            ->persistent()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password');
    }
}
