<?php

namespace App\Livewire\Page;

use Livewire\Component;

class Welcome extends Component
{
    public function render()
    {
        seo()
            ->title('100DaysOfCode AI Coach')
            ->description(__('Join the #100DaysOfCode challenge, log your daily progress, and unlock AI-powered badges.'))
            ->tag('og:type', 'website');

        return view('livewire.page.welcome');
    }
}
