<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('inherits permissions from the parent project', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $project = Project::create([
        'name' => 'Task Policy Project',
        'user_id' => $owner->id,
    ]);

    $task = Task::create([
        'title' => 'Owner task',
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);

    $project->members()->attach($member->id);

    expect($owner->can('view', $task))->toBeTrue()
        ->and($owner->can('update', $task))->toBeTrue();

    expect($member->can('view', $task))->toBeTrue()
        ->and($member->can('update', $task))->toBeTrue();
});

it('denies users unrelated to the project', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $project = Project::create([
        'name' => 'Restricted Project',
        'user_id' => $owner->id,
    ]);

    $task = Task::create([
        'title' => 'Restricted task',
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);

    expect($intruder->can('view', $task))->toBeFalse()
        ->and($intruder->can('update', $task))->toBeFalse();
});
