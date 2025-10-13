<?php

use App\Livewire\Page\Support;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the support knowledge base with faq and resources', function (): void {
    Livewire::test(Support::class)
        ->assertStatus(200)
        ->assertSee('Centre d’aide & feedback', false)
        ->assertSee('Questions fréquentes', false)
        ->assertSee('Roadmap publique', false)
        ->assertSee('Envoyer un feedback', false);
});
