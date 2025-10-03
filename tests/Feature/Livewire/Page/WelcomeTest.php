<?php

use App\Livewire\Page\Welcome;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Welcome::class)
        ->assertStatus(200);
});
