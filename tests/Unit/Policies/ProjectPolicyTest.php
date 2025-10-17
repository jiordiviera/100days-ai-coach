<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows the owner to manage a project', function () {
    $owner = User::factory()->create();

    $project = Project::create([
        'name' => 'Owner Project',
        'user_id' => $owner->id,
    ]);

    expect($owner->can('view', $project))->toBeTrue()
        ->and($owner->can('update', $project))->toBeTrue()
        ->and($owner->can('delete', $project))->toBeTrue();
});

it('allows project members to view and update the project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $project = Project::create([
        'name' => 'Team Project',
        'user_id' => $owner->id,
    ]);

    $project->members()->attach($member->id);

    expect($member->can('view', $project))->toBeTrue()
        ->and($member->can('update', $project))->toBeTrue();
});

it('denies access to unrelated users', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $project = Project::create([
        'name' => 'Private Project',
        'user_id' => $owner->id,
    ]);

    expect($intruder->can('view', $project))->toBeFalse()
        ->and($intruder->can('update', $project))->toBeFalse();
});
