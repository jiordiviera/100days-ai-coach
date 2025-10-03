<div class="mx-auto max-w-3xl space-y-6 py-8">
  <x-filament::section>
    <x-slot name="heading">
      Tâches du projet
    </x-slot>
    <x-slot name="description">
      Gérez vos tâches quotidiennes pour « {{ $project->name }} ».
    </x-slot>

    @if (session()->has('message'))
      <div class="mb-4 rounded-md bg-green-100 px-3 py-2 text-sm text-green-800 dark:bg-green-900/60 dark:text-green-200">
        {{ session('message') }}
      </div>
    @endif

    <x-filament::card>
      <form wire:submit.prevent="createTask" class="space-y-4">
        <div>
          <label class="text-sm font-medium">Nom de la tâche</label>
          <input
            type="text"
            wire:model="taskName"
            placeholder="Nom de la tâche..."
            class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
          />
          @error('taskName')
            <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
          @enderror
        </div>

        @if (! empty($assignableUsers))
          <div>
            <label class="text-sm font-medium" for="task_assignee">Assigner à</label>
            <select
              id="task_assignee"
              wire:model="taskAssigneeId"
              class="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
            >
              <option value="">-- Assigner plus tard --</option>
              @foreach ($assignableUsers as $member)
                <option value="{{ $member->id }}">{{ $member->name }}</option>
              @endforeach
            </select>
            @error('taskAssigneeId')
              <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
            @enderror
          </div>
        @endif

        <x-filament::button type="submit">
          Créer la tâche
        </x-filament::button>
      </form>
    </x-filament::card>

    <div class="space-y-4">
      @forelse ($project->tasks as $task)
        <x-filament::card wire:key="task-{{ $task->id }}">
          <div class="space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div class="space-y-1">
                <p class="text-base font-semibold {{ $task->is_completed ? 'text-muted-foreground line-through' : '' }}">
                  {{ $task->title }}
                </p>
                <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                  <span>Créée par {{ $task->user->name ?? 'N/A' }}</span>
                  @if ($task->assignee)
                    <span>•</span>
                    <span>Assignée à {{ $task->assignee->name }}</span>
                  @endif
                </div>
                @if ($task->is_completed)
                  <x-filament::badge color="success">
                    Terminée
                  </x-filament::badge>
                @endif
              </div>

              <div class="flex flex-wrap gap-2">
                @if (! $task->is_completed)
                  <x-filament::button
                    wire:click="completeTask('{{ $task->id }}')"
                    size="sm"
                    color="success"
                    type="button"
                  >
                    Marquer comme terminée
                  </x-filament::button>
                @endif

                <x-filament::button
                  wire:click="editTask({{ $task->id }})"
                  size="sm"
                  type="button"
                >
                  Éditer
                </x-filament::button>

                <x-filament::button
                  wire:confirm="Supprimer cette tâche ?"
                  wire:click="deleteTask({{ $task->id }})"
                  size="sm"
                  color="danger"
                  type="button"
                >
                  Supprimer
                </x-filament::button>
              </div>
            </div>

            @if (! empty($assignableUsers))
              <div class="flex flex-wrap items-center gap-2">
                <label class="text-xs uppercase text-muted-foreground" for="assign-{{ $task->id }}">Assigner</label>
                <select
                  id="assign-{{ $task->id }}"
                  wire:model="assignmentBuffer.{{ $task->id }}"
                  wire:change="updateTaskAssignment('{{ $task->id }}')"
                  class="rounded-lg border border-border bg-background px-2 py-1 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
                >
                  <option value="">Non assignée</option>
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
              <form wire:submit.prevent="updateTask" class="flex flex-wrap items-center gap-2" wire:key="task-edit-{{ $task->id }}">
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
                    <option value="">Non assignée</option>
                    @foreach ($assignableUsers as $member)
                      <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                  </select>
                @endif
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
                  <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                @enderror
                @error('editTaskAssigneeId')
                  <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                @enderror
              </form>
            @endif

            <div class="space-y-2">
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

              <form wire:submit.prevent="addComment('{{ $task->id }}')" class="flex flex-wrap items-center gap-2">
                <input
                  type="text"
                  wire:model.defer="commentDrafts.{{ $task->id }}"
                  placeholder="Ajouter un commentaire"
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
        </x-filament::card>
      @empty
        <x-filament::card>
          <x-slot name="heading">
            Aucune tâche pour ce projet
          </x-slot>
          <p class="text-sm text-muted-foreground">
            Ajoutez votre première tâche pour commencer à progresser !
          </p>
        </x-filament::card>
      @endforelse
    </div>

    <x-filament::button tag="a" href="{{ route('projects.index') }}" color="gray">
      Retour aux projets
    </x-filament::button>
  </x-filament::section>
</div>
