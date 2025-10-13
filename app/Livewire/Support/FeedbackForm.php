<?php

namespace App\Livewire\Support;

use App\Models\SupportTicket;
use App\Events\SupportTicketCreated;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FeedbackForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $formData = null;

    public bool $submitted = false;

    public function mount(): void
    {
        $user = Auth::user();

        $this->formData = [
            'name' => $user?->name,
            'email' => $user?->email,
            'category' => 'question',
            'message' => null,
        ];

        $this->form->fill($this->formData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('formData')
            ->columns([
                'default' => 1,
                'sm' => 2,
            ])
            ->components([
                TextInput::make('name')
                    ->label('Ton nom')
                    ->required()
                    ->maxLength(120),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(190),
                Select::make('category')
                    ->label('Catégorie')
                    ->options([
                        'question' => 'Question',
                        'bug' => 'Bug',
                        'idea' => 'Idée',
                    ])
                    ->required()
                    ->default('question')
                    ->columnSpan([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                Textarea::make('message')
                    ->label('Dis-nous tout')
                    ->rows(5)
                    ->required()
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ]);
    }

    public function submit(): void
    {
        $this->form->validate();
        $data = $this->form->getState();

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'category' => $data['category'] ?? 'question',
            'message' => $data['message'] ?? '',
        ]);

        SupportTicketCreated::dispatch($ticket);

        $this->submitted = true;
        $this->form->fill([
            'name' => $ticket->name,
            'email' => $ticket->email,
            'category' => 'question',
            'message' => null,
        ]);

        Notification::make()
            ->title('Merci pour ton retour !')
            ->body('Nous revenons vers toi rapidement. Tu peux suivre la discussion par email.')
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.support.feedback-form');
    }
}
