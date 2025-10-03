<?php

use App\Livewire\Page\ProjectManager;
use App\Models\ChallengeRun;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('prevents project creation without active challenge', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(ProjectManager::class)
        ->set('projectName', 'Projet orphelin')
        ->call('createProject')
        ->assertSet('feedbackType', 'error')
        ->assertSet('feedbackMessage', "Vous devez d'abord rejoindre ou créer un challenge actif avant d'ajouter un projet.");

    expect(Project::count())->toBe(0);
});

it('creates project tied to the active challenge run', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $run = ChallengeRun::create([
        'owner_id' => $user->id,
        'title' => 'Sprint Automne',
        'start_date' => Carbon::now()->subDays(3),
        'target_days' => 100,
        'status' => 'active',
    ]);

    Livewire::test(ProjectManager::class)
        ->set('projectName', 'Suivi API')
        ->call('createProject')
        ->assertSet('feedbackType', 'success')
        ->assertSet('feedbackMessage', 'Projet créé avec succès.');

    $project = Project::first();

    expect($project)->not()->toBeNull()
        ->and($project->challenge_run_id)->toBe($run->id)
        ->and($project->user_id)->toBe($user->id);
});

it('prevents task creation without active challenge', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'Projet isolé',
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);

    Livewire::test(ProjectManager::class)
        ->set('taskName', 'Tâche bloquée')
        ->set('taskProjectId', $project->id)
        ->call('createTask')
        ->assertSet('feedbackType', 'error')
        ->assertSet('feedbackMessage', "Vous devez d'abord rejoindre ou créer un challenge actif avant d'ajouter une tâche.");

    expect(Task::count())->toBe(0);
});

it('requires task project to belong to the active challenge', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $activeRun = ChallengeRun::create([
        'owner_id' => $user->id,
        'title' => 'Run principal',
        'start_date' => Carbon::now()->subDay(),
        'target_days' => 100,
        'status' => 'active',
    ]);

    $foreignRun = ChallengeRun::create([
        'owner_id' => $user->id,
        'title' => 'Run secondaire',
        'start_date' => Carbon::now()->subDays(2),
        'target_days' => 30,
        'status' => 'active',
    ]);

    $project = Project::create([
        'name' => 'Projet concurrent',
        'user_id' => $user->id,
        'challenge_run_id' => $foreignRun->id,
    ]);

    Livewire::test(ProjectManager::class)
        ->set('taskName', 'Tâche invalide')
        ->set('taskProjectId', $project->id)
        ->call('createTask')
        ->assertHasErrors(['taskProjectId']);

    expect(Task::count())->toBe(0);
});

it('creates task for project within active challenge', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $run = ChallengeRun::create([
        'owner_id' => $user->id,
        'title' => 'Run valide',
        'start_date' => Carbon::now()->subDay(),
        'target_days' => 100,
        'status' => 'active',
    ]);

    $project = Project::create([
        'name' => 'Projet aligné',
        'user_id' => $user->id,
        'challenge_run_id' => $run->id,
    ]);

    Livewire::test(ProjectManager::class)
        ->set('taskName', 'Tâche ok')
        ->set('taskProjectId', $project->id)
        ->call('createTask');

    $task = Task::first();

    expect($task)->not()->toBeNull()
        ->and($task->project_id)->toBe($project->id)
        ->and($task->user_id)->toBe($user->id);
});
