<?php

namespace App\Livewire\Page;

use Livewire\Component;

class Welcome extends Component
{
    public function render()
    {
        seo()
            ->title('100DaysOfCode AI Coach')
            ->description('Rejoins le challenge #100DaysOfCode, consigne tes progrès quotidiens et débloque des badges alimentés par l’IA.')
            ->tag('og:type', 'website');

        return view('livewire.page.welcome');
    }
}
