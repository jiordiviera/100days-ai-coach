<?php

use App\Livewire\Partials\Header;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Header::class)
        ->assertStatus(200);
});
