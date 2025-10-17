<?php

namespace App\Livewire\Page;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TaskManager extends Component
{
    public Project $project;

    #[Locked]
    public string $projectId;

    #[Validate('required|string|max:255')]
    public string $taskName = '';

    public ?string $editTaskId = null;

    #[Validate('required|string|max:255')]
    public string $editTaskName = '';

    public ?string $taskAssigneeId = null;

    public ?string $editTaskAssigneeId = null;

    public array $assignmentBuffer = [];

    public array $commentDrafts = [];

    public array $assignableUsers = [];

    public function mount(Project $project): void
    {
        $this->projectId = $project->id;
        $this->ensureProjectIsAccessible($project);
        $this->refreshProject();
    }

    public function createTask(): void
    {
        $this->validateOnly('taskName');

        if ($this->taskAssigneeId && ! $this->isAssignableUser($this->project, $this->taskAssigneeId)) {
            $this->addError('taskAssigneeId', 'Choisissez un membre du challenge.');

            return;
        }

        $task = Task::query()->create([
            'title' => $this->taskName,
            'project_id' => $this->project->id,
            'user_id' => auth()->id(),
            'assigned_user_id' => $this->taskAssigneeId,
        ]);

        $this->reset('taskName');
        $this->taskAssigneeId = null;
        $this->resetErrorBag('taskName');
        $this->assignmentBuffer[$task->id] = $task->assigned_user_id;
        $this->commentDrafts[$task->id] = '';
        $this->refreshProject();
    }

    public function editTask(string $id): void
    {
        $task = $this->findTaskForUser($id);

        $this->editTaskId = $task->id;
        $this->editTaskName = $task->title;
        $this->editTaskAssigneeId = $task->assigned_user_id;
    }

    public function updateTask(): void
    {
        $this->validateOnly('editTaskName');

        $task = $this->findTaskForUser((string) $this->editTaskId);
        if ($this->editTaskAssigneeId && ! $this->isAssignableUser($task->project, $this->editTaskAssigneeId)) {
            $this->addError('editTaskAssigneeId', 'Choisissez un membre du challenge.');

            return;
        }

        $task->update([
            'title' => $this->editTaskName,
            'assigned_user_id' => $this->editTaskAssigneeId,
        ]);

        $this->reset('editTaskId', 'editTaskName');
        $this->editTaskAssigneeId = null;
        $this->resetErrorBag('editTaskName');
        $this->refreshProject();
    }

    public function deleteTask(string $id): void
    {
        $this->findTaskForUser($id)->delete();

        unset($this->assignmentBuffer[$id], $this->commentDrafts[$id]);

        $this->refreshProject();
    }

    public function completeTask(string $id): void
    {
        $task = $this->findTaskForUser($id);
        $task->update(['is_completed' => true]);

        $this->refreshProject();
    }

    public function updateTaskAssignment(string $taskId): void
    {
        $task = $this->findTaskForUser($taskId);
        $selected = $this->assignmentBuffer[$taskId] ?? null;

        if (! $selected) {
            $task->update(['assigned_user_id' => null]);

            return;
        }

        if (! $this->isAssignableUser($task->project, $selected)) {
            $this->addError('assignmentBuffer.'.$taskId, 'Membre invalide.');

            return;
        }

        $task->update(['assigned_user_id' => $selected]);
    }

    public function addComment(string $taskId): void
    {
        $body = trim($this->commentDrafts[$taskId] ?? '');

        if ($body === '') {
            $this->addError('commentDrafts.'.$taskId, 'Le commentaire ne peut pas être vide.');

            return;
        }

        $task = $this->findTaskForUser($taskId);

        if (! $this->isAssignableUser($task->project, auth()->id())) {
            $this->addError('commentDrafts.'.$taskId, 'Vous ne pouvez commenter que les tâches du challenge.');

            return;
        }

        $cleanBody = Str::of($body)
            ->replace(["\r\n", "\r"], "\n")
            ->stripTags()
            ->replaceMatches('/[^\S\n]+/', ' ')
            ->trim();

        if ($cleanBody->isEmpty()) {
            $this->addError('commentDrafts.'.$taskId, 'Le commentaire doit contenir du texte lisible.');

            return;
        }

        if ($cleanBody->length() > 1000) {
            $this->addError('commentDrafts.'.$taskId, 'Le commentaire ne doit pas dépasser 1000 caractères.');

            return;
        }

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body' => (string) $cleanBody,
        ]);

        $this->commentDrafts[$taskId] = '';
        $this->resetErrorBag('commentDrafts.'.$taskId);
        $this->refreshProject();
    }

    protected function isAssignableUser(Project $project, ?string $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return collect($this->assignableUsers)
            ->contains(fn (User $user) => $user->id === $userId);
    }

    protected function buildAssignableUsers(): array
    {
        $users = collect([$this->project->user])
            ->merge($this->project->members);

        if ($this->project->challengeRun) {
            $users = $users->merge([$this->project->challengeRun->owner])
                ->merge($this->project->challengeRun->participants);
        }

        return $users->filter()->unique('id')->values()->all();
    }

    protected function refreshProject(): void
    {
        $this->project = Project::with([
            'tasks.user',
            'tasks.assignee',
            'tasks.comments.user',
            'members',
            'challengeRun.owner',
            'challengeRun.participants',
        ])
            ->accessibleTo(Auth::user())
            ->findOrFail($this->projectId);

        $this->assignableUsers = $this->buildAssignableUsers();

        foreach ($this->project->tasks as $task) {
            $this->assignmentBuffer[$task->id] ??= $task->assigned_user_id;
            $this->commentDrafts[$task->id] ??= '';
        }
    }

    protected function ensureProjectIsAccessible(Project $project): void
    {
        $accessible = Project::accessibleTo(Auth::user())
            ->whereKey($project->id)
            ->exists();

        abort_unless($accessible, 403);
    }

    protected function findTaskForUser(string $taskId): Task
    {
        return Task::with(['project'])
            ->accessibleTo(Auth::user())
            ->whereKey($taskId)
            ->firstOrFail();
    }

    public function render(): View
    {
        return view('livewire.page.task-manager', [
            'project' => $this->project,
            'assignableUsers' => $this->assignableUsers,
        ]);
    }
}
