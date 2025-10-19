<?php

namespace App\Livewire\Page;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Support extends Component
{
    public function render(): View
    {
        $sections = collect(config('support.faq_sections', []))
            ->map(fn (array $section): array => [
                'title' => __($section['title'] ?? ''),
                'items' => collect($section['items'] ?? [])
                    ->map(fn (array $item): array => [
                        'question' => __($item['question'] ?? ''),
                        'answer' => __($item['answer'] ?? ''),
                    ])
                    ->all(),
            ])
            ->values();

        $resources = collect(config('support.resources', []))
            ->map(fn (array $resource): array => [
                'title' => __($resource['title'] ?? ''),
                'description' => __($resource['description'] ?? ''),
                'url' => $resource['url'] ?? '',
            ])
            ->values();

        return view('livewire.page.support', [
            'sections' => $sections,
            'resources' => $resources,
        ]);
    }
}
