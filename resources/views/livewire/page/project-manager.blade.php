<div class="mx-auto max-w-5xl space-y-6 py-6">
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Project management') }}
        </x-slot>
        <x-slot name="description">
            @if ($activeRun)
                {{ __('Current challenge:') }}
                <x-filament::link wire:navigate href="{{ route('challenges.show', $activeRun->id) }}">
                    {{ $activeRun->title ?? '100 Days of Code' }}
                </x-filament::link>
                &mdash; {{ __('started on :date', ['date' => $activeRun->start_date->translatedFormat('F j, Y')]) }}
            @else
                {{ __('No active challenge at the moment.') }}
                <x-filament::link wire:navigate href="{{ route('challenges.index') }}">
                    {{ __('Create or join a challenge to collaborate on your projects.') }}
                </x-filament::link>
            @endif
        </x-slot>

        <div class="space-y-4">
            @if ($feedbackMessage)
                <x-filament::card class="{{ $feedbackType === 'error' ? 'border border-danger-200 bg-danger-50 text-danger-900 dark:border-danger-900/40 dark:bg-danger-900/30 dark:text-danger-100' : 'border border-success-200 bg-success-50 text-success-900 dark:border-success-900/40 dark:bg-success-900/30 dark:text-success-100' }}">
                    {{ $feedbackMessage }}
                </x-filament::card>
            @endif

            @if (! $activeRun)
                <x-filament::card>
                    <x-slot name="heading">{{ __('No active challenge') }}</x-slot>
                    <p class="text-sm text-muted-foreground">
                        {{ __('Create or join a challenge first to add projects and tasks.') }}
                    </p>
                    <x-filament::button tag="a" href="{{ route('challenges.index') }}" class="mt-4">
                        {{ __('Browse challenges') }}
                    </x-filament::button>
                </x-filament::card>
            @else
                <div class="grid gap-4 md:grid-cols-2">
                    <x-filament::card>
                        <x-slot name="heading">{{ __('Create a project') }}</x-slot>

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

                            <p class="text-xs text-muted-foreground">
                                {{ __('The project will be attached to the challenge “:title”.', ['title' => $activeRun->title ?? '100 Days of Code']) }}
                            </p>
                        </form>
                    </x-filament::card>

                    <x-filament::card>
                        <x-slot name="heading">{{ __('Create a task') }}</x-slot>

                        <form wire:submit.prevent="createTask" class="space-y-4">
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
                    </x-filament::card>
                </div>
            @endif
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            {{ __('My projects') }}
        </x-slot>
        <x-slot name="description">
            @if ($projects->isEmpty())
                {{ __('Create your first project to track your 100DaysOfCode goals.') }}
            @else
                {{ __('Manage your projects, members, and daily tasks.') }}
            @endif
        </x-slot>

        <div class="space-y-4">
            @forelse ($projects as $project)
                <x-filament::card wire:key="project-{{ $project->id }}">
                    <x-slot name="heading">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="space-y-1">
                                <p class="text-base font-semibold">{{ $project->name }}</p>
                                <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                    <x-filament::badge color="primary">
                                        {{ $project->tasks->count() }} {{ \Illuminate\Support\Str::plural('task', $project->tasks->count()) }}
                                    </x-filament::badge>
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
                                <x-filament::button
                                        type="button"
                                        wire:click="editProject({{ $project->id }})"
                                        size="sm"
                                >
                                    {{ __('Edit') }}
                                </x-filament::button>
                                <x-filament::button
                                        type="button"
                                        wire:click="deleteProject({{ $project->id }})"
                                        wire:confirm="{{ __('Delete this project?') }}"
                                        color="danger"
                                        size="sm"
                                >
                                    {{ __('Delete') }}
                                </x-filament::button>
                            </div>
                            @if ($templates->isNotEmpty())
                                <form wire:submit.prevent="applyTemplateToProject(@js($project->id))" class="mt-3 flex flex-wrap items-center gap-2 text-xs">
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
                        </div>
                    </x-slot>

                    <div class="space-y-4">
                        @if ($editProjectId === $project->id)
                            <form wire:submit.prevent="updateProject" class="flex flex-wrap items-center gap-2">
                                <input
                                        id="edit_project_{{ $project->id }}"
                                        type="text"
                                        wire:model="editProjectName"
                                        class="w-full min-w-[12rem] flex-1 rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
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
                                @error('editProjectName')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </form>
                        @endif

                        <div class="grid gap-3 text-sm md:grid-cols-2">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">{{ __('Owner') }}</p>
                                <p>{{ $project->user->name ?? 'N/A' }}</p>
                            </div>

                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">{{ __('Members') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($project->members as $member)
                                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-medium text-secondary-foreground">
                      {{ $member->name }}
                    </span>
                                    @empty
                                        <span class="text-xs text-muted-foreground">{{ __('No members yet') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase text-muted-foreground">{{ __('Tasks') }}</p>
                            <div class="space-y-3">
                                @forelse ($project->tasks as $task)
                                    <div class="space-y-2 rounded-lg border border-border/70 bg-background p-3"
                                         wire:key="task-{{ $task->id }}">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
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


                                            <div class="flex flex-wrap items-center gap-2">
                                                @if ($task->is_completed)
                                                    <x-filament::badge color="success">
                                                        {{ __('Completed') }}
                                                    </x-filament::badge>
                                                @endif

                                                <x-filament::button
                                                    type="button"
                                                    wire:click="editTask('{{ $task->id }}')"
                                                    size="xs"
                                                >
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
                                            <form wire:submit.prevent="updateTask"
                                                  class="mt-2 flex flex-wrap items-center gap-2">
                                                <input
                                                    id="edit_task_{{ $task->id }}"
                                                    type="text"
                                                    wire:model="editTaskName"
                                                    class="w-full min-w-[12rem] flex-1 rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                                />
                                                <select
                                                    wire:model="editTaskAssigneeId"
                                                    class="w-full min-w-[10rem] rounded-lg border border-border bg-background px-2 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                                >
                                                    <option value="">{{ __('Unassigned') }}</option>
                                                    @foreach ($assignableByProject[$project->id] ?? [] as $member)
                                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                    @endforeach
                                                </select>
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
                                                @error('editTaskName')
                                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                                @enderror
                                                @error('editTaskAssigneeId')
                                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                                @enderror
                                            </form>
                                        @endif

                                        <div class="space-y-2 pt-2">
                                            <p class="text-xs uppercase text-muted-foreground">{{ __('Comments') }}</p>
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
                                    <p class="text-sm text-muted-foreground">{{ __('No tasks for this project yet.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </x-filament::card>
            @empty
                <x-filament::card>
                    <x-slot name="heading">{{ __('No projects') }}</x-slot>
                    <p class="text-sm text-muted-foreground">
                        {{ __('Start your first project to document your progress.') }}
                    </p>
                </x-filament::card>
            @endforelse
        </div>
    </x-filament::section>
</div>
