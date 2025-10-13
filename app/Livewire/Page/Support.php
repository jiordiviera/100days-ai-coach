<?php

namespace App\Livewire\Page;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Support extends Component
{
    public function render(): View
    {
        $sections = config('support.faq_sections', []);
        $resources = config('support.resources', []);

        return view('livewire.page.support', [
            'sections' => $sections,
            'resources' => $resources,
        ]);
    }
}
