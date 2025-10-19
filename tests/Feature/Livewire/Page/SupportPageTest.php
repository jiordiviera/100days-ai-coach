<?php

use App\Livewire\Page\Support;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the support knowledge base with faq and resources', function (): void {
    $firstQuestion = __((string) data_get(config('support.faq_sections'), '0.items.0.question'));
    $firstResource = collect(config('support.resources'))->first() ?? [];

    Livewire::test(Support::class)
        ->assertStatus(200)
        ->assertSee(__('Support & feedback center'), false)
        ->assertSee(__('Frequently asked questions'), false)
        ->assertSee($firstQuestion, false)
        ->assertSee(__('Quick resources'), false)
        ->assertSee(__('Send feedback'), false)
        ->assertSee(__($firstResource['title'] ?? ''), false)
        ->assertSee(__($firstResource['description'] ?? ''), false);
});
