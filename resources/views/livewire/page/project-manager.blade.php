<div class="mx-auto max-w-5xl space-y-6 py-6">
    <x-filament::section>
        <x-slot name="heading">
            Gestion des projets
        </x-slot>
        <x-slot name="description">
            @if ($activeRun)
                Challenge actuel&nbsp;:
                <x-filament::link wire:navigate href="{{ route('challenges.show', $activeRun->id) }}">
                    {{ $activeRun->title ?? '100 Days of Code' }}
                </x-filament::link>
                &mdash; démarré le {{ $activeRun->start_date->translatedFormat('d F Y') }}
            @else
                Aucun challenge actif pour l'instant.
                <x-filament::link wire:navigate href="{{ route('challenges.index') }}">
                    Créez ou rejoignez un challenge pour collaborer sur vos projets.
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
                    <x-slot name="heading">Aucun challenge actif</x-slot>
                    <p class="text-sm text-muted-foreground">
                        Créez ou rejoignez d'abord un challenge pour pouvoir ajouter des projets et leurs tâches associées.
                    </p>
                    <x-filament::button tag="a" href="{{ route('challenges.index') }}" class="mt-4">
                        Voir les challenges
                    </x-filament::button>
                </x-filament::card>
            @else
                <div class="grid gap-4 md:grid-cols-2">
                    <x-filament::card>
                        <x-slot name="heading">Créer un projet</x-slot>

                        <form wire:submit.prevent="createProject" class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="project_name">Nom du projet</label>
                                <input
                                    id="project_name"
                                    type="text"
                                    wire:model="projectName"
                                    placeholder="Mon super projet"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
                                @error('projectName')
                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            @if ($templates->isNotEmpty())
                                <div class="space-y-2">
                                    <label class="text-sm font-medium" for="project_template">Modèle</label>
                                    <select
                                        id="project_template"
                                        wire:model="projectTemplate"
                                        class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                    >
                                        <option value="">-- Sans modèle --</option>
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
                                                {{ $selectedTemplate->description }} ({{ count($selectedTemplate->tasks ?? []) }} tâches précréées)
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            @endif

                            <x-filament::button type="submit">
                                Créer le projet
                            </x-filament::button>

                            <p class="text-xs text-muted-foreground">
                                Le projet sera rattaché au challenge &laquo;&nbsp;{{ $activeRun->title ?? '100 Days of Code' }}&nbsp;&raquo;.
                            </p>
                        </form>
                    </x-filament::card>

                    <x-filament::card>
                        <x-slot name="heading">Créer une tâche</x-slot>

                        <form wire:submit.prevent="createTask" class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="task_name">Nom de la tâche</label>
                                <input
                                    id="task_name"
                                    type="text"
                                    wire:model="taskName"
                                    placeholder="Nouvelle tâche"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                />
                                @error('taskName')
                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium" for="task_project">Projet associé</label>
                                <select
                                    id="task_project"
                                    wire:model="taskProjectId"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                >
                                    <option value="">-- Choisir un projet --</option>
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
                                        <label class="text-sm font-medium" for="task_assignee">Assigner à</label>
                                        <select
                                            id="task_assignee"
                                            wire:model="taskAssigneeId"
                                            class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                        >
                                            <option value="">-- Assigner plus tard --</option>
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
                                Créer la tâche
                            </x-filament::button>
                        </form>
                    </x-filament::card>
                </div>
            @endif
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Mes projets
        </x-slot>
        <x-slot name="description">
            @if ($projects->isEmpty())
                Créez votre premier projet pour suivre vos objectifs 100DaysOfCode.
            @else
                Gérez vos projets, leurs membres et leurs tâches quotidiennes.
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
                                        {{ $project->tasks->count() }} {{ \Illuminate\Support\Str::plural('tâche', $project->tasks->count()) }}
                                    </x-filament::badge>
                                    @if ($project->created_at)
                                        <span>Créé le {{ $project->created_at->translatedFormat('d F Y') }}</span>
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
                                    Voir les tâches
                                </x-filament::button>
                                <x-filament::button
                                        type="button"
                                        wire:click="editProject({{ $project->id }})"
                                        size="sm"
                                >
                                    Éditer
                                </x-filament::button>
                                <x-filament::button
                                        type="button"
                                        wire:click="deleteProject({{ $project->id }})"
                                        wire:confirm="Supprimer ce projet ?"
                                        color="danger"
                                        size="sm"
                                >
                                    Supprimer
                                </x-filament::button>
                            </div>
                            @if ($templates->isNotEmpty())
                                <form wire:submit.prevent="applyTemplateToProject(@js($project->id))" class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                    <label class="font-medium" for="project-template-{{ $project->id }}">Appliquer un modèle :</label>
                                    <select
                                        id="project-template-{{ $project->id }}"
                                        wire:model="templateSelection.{{ $project->id }}"
                                        class="rounded-lg border border-border bg-background px-2 py-1 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                    >
                                        <option value="">-- Sélectionner --</option>
                                        @foreach ($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-filament::button size="xs" type="submit">
                                        Ajouter les tâches
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
                                    Valider
                                </x-filament::button>
                                <x-filament::button
                                        size="sm"
                                        type="button"
                                        color="gray"
                                        outlined
                                        wire:click="$set('editProjectId', null)"
                                >
                                    Annuler
                                </x-filament::button>
                                @error('editProjectName')
                                <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </form>
                        @endif

                        <div class="grid gap-3 text-sm md:grid-cols-2">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Créateur</p>
                                <p>{{ $project->user->name ?? 'N/A' }}</p>
                            </div>

                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Membres</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($project->members as $member)
                                        <span class="rounded-full bg-secondary px-3 py-1 text-xs font-medium text-secondary-foreground">
                      {{ $member->name }}
                    </span>
                                    @empty
                                        <span class="text-xs text-muted-foreground">Aucun membre</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase text-muted-foreground">Tâches</p>
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
                                                    <span>Créée par {{ $task->user->name ?? 'N/A' }}</span>
                                                    @if ($task->assignee)
                                                        <span>•</span>
                                                        <span>Assignée à {{ $task->assignee->name }}</span>
                                                    @endif
                                                </div>
                                            </div>


                                            <div class="flex flex-wrap items-center gap-2">
                                                @if ($task->is_completed)
                                                    <x-filament::badge color="success">
                                                        Terminée
                                                    </x-filament::badge>
                                                @endif

                                                <x-filament::button
                                                    type="button"
                                                    wire:click="editTask('{{ $task->id }}')"
                                                    size="xs"
                                                >
                                                    Éditer
                                                </x-filament::button>
                                                <x-filament::button
                                                    type="button"
                                                    wire:click="deleteTask('{{ $task->id }}')"
                                                    wire:confirm="Supprimer cette tâche ?"
                                                    color="danger"
                                                    size="xs"
                                                >
                                                    Supprimer
                                                </x-filament::button>
                                            </div>
                                        </div>

                                        @if (($assignableByProject[$project->id] ?? collect())->isNotEmpty())
                                            <div class="flex flex-wrap items-center gap-2">
                                                <label class="text-xs uppercase text-muted-foreground" for="assignment-{{ $task->id }}">
                                                    Assigner
                                                </label>
                                                <select
                                                    id="assignment-{{ $task->id }}"
                                                    wire:model="assignmentBuffer.{{ $task->id }}"
                                                    wire:change="updateTaskAssignment('{{ $task->id }}')"
                                                    class="rounded-lg border border-border bg-background px-2 py-1 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                                >
                                                    <option value="">Non assignée</option>
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
                                                    <option value="">Non assignée</option>
                                                    @foreach ($assignableByProject[$project->id] ?? [] as $member)
                                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                    @endforeach
                                                </select>
                                                <x-filament::button size="sm" type="submit">
                                                    Valider
                                                </x-filament::button>
                                                <x-filament::button
                                                    size="sm"
                                                    type="button"
                                                    color="gray"
                                                    outlined
                                                    wire:click="$set('editTaskId', null)"
                                                >
                                                    Annuler
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
                                            <p class="text-xs uppercase text-muted-foreground">Commentaires</p>
                                            <div class="space-y-2">
                                                @forelse ($task->comments as $comment)
                                                    <div class="rounded-lg border border-border/60 bg-muted/40 px-3 py-2 text-sm">
                                                        <p class="text-xs text-muted-foreground">
                                                            {{ $comment->user->name ?? 'Utilisateur inconnu' }} · {{ $comment->created_at?->diffForHumans() }}
                                                        </p>
                                                        <p>{{ $comment->body }}</p>
                                                    </div>
                                                @empty
                                                    <p class="text-xs text-muted-foreground">Pas encore de commentaire.</p>
                                                @endforelse
                                            </div>

                                            <form wire:submit.prevent="addComment(@js($task->id))" class="flex flex-wrap items-center gap-2">
                                                <x-filament::input
                                                    id="comment_{{ $task->id }}"
                                                    type="text"
                                                    placeholder="Ajouter un commentaire"
                                                    wire:model.defer="commentDrafts.{{ $task->id }}"
                                                    class="w-full flex-1 rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                                                />
                                                <x-filament::button size="xs" type="submit">
                                                    Envoyer
                                                </x-filament::button>
                                                @error('commentDrafts.'.$task->id)
                                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                                @enderror
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-muted-foreground">Aucune tâche pour ce projet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </x-filament::card>
            @empty
                <x-filament::card>
                    <x-slot name="heading">Aucun projet</x-slot>
                    <p class="text-sm text-muted-foreground">
                        Lancez votre premier projet pour documenter vos progrès.
                    </p>
                </x-filament::card>
            @endforelse
        </div>
    </x-filament::section>
</div>
