<?php

namespace App\Livewire\Page;

use App\Models\ChallengeRun;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProjectManager extends Component
{
    public $projectName = '';

    public $taskName = '';

    public $taskProjectId = '';

    public ?string $taskAssigneeId = null;

    public $editProjectId = null;

    public $editProjectName = '';

    public $editTaskId = null;

    public $editTaskName = '';

    public ?string $editTaskAssigneeId = null;

    public array $commentDrafts = [];

    public array $assignmentBuffer = [];

    public ?string $activeRunId = null;

    public ?string $feedbackMessage = null;

    public string $feedbackType = 'success';

    public ?string $projectTemplate = null;

    protected Collection $templates;

    public array $templateSelection = [];

    protected function setFeedback(string $type, string $message): void
    {
        $this->feedbackType = $type;
        $this->feedbackMessage = $message;
    }

    public function createProject(): void
    {
        $this->resolveActiveRun();

        if (! $this->activeRunId) {
            $this->setFeedback('error', "Vous devez d'abord rejoindre ou créer un challenge actif avant d'ajouter un projet.");

            return;
        }

        $this->validate([
            'projectName' => 'required|string|max:255',
            'projectTemplate' => 'nullable|string',
        ]);
        $project = Project::create([
            'name' => $this->projectName,
            'user_id' => auth()->id(),
            'challenge_run_id' => $this->activeRunId,
        ]);
        $this->projectName = '';
        $this->applyTemplate($project, $this->projectTemplate);
        $this->projectTemplate = null;

        $this->resetErrorBag();
        $this->setFeedback('success', 'Projet créé avec succès.');
    }

    public function createTask(): void
    {
        $this->resolveActiveRun();

        if (! $this->activeRunId) {
            $this->setFeedback('error', "Vous devez d'abord rejoindre ou créer un challenge actif avant d'ajouter une tâche.");

            return;
        }

        $this->validate([
            'taskName' => 'required|string|max:255',
            'taskProjectId' => 'required|exists:projects,id',
        ]);
        $project = Project::query()
            ->whereKey($this->taskProjectId)
            ->where('challenge_run_id', $this->activeRunId)
            ->first();

        if (! $project) {
            $this->addError('taskProjectId', 'Sélectionnez un projet lié à votre challenge actif.');

            return;
        }

        if ($this->taskAssigneeId && ! $this->isAssignableUser($project, $this->taskAssigneeId)) {
            $this->addError('taskAssigneeId', 'Choisissez un membre du challenge pour l’assignation.');

            return;
        }

        $task = Task::create([
            'title' => $this->taskName,
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'assigned_user_id' => $this->taskAssigneeId,
        ]);
        $this->taskName = '';
        $this->taskProjectId = '';
        $this->taskAssigneeId = null;

        $this->assignmentBuffer[$task->id] = $task->assigned_user_id;
        $this->commentDrafts[$task->id] = $this->commentDrafts[$task->id] ?? '';

        $this->resetErrorBag();
        $this->setFeedback('success', 'Tâche créée avec succès.');
    }

    public function editProject($id): void
    {
        $project = Project::findOrFail($id);
        $this->editProjectId = $project->id;
        $this->editProjectName = $project->name;
    }

    public function updateProject(): void
    {
        $this->validate([
            'editProjectName' => 'required|string|max:255',
        ]);
        $project = Project::findOrFail($this->editProjectId);
        $project->update(['name' => $this->editProjectName]);
        $this->editProjectId = null;
        $this->editProjectName = '';
    }

    public function deleteProject($id): void
    {
        Project::findOrFail($id)->delete();
    }

    public function editTask($id): void
    {
        $task = Task::findOrFail($id);
        $this->editTaskId = $task->id;
        $this->editTaskName = $task->title;
        $this->taskProjectId = $task->project_id;
        $this->editTaskAssigneeId = $task->assigned_user_id;
    }

    public function updateTask(): void
    {
        $task = Task::findOrFail($this->editTaskId);

        $this->validate([
            'editTaskName' => 'required|string|max:255',
        ]);

        if ($this->editTaskAssigneeId && ! $this->isAssignableUser($task->project, $this->editTaskAssigneeId)) {
            $this->addError('editTaskAssigneeId', 'Choisissez un membre du challenge pour l’assignation.');

            return;
        }

        $task->update([
            'title' => $this->editTaskName,
            'assigned_user_id' => $this->editTaskAssigneeId,
        ]);
        $this->editTaskId = null;
        $this->editTaskName = '';
        $this->taskProjectId = '';
        $this->editTaskAssigneeId = null;
    }

    public function deleteTask($id): void
    {
        Task::findOrFail($id)->delete();

        unset($this->commentDrafts[$id], $this->assignmentBuffer[$id]);
    }

    public function updateTaskAssignment(string $taskId): void
    {
        $task = Task::with('project')->findOrFail($taskId);

        $userId = $this->assignmentBuffer[$taskId] ?? null;

        if (! $userId) {
            $task->update(['assigned_user_id' => null]);

            return;
        }

        if (! $this->isAssignableUser($task->project, $userId)) {
            $this->addError('assignmentBuffer.'.$taskId, 'Membre invalide.');

            return;
        }

        $task->update(['assigned_user_id' => $userId]);
    }

    public function addComment(string $taskId): void
    {
//        dd($taskId);
        $body = trim($this->commentDrafts[$taskId] ?? '');

        if ($body === '') {
            $this->addError('commentDrafts.'.$taskId, 'Le commentaire ne peut pas être vide.');

            return;
        }

        $task = Task::with('project')->findOrFail($taskId);

        if (! $this->isAssignableUser($task->project, auth()->id())) {
            $this->addError('commentDrafts.'.$taskId, 'Vous ne pouvez commenter que les tâches de votre challenge.');

            return;
        }

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body' => $body,
        ]);

        $this->commentDrafts[$taskId] = '';
        $this->resetErrorBag('commentDrafts.'.$taskId);
    }

    protected function isAssignableUser(Project $project, ?string $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->getAssignableUsersForProject($project)->contains(fn (User $user) => $user->id === $userId);
    }

    protected function getAssignableUsersForProject(Project $project)
    {
        $users = collect();

        $users = $users->merge([$project->user]);
        $users = $users->merge($project->members);

        if ($project->challengeRun) {
            $users = $users->merge([$project->challengeRun->owner]);
            $users = $users->merge($project->challengeRun->participants);
        }

        return $users
            ->filter()
            ->unique('id')
            ->values();
    }

    protected function resolveActiveRun(): void
    {
        $user = auth()->user();
        $run = ChallengeRun::query()
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn ($qq) => $qq->where('user_id', $user->id));
            })
            ->latest('start_date')
            ->first();
        $this->activeRunId = $run?->id;
    }

    public function render()
    {
        $user = auth()->user();
        $this->resolveActiveRun();

        $projects = Project::with([
            'tasks.assignee',
            'tasks.user',
            'tasks.comments.user',
            'user',
            'members',
            'challengeRun.owner',
            'challengeRun.participants',
        ])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('members', function ($qq) use ($user) {
                        $qq->where('users.id', $user->id);
                    });
            })
            ->when($this->activeRunId, fn ($q) => $q->where('challenge_run_id', $this->activeRunId))
            ->latest()
            ->get();

        $activeRun = $this->activeRunId ? ChallengeRun::find($this->activeRunId) : null;

        $this->templates = $this->getTemplates();

        foreach ($projects as $project) {
            foreach ($project->tasks as $task) {
                $this->assignmentBuffer[$task->id] ??= $task->assigned_user_id;
                $this->commentDrafts[$task->id] ??= '';
            }

            $this->templateSelection[$project->id] ??= '';
        }

        return view('livewire.page.project-manager', [
            'projects' => $projects,
            'activeRun' => $activeRun,
            'assignableByProject' => $projects->mapWithKeys(fn ($project) => [
                $project->id => $this->getAssignableUsersForProject($project),
            ]),
            'templates' => $this->templates,
        ]);
    }

    public function applyTemplateToProject(string $projectId): void
    {
        $templateId = $this->templateSelection[$projectId] ?? null;

        if (! $templateId) {
            $this->addError('templateSelection.'.$projectId, 'Sélectionnez un modèle.');

            return;
        }

        $project = Project::findOrFail($projectId);

        $this->applyTemplate($project, $templateId);
        $this->templateSelection[$projectId] = '';

        $this->setFeedback('success', 'Modèle appliqué au projet « '.$project->name.' ».');
    }

    protected function getTemplates(): Collection
    {
        return collect(config('project-templates.templates', []))->map(fn ($template) => (object) $template);
    }

    protected function applyTemplate(Project $project, ?string $templateId): void
    {
        if (! $templateId) {
            return;
        }

        $template = $this->getTemplates()->firstWhere('id', $templateId);

        if (! $template || empty($template->tasks)) {
            return;
        }

        foreach ($template->tasks as $title) {
            $title = trim((string) $title);

            if ($title === '') {
                continue;
            }

            Task::create([
                'title' => $title,
                'project_id' => $project->id,
                'user_id' => auth()->id(),
                'assigned_user_id' => null,
            ]);
        }
    }
}
