<?php

namespace App\Livewire\Onboarding;

use App\Models\UserProfile;
use Filament\Notifications\Notification;
use Livewire\Component;
class DailyChallengeTour extends Component
{
    public array $steps = [];

    public int $currentStep = 0;

    public bool $visible = false;

    protected $listeners = [
        'daily-challenge-tour-open' => 'show',
        'daily-challenge-tour-close' => 'finish',
    ];

    protected $updatesQueryString = [];

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $profile = $user->profile;

        if (! $profile) {
            return;
        }

        $tourCompleted = (bool) data_get($profile->preferences, 'onboarding.tour_completed', false);

        $this->steps = [
            [
                'title' => 'Consigne ton premier log',
                'description' => 'Renseigne ce que tu as shippé aujourd’hui et déclenche l’IA pour obtenir un résumé.',
                'anchor' => 'daily-log-form',
                'action_label' => 'Aller au formulaire',
            ],
            [
                'title' => 'Associe un projet ou des tâches',
                'description' => 'Relie ton shipment à un projet pour mieux suivre tes objectifs sur la durée.',
                'anchor' => 'project-section',
                'action_label' => 'Afficher mes projets',
            ],
            [
                'title' => 'Active le rappel quotidien',
                'description' => 'Définis l’heure idéale pour recevoir un rappel et ne jamais casser ta streak.',
                'anchor' => 'reminder-settings',
                'action_label' => 'Ouvrir les paramètres',
                'external' => route('settings'),
            ],
            [
                'title' => 'Partage ton log public',
                'description' => 'Génère un lien public ou un post LinkedIn/X pour célébrer ton avancée.',
                'anchor' => 'share-section',
                'action_label' => 'Prévisualiser le partage',
            ],
        ];

        if (! $tourCompleted) {
            $this->visible = true;
        }
    }

    public function show(): void
    {
        $this->currentStep = 0;
        $this->visible = true;
    }

    public function render()
    {
        return view('livewire.onboarding.daily-challenge-tour');
    }

    public function next(): void
    {
        if ($this->currentStep < count($this->steps) - 1) {
            $this->currentStep++;
        }
    }

    public function previous(): void
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
        }
    }

    public function finish(): void
    {
        $this->complete(true);
    }

    public function skip(): void
    {
        $this->complete(false);
    }

    public function performAction(): void
    {
        if (! isset($this->steps[$this->currentStep])) {
            return;
        }

        $step = $this->steps[$this->currentStep];

        if (! empty($step['external'])) {
            $this->dispatch('tour-open-external', url: $step['external']);

            return;
        }

        $anchor = $step['anchor'] ?? null;

        if ($anchor) {
            $this->dispatch('tour-scroll-to', target: $anchor);
        }
    }

    protected function complete(bool $celebrate = false): void
    {
        $user = auth()->user();

        if (! $user || ! $user->profile) {
            $this->visible = false;

            return;
        }

        $profile = $user->profile;
        $preferences = $profile->preferences ?? [];
        data_set($preferences, 'onboarding.tour_completed', true);

        $profile->forceFill(['preferences' => $preferences])->save();

        $this->visible = false;

        if ($celebrate) {
            $this->dispatch('tour-confetti');

            Notification::make()
                ->title('Bien joué !')
                ->body('Tu maîtrises les actions clés. À toi de jouer 💪')
                ->success()
                ->send();
        }
    }
}
