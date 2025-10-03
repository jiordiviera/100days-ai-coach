<?php

use App\Livewire\Page\Dashboard;
use App\Models\ChallengeRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders successfully', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    ChallengeRun::create([
        'owner_id' => $user->id,
        'title' => 'Test Challenge',
        'start_date' => Carbon::now()->subDay(),
        'target_days' => 100,
    ]);

    Livewire::test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('Progression quotidienne');
});
