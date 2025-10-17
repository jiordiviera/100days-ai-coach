@php
    $heroStats = [
        [
            'label' => __('Projects'),
            'value' => $projects->count(),
            'hint' => __('Across your active challenge'),
        ],
        [
            'label' => __('Tasks'),
            'value' => $projects->sum(fn ($project) => $project->tasks->count()),
            'hint' => __('Including assigned items'),
        ],
        [
            'label' => __('Members'),
            'value' => $projects->flatMap->members->pluck('id')->merge($projects->pluck('user_id'))->unique()->count(),
            'hint' => __('Collaborators across projects'),
        ],
    ];
@endphp

<div class="mx-auto max-w-6xl space-y-12 px-4 py-12 sm:px-6 lg:px-0">
    <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
        <div class="absolute inset-0">
            <div class="absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
            <div class="absolute -right-12 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
        </div>

        <div class="relative grid gap-10 p-8 lg:grid-cols-[1.2fr_0.8fr] lg:p-12">
            <div class="space-y-6">
                <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
                    {{ __('Project management') }}
                </span>

                <div class="space-y-3">
                    @if ($activeRun)
                        <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">
                            {{ $activeRun->title ?? __('100DaysOfCode challenge') }}
                        </h1>
                        <p class="text-sm text-muted-foreground sm:text-base">
                            {{ __('Current challenge:') }}
                            <x-filament::link wire:navigate href="{{ route('challenges.show', $activeRun->id) }}">
                                {{ $activeRun->title ?? '100 Days of Code' }}
                            </x-filament::link>
                            &mdash; {{ __('started on :date', ['date' => $activeRun->start_date->translatedFormat('F j, Y')]) }}
                        </p>
                    @else
                        <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">
                            {{ __('No active challenge at the moment.') }}
                        </h1>
                        <p class="text-sm text-muted-foreground sm:text-base">
                            {{ __('Create or join a challenge to collaborate on your projects.') }}
                        </p>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ($heroStats as $stat)
                        <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ $stat['label'] }}</p>
                            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $stat['value'] }}</p>
                            <p class="text-xs text-muted-foreground">{{ $stat['hint'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-5 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl">
                @if ($feedbackMessage)
                    <div class="rounded-2xl border px-4 py-3 text-sm {{ $feedbackType === 'error' ? 'border-danger-200 bg-danger-50 text-danger-900 dark:border-danger-900/40 dark:bg-danger-900/30 dark:text-danger-100' : 'border-success-200 bg-success-50 text-success-900 dark:border-success-900/40 dark:bg-success-900/30 dark:text-success-100' }}">
                        {{ $feedbackMessage }}
                    </div>
                @endif

                @if (! $activeRun)
                    <div class="space-y-3">
                        <p class="text-sm text-muted-foreground">
                            {{ __('Create or join a challenge first to add projects and tasks.') }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <x-filament::button tag="a" href="{{ route('challenges.index') }}">
                                {{ __('Browse challenges') }}
                            </x-filament::button>
                            <x-filament::button tag="a" href="{{ route('challenges.index') }}#create" color="gray">
                                {{ __('Create my challenge') }}
                            </x-filament::button>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-[0.24em] text-muted-foreground">{{ __('Create a project') }}</h2>
                            <p class="text-xs text-muted-foreground">
                                {{ __('The project will be attached to the challenge “:title”.', ['title' => $activeRun->title ?? '100 Days of Code']) }}
                            </p>
                        </div>

                        <form wire:submit.prevent="createProject" class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="project_name">{{ __('Project name') }}</label>
                                <input
                                    id="project_name"
                                    type="text"
                                    wire:model="projectName"
                                    placeholder="{{ __('My awesome project') }}"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
                                @error('projectName')
                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            @if ($templates->isNotEmpty())
                                <div class="space-y-2">
                                    <label class="text-sm font-medium" for="project_template">{{ __('Template') }}</label>
                                    <select
                                        id="project_template"
                                        wire:model="projectTemplate"
                                        class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                    >
                                        <option value="">-- {{ __('No template') }} --</option>
                                        @foreach ($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('projectTemplate')
                                        <p class="text-xs text-destructive">{{ $message }}</p>
                                    @enderror
                                    @if ($projectTemplate)
                                        @php($selectedTemplate = $templates->firstWhere('id', $projectTemplate))
                                        @if ($selectedTemplate)
                                            <p class="text-xs text-muted-foreground">
                                                {{ $selectedTemplate->description }} ({{ count($selectedTemplate->tasks ?? []) }} {{ __('pre-created tasks') }})
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            @endif

                            <x-filament::button type="submit">
                                {{ __('Create project') }}
                            </x-filament::button>
                        </form>

                        <div class="border-t border-border/70 pt-4">
                            <h2 class="text-sm font-semibold uppercase tracking-[0.24em] text-muted-foreground">{{ __('Create a task') }}</h2>
                            <form wire:submit.prevent="createTask" class="mt-3 space-y-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium" for="task_name">{{ __('Task name') }}</label>
                                    <input
                                        id="task_name"
                                        type="text"
                                        wire:model="taskName"
                                        placeholder="{{ __('New task') }}"
                                        class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                    />
                                    @error('taskName')
                                        <p class="text-xs text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium" for="task_project">{{ __('Related project') }}</label>
                                    <select
                                        id="task_project"
                                        wire:model="taskProjectId"
                                        class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                    >
                                        <option value="">-- {{ __('Select a project') }} --</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('taskProjectId')
                                        <p class="text-xs text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                @if ($taskProjectId)
                                    @php($assignable = ($assignableByProject[$taskProjectId] ?? collect()))
                                    @if ($assignable->isNotEmpty())
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium" for="task_assignee">{{ __('Assign to') }}</label>
                                            <select
                                                id="task_assignee"
                                                wire:model="taskAssigneeId"
                                                class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                            >
                                                <option value="">-- {{ __('Assign later') }} --</option>
                                                @foreach ($assignable as $member)
                                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('taskAssigneeId')
                                                <p class="text-xs text-destructive">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif
                                @endif

                                <x-filament::button type="submit">
                                    {{ __('Create task') }}
                                </x-filament::button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-foreground sm:text-xl">{{ __('My projects') }}</h2>
                <p class="text-sm text-muted-foreground">
                    {{ $projects->isEmpty() ? __('Create your first project to track your 100DaysOfCode goals.') : __('Manage your projects, members, and daily tasks.') }}
                </p>
            </div>
        </div>

        @if ($projects->isEmpty())
            <div class="rounded-3xl border border-dashed border-border/60 bg-card/80 p-8 text-center shadow-sm">
                <p class="text-sm text-muted-foreground">
                    {{ __('Start your first project to document your progress.') }}
                </p>
            </div>
        @else
            <div class="space-y-8">
                @foreach ($projects as $project)
                    <div class="space-y-6 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm" wire:key="project-{{ $project->id }}">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-foreground">{{ $project->name }}</h3>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 font-medium text-primary">
                                        <span class="text-[11px] uppercase tracking-widest">{{ __('Tasks') }}</span>
                                        <span class="text-sm font-semibold">{{ $project->tasks->count() }}</span>
                                    </span>
                                    @if ($project->created_at)
                                        <span>{{ __('Created on :date', ['date' => $project->created_at->translatedFormat('F j, Y')]) }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <x-filament::button
                                    tag="a"
                                    href="{{ route('projects.tasks.index', ['project' => $project->id]) }}"
                                    size="sm"
                                    color="gray"
                                >
                                    {{ __('View tasks') }}
                                </x-filament::button>
                                <x-filament::button type="button" wire:click="editProject('{{ $project->id }}')" size="sm">
                                    {{ __('Edit') }}
                                </x-filament::button>
                                <x-filament::button
                                    type="button"
                                    wire:click="deleteProject('{{ $project->id }}')"
                                    wire:confirm="{{ __('Delete this project?') }}"
                                    color="danger"
                                    size="sm"
                                >
                                    {{ __('Delete') }}
                                </x-filament::button>
                            </div>
                        </div>

                        @if ($templates->isNotEmpty())
                            <form wire:submit.prevent="applyTemplateToProject(@js($project->id))" class="flex flex-wrap items-center gap-2 text-xs">
                                <label class="font-medium" for="project-template-{{ $project->id }}">{{ __('Apply a template:') }}</label>
                                <select
                                    id="project-template-{{ $project->id }}"
                                    wire:model="templateSelection.{{ $project->id }}"
                                    class="rounded-lg border border-border bg-background px-2 py-1 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                >
                                    <option value="">-- {{ __('Select') }} --</option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                                <x-filament::button size="xs" type="submit">
                                    {{ __('Add tasks') }}
                                </x-filament::button>
                                @error('templateSelection.'.$project->id)
                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </form>
                        @endif

                        @if ($editProjectId === $project->id)
                            <form wire:submit.prevent="updateProject" class="flex flex-wrap items-center gap-2 rounded-2xl border border-border/70 bg-background/80 p-4">
                                <input
                                    id="edit_project_{{ $project->id }}"
                                    type="text"
                                    wire:model="editProjectName"
                                    class="w-full min-w-[12rem] flex-1 rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
                                <div class="flex flex-wrap gap-2">
                                    <x-filament::button size="sm" type="submit">
                                        {{ __('Save') }}
                                    </x-filament::button>
                                    <x-filament::button
                                        size="sm"
                                        type="button"
                                        color="gray"
                                        outlined
                                        wire:click="$set('editProjectId', null)"
                                    >
                                        {{ __('Cancel') }}
                                    </x-filament::button>
                                </div>
                                @error('editProjectName')
                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </form>
                        @endif

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">{{ __('Owner') }}</p>
                                <p class="text-sm">{{ $project->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">{{ __('Members') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($project->members as $member)
                                        <span class="rounded-full bg-secondary/10 px-3 py-1 text-xs font-medium text-secondary-foreground">
                                            {{ $member->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-muted-foreground">{{ __('No members yet') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold uppercase text-muted-foreground">{{ __('Tasks') }}</h4>
                                <span class="text-xs text-muted-foreground">
                                    {{ trans_choice(':count task|:count tasks', $project->tasks->count(), ['count' => $project->tasks->count()]) }}
                                </span>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-2">
                                @forelse ($project->tasks as $task)
                                    <div class="space-y-3 rounded-2xl border border-border/70 bg-background/80 p-4" wire:key="task-{{ $task->id }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="space-y-1">
                                                <p class="text-sm font-medium {{ $task->is_completed ? 'line-through text-muted-foreground' : '' }}">
                                                    {{ $task->title }}
                                                </p>
                                                <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                                    <span>{{ __('Created by :name', ['name' => $task->user->name ?? 'N/A']) }}</span>
                                                    @if ($task->assignee)
                                                        <span>•</span>
                                                        <span>{{ __('Assigned to :name', ['name' => $task->assignee->name]) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-1">
                                                @if ($task->is_completed)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-1 text-xs font-semibold text-emerald-600">
                                                        {{ __('Completed') }}
                                                    </span>
                                                @endif
                                                <x-filament::button type="button" wire:click="editTask('{{ $task->id }}')" size="xs">
                                                    {{ __('Edit') }}
                                                </x-filament::button>
                                                <x-filament::button
                                                    type="button"
                                                    wire:click="deleteTask('{{ $task->id }}')"
                                                    wire:confirm="{{ __('Delete this task?') }}"
                                                    color="danger"
                                                    size="xs"
                                                >
                                                    {{ __('Delete') }}
                                                </x-filament::button>
                                            </div>
                                        </div>

                                        @if (($assignableByProject[$project->id] ?? collect())->isNotEmpty())
                                            <div class="flex flex-wrap items-center gap-2">
                                                <label class="text-xs uppercase text-muted-foreground" for="assignment-{{ $task->id }}">
                                                    {{ __('Assign') }}
                                                </label>
                                                <select
                                                    id="assignment-{{ $task->id }}"
                                                    wire:model="assignmentBuffer.{{ $task->id }}"
                                                    wire:change="updateTaskAssignment('{{ $task->id }}')"
                                                    class="rounded-lg border border-border bg-background px-2 py-1 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                                >
                                                    <option value="">{{ __('Unassigned') }}</option>
                                                    @foreach ($assignableByProject[$project->id] as $member)
                                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('assignmentBuffer.'.$task->id)
                                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endif

                                        @if ($editTaskId === $task->id)
                                            <form wire:submit.prevent="updateTask" class="space-y-2 rounded-xl border border-border/60 bg-background px-3 py-2">
                                                <input
                                                    id="edit_task_{{ $task->id }}"
                                                    type="text"
                                                    wire:model="editTaskName"
                                                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                                />
                                                <select
                                                    wire:model="editTaskAssigneeId"
                                                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                                >
                                                    <option value="">{{ __('Unassigned') }}</option>
                                                    @foreach ($assignableByProject[$project->id] ?? [] as $member)
                                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="flex flex-wrap gap-2">
                                                    <x-filament::button size="sm" type="submit">
                                                        {{ __('Save') }}
                                                    </x-filament::button>
                                                    <x-filament::button
                                                        size="sm"
                                                        type="button"
                                                        color="gray"
                                                        outlined
                                                        wire:click="$set('editTaskId', null)"
                                                    >
                                                        {{ __('Cancel') }}
                                                    </x-filament::button>
                                                </div>
                                                @error('editTaskName')
                                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                                @enderror
                                                @error('editTaskAssigneeId')
                                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                                @enderror
                                            </form>
                                        @endif

                                        <div class="space-y-2">
                                            <p class="text-xs font-semibold uppercase text-muted-foreground">{{ __('Comments') }}</p>
                                            <div class="space-y-2">
                                                @forelse ($task->comments as $comment)
                                                    <div class="rounded-lg border border-border/60 bg-muted/40 px-3 py-2 text-sm">
                                                        <p class="text-xs text-muted-foreground">
                                                            {{ $comment->user->name ?? __('Unknown user') }} · {{ $comment->created_at?->diffForHumans() }}
                                                        </p>
                                                        <p>{{ $comment->body }}</p>
                                                    </div>
                                                @empty
                                                    <p class="text-xs text-muted-foreground">{{ __('No comments yet.') }}</p>
                                                @endforelse
                                            </div>

                                            <form wire:submit.prevent="addComment(@js($task->id))" class="flex flex-wrap items-center gap-2">
                                                <x-filament::input
                                                    id="comment_{{ $task->id }}"
                                                    type="text"
                                                    placeholder="{{ __('Add a comment') }}"
                                                    wire:model.defer="commentDrafts.{{ $task->id }}"
                                                    class="w-full flex-1 rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                                />
                                                <x-filament::button size="xs" type="submit">
                                                    {{ __('Send') }}
                                                </x-filament::button>
                                                @error('commentDrafts.'.$task->id)
                                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                                @enderror
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-dashed border-border/70 bg-background/70 p-4 text-sm text-muted-foreground">
                                        {{ __('No tasks for this project yet.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
