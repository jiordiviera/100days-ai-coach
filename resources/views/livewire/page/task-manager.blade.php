@php
    $totalTasks = $project->tasks->count();
    $completedTasks = $project->tasks->where('is_completed', true)->count();
    $openTasks = max($totalTasks - $completedTasks, 0);
    $assignableCount = count($assignableUsers ?? []);
@endphp

<div class="mx-auto max-w-6xl space-y-12 px-4 py-12 sm:px-6 lg:px-0">
    <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
        <div class="absolute inset-0">
            <div class="absolute -left-20 top-1/2 h-40 w-40 -translate-y-1/2 rounded-full bg-primary/15 blur-3xl"></div>
            <div class="absolute -right-16 top-10 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
        </div>

        <div class="relative flex flex-col gap-10 p-8 lg:flex-row lg:items-center lg:justify-between lg:p-12">
            <div class="space-y-5">
                <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
                    {{ __('Task manager') }}
                </span>

                <div class="space-y-3">
                    <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">
                        {{ $project->name }}
                    </h1>
                    <p class="text-sm text-muted-foreground sm:max-w-xl">
                        {{ __('Coordinate assignments, follow up on progress, and keep discussions in one place for this project.') }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-filament::button tag="a" href="{{ route('projects.index') }}" color="gray" size="sm" wire:navigate>
                        {{ __('Back to projects') }}
                    </x-filament::button>
                </div>
            </div>

            <div class="grid w-full gap-4 sm:grid-cols-3 lg:w-auto">
                <div class="rounded-2xl border border-border/80 bg-card/95 p-4 shadow-md">
                    <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Open tasks') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-foreground">{{ $openTasks }}</p>
                    <p class="text-xs text-muted-foreground">{{ trans_choice('{0}No items waiting|{1}1 item waiting|[2,*]:count items waiting', $openTasks, ['count' => $openTasks]) }}</p>
                </div>
                <div class="rounded-2xl border border-border/80 bg-card/95 p-4 shadow-md">
                    <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Completed') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-foreground">{{ $completedTasks }}</p>
                    <p class="text-xs text-muted-foreground">{{ __('Great progress so far!') }}</p>
                </div>
                <div class="rounded-2xl border border-border/80 bg-card/95 p-4 shadow-md">
                    <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Collaborators') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-foreground">{{ $assignableCount }}</p>
                    <p class="text-xs text-muted-foreground">{{ __('People available for assignment') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-8 lg:grid-cols-[2fr_1fr]">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('Project tasks') }}
            </x-slot>
            <x-slot name="description">
                {{ __('Review the task board for “:project” and keep everyone aligned.', ['project' => $project->name]) }}
            </x-slot>

            <div class="space-y-5">
                @forelse ($project->tasks as $task)
                    <x-filament::card wire:key="task-{{ $task->id }}" class="border-border/70">
                        <div class="space-y-5">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="text-lg font-semibold {{ $task->is_completed ? 'text-muted-foreground line-through' : '' }}">
                                            {{ $task->title }}
                                        </p>
                                        @if ($task->is_completed)
                                            <x-filament::badge color="success" size="sm">
                                                {{ __('Completed') }}
                                            </x-filament::badge>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                        <span>{{ __('Created by :name', ['name' => $task->user->name ?? 'N/A']) }}</span>
                                        <span>•</span>
                                        <span>{{ __('Updated :time', ['time' => $task->updated_at?->diffForHumans() ?? __('recently')]) }}</span>
                                        @if ($task->assignee)
                                            <span>•</span>
                                            <span>{{ __('Assigned to :name', ['name' => $task->assignee->name]) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @if (! $task->is_completed)
                                        <x-filament::button
                                            wire:click="completeTask('{{ $task->id }}')"
                                            size="sm"
                                            color="success"
                                            type="button"
                                        >
                                            {{ __('Mark as completed') }}
                                        </x-filament::button>
                                    @endif

                                    <x-filament::button
                                        wire:click="editTask('{{ $task->id }}')"
                                        size="sm"
                                        type="button"
                                    >
                                        {{ __('Edit') }}
                                    </x-filiment::button>

                                    <x-filament::button
                                        wire:confirm="{{ __('Delete this task?') }}"
                                        wire:click="deleteTask('{{ $task->id }}')"
                                        size="sm"
                                        color="danger"
                                        type="button"
                                    >
                                        {{ __('Delete') }}
                                    </x-filament::button>
                                </div>
                            </div>

                            @if (! empty($assignableUsers))
                                <div class="flex flex-wrap items-center gap-3 rounded-xl border border-border/70 bg-muted/30 px-3 py-2">
                                    <label class="text-xs font-semibold uppercase tracking-widest text-muted-foreground" for="assign-{{ $task->id }}">
                                        {{ __('Assign') }}
                                    </label>
                                    <select
                                        id="assign-{{ $task->id }}"
                                        wire:model="assignmentBuffer.{{ $task->id }}"
                                        wire:change="updateTaskAssignment('{{ $task->id }}')"
                                        class="rounded-lg border border-border bg-background px-2 py-1 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                    >
                                        <option value="">{{ __('Unassigned') }}</option>
                                        @foreach ($assignableUsers as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('assignmentBuffer.'.$task->id)
                                        <p class="text-xs text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            @if ($editTaskId === $task->id)
                                <form wire:submit.prevent="updateTask" class="flex flex-col gap-3 rounded-xl border border-primary/40 bg-primary/5 p-4 md:flex-row md:items-center md:gap-4" wire:key="task-edit-{{ $task->id }}">
                                    <input
                                        type="text"
                                        wire:model="editTaskName"
                                        class="w-full flex-1 min-w-[12rem] rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                    />
                                    @if (! empty($assignableUsers))
                                        <select
                                            wire:model="editTaskAssigneeId"
                                            class="w-full min-w-[12rem] rounded-lg border border-border bg-background px-2 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                        >
                                            <option value="">{{ __('Unassigned') }}</option>
                                            @foreach ($assignableUsers as $member)
                                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
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
                                    <div class="flex flex-col gap-1">
                                        @error('editTaskName')
                                            <p class="text-xs text-destructive">{{ $message }}</p>
                                        @enderror
                                        @error('editTaskAssigneeId')
                                            <p class="text-xs text-destructive">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </form>
                            @endif

                            <div class="space-y-3 rounded-2xl border border-border/70 bg-muted/20 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                                        {{ __('Comments') }}
                                    </p>
                                    <span class="text-xs text-muted-foreground">
                                        {{ trans_choice(':count comment|:count comments', $task->comments->count(), ['count' => $task->comments->count()]) }}
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    @forelse ($task->comments as $comment)
                                        <div class="rounded-xl border border-border/60 bg-card/90 px-3 py-2 text-sm shadow-sm">
                                            <p class="text-xs text-muted-foreground">
                                                {{ $comment->user->name ?? __('Unknown user') }} · {{ $comment->created_at?->diffForHumans() }}
                                            </p>
                                            <p class="mt-1">{{ $comment->body }}</p>
                                        </div>
                                    @empty
                                        <p class="text-xs text-muted-foreground">{{ __('No comments yet.') }}</p>
                                    @endforelse
                                </div>

                                <form wire:submit.prevent="addComment('{{ $task->id }}')" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <input
                                        type="text"
                                        wire:model.defer="commentDrafts.{{ $task->id }}"
                                        placeholder="{{ __('Add a comment') }}"
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
                    </x-filament::card>
                @empty
                    <div class="rounded-3xl border border-border/70 bg-card/95 p-8 text-center shadow-sm">
                        <h2 class="text-lg font-semibold text-foreground">{{ __('No tasks for this project yet.') }}</h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ __('Add your first task to start making progress!') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                {{ __('Create a task') }}
            </x-slot>
            <x-slot name="description">
                {{ __('Capture a new action item and optionally assign it to a teammate.') }}
            </x-slot>

            <x-filament::card class="border-border/70">
                <form wire:submit.prevent="createTask" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="task_name">{{ __('Task name') }}</label>
                        <input
                            id="task_name"
                            type="text"
                            wire:model="taskName"
                            placeholder="{{ __('Task name...') }}"
                            class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                        />
                        @error('taskName')
                            <p class="text-xs text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    @if (! empty($assignableUsers))
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="task_assignee">{{ __('Assign to') }}</label>
                            <select
                                id="task_assignee"
                                wire:model="taskAssigneeId"
                                class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                            >
                                <option value="">{{ __('Assign later') }}</option>
                                @foreach ($assignableUsers as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                            @error('taskAssigneeId')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="border-t border-border/60 pt-4">
                        <x-filament::button type="submit" class="w-full justify-center">
                            {{ __('Create task') }}
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::card>

            <div class="rounded-2xl border border-border/70 bg-muted/20 p-4 text-sm text-muted-foreground">
                <p>{{ __('Need to adjust project members? Manage collaborators from the project overview to make them appear here.') }}</p>
            </div>
        </x-filament::section>
    </div>
</div>
