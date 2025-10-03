<?php

namespace App\Livewire\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Mot de passe oublié')]
#[Layout('components.layouts.auth', [
    'heroTitle' => 'Réinitialisez votre accès',
    'heroSubtitle' => 'Recevez un lien de réinitialisation pour reprendre votre progression.',
])]
class ForgotPassword extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $forgotForm = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('forgotForm')
            ->components([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->autocomplete('email')
                    ->helperText('Nous vous enverrons un lien de réinitialisation.'),
            ]);
    }

    public function submit(): void
    {
        $this->form->validate();

        $email = strtolower(trim($this->form->getState()['email'] ?? ''));
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            Notification::make()
                ->title(__('Lien de réinitialisation envoyé'))
                ->body(__($status))
                ->success()
                ->persistent()
                ->send();

            return;
        }

        $message = __($status);
        $this->addError('forgotForm.email', $message);

        Notification::make()
            ->title(__('Impossible d\'envoyer le lien'))
            ->body($message)
            ->danger()
            ->persistent()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }
}
