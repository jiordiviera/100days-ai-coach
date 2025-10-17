<?php

namespace App\Livewire\Onboarding;

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
                'title' => __('Log your first entry'),
                'description' => __('Tell the coach what you shipped today and trigger the AI recap.'),
                'anchor' => 'daily-log-form',
                'action_label' => __('Go to the form'),
            ],
            [
                'title' => __('Link a project or tasks'),
                'description' => __('Attach your shipment to a project so you can track long-term goals.'),
                'anchor' => 'project-section',
                'action_label' => __('Show my projects'),
            ],
            [
                'title' => __('Schedule the daily reminder'),
                'description' => __('Pick the perfect time for your reminder and keep the streak alive.'),
                'anchor' => 'reminder-settings',
                'action_label' => __('Open settings'),
                'external' => route('settings'),
            ],
            [
                'title' => __('Share your public log'),
                'description' => __('Generate a public link or a LinkedIn/X draft to celebrate progress.'),
                'anchor' => 'share-section',
                'action_label' => __('Preview sharing options'),
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
                ->title(__('Nice work!'))
                ->body(__('You\'re ready to log and ship. Keep the momentum going! 💪'))
                ->success()
                ->send();
        }
    }
}
