@php
    $active = $stats['active'] ?? null;
    $onboardingMessages = session('onboarding_messages');
    $projectCount = $stats['projectCount'] ?? 0;
    $taskCount = $stats['taskCount'] ?? 0;
    $completedTaskCount = $stats['completedTaskCount'] ?? 0;
    $taskCompletionRate = $taskCount > 0 ? round(($completedTaskCount / max($taskCount, 1)) * 100) : 0;
    $activeDayNumber = $active['dayNumber'] ?? null;
    $targetDays = $active['targetDays'] ?? 100;
    $daysLeft = $activeDayNumber ? max(0, $targetDays - $activeDayNumber) : null;
    $taskCompletionDescription = $taskCount > 0 ? $taskCompletionRate . '% complétées' : 'Aucune tâche pour le moment';
    $challengeDayValue = $activeDayNumber ? 'Jour ' . min($targetDays, $activeDayNumber) . '/' . $targetDays : 'Aucun challenge';
    $challengeDayDescription = $daysLeft !== null ? $daysLeft . ' jours restants' : 'Rejoignez un challenge';
@endphp

<div class="mx-auto max-w-6xl space-y-6 py-6">
  @if ($needsOnboarding && ! $onboardingMessages)
    <x-filament::card class="border border-primary-200 bg-primary-50 text-primary-900">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm">Complétez l’onboarding pour configurer votre challenge personnalisé.</div>
        <x-filament::button tag="a" href="{{ route('onboarding') }}" size="sm">Continuer</x-filament::button>
      </div>
    </x-filament::card>
  @endif

  @if ($onboardingMessages)
    <x-filament::card class="border border-primary-200 bg-primary-50 text-primary-900">
      <ul class="space-y-1 text-sm">
        @foreach ($onboardingMessages as $message)
          <li>{{ $message }}</li>
        @endforeach
      </ul>
    </x-filament::card>
  @endif
    <x-filament::section
        heading="Tableau de bord"
        description="Bienvenue dans votre espace 100DaysOfCode."
    >
        <div class="flex flex-wrap gap-3">
            <x-filament::button wire:navigate tag="a" href="{{ route('daily-challenge') }}">
                Journal du jour
            </x-filament::button>
            <x-filament::button wire:navigate tag="a" href="{{ route('projects.index') }}" color="gray">
                Gérer mes projets
            </x-filament::button>
            <x-filament::button wire:navigate tag="a" href="{{ route('challenges.index') }}" color="gray">
                Challenges
            </x-filament::button>
        </div>
    </x-filament::section>
    @if (! $active)
        <x-filament::section

        >
            <x-slot name="heading">
                Aucun challenge pour le moment
            </x-slot>

            <x-slot name="description">
                Lancez-vous dans le défi 100DaysOfCode en créant un challenge ou en rejoignant l'invitation d'un
                partenaire.
            </x-slot>
            <div class="space-y-3">

                <div class="flex flex-wrap gap-2">
                    <x-filament::button tag="a" href="{{ route('challenges.index') }}">
                        Accéder aux challenges
                    </x-filament::button>
                </div>
                <p class="text-xs text-muted-foreground">
                    Vous pouvez aussi suivre un lien d'invitation partagé pour rejoindre un challenge existant.
                </p>
            </div>
        </x-filament::section>
    @else
        @if (! empty($newBadges))
            <x-filament::card class="border border-green-200 bg-green-50 text-green-900">
                <div class="space-y-2 text-sm">
                    <p class="font-semibold">🎉 Nouveaux badges débloqués !</p>
                    <ul class="space-y-1">
                        @foreach ($newBadges as $badge)
                            <li>
                                <span class="font-medium">{{ $badge['label'] }}</span>
                                @if (! empty($badge['description']))
                                    — {{ $badge['description'] }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-filament::card>
        @endif

        <x-filament::card heading="Progression quotidienne">
            <div class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-sm text-muted-foreground">
                            @if ($dailyProgress['hasEntryToday'])
                                Entrée du jour complétée !
                            @else
                                Aucune entrée aujourd'hui pour l'instant.
                            @endif
                        </p>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                            <span>Streak actuel : {{ $dailyProgress['streak'] }} {{ \Illuminate\Support\Str::plural('jour', $dailyProgress['streak']) }}</span>
                            <span>•</span>
                            <span>{{ $dailyProgress['totalLogs'] }} entrée{{ $dailyProgress['totalLogs'] === 1 ? '' : 's' }} enregistrée{{ $dailyProgress['totalLogs'] === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                    <x-filament::badge :color="$dailyProgress['hasEntryToday'] ? 'success' : 'warning'">
                        {{ $dailyProgress['hasEntryToday'] ? 'Journal complété' : 'À compléter' }}
                    </x-filament::badge>
                </div>

                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full rounded-full bg-primary transition-all"
                        style="width: {{ $dailyProgress['completionPercent'] }}%"
                    ></div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div class="rounded-lg border border-border bg-background px-3 py-2 text-sm">
                        <p class="text-xs uppercase text-muted-foreground">Dernière entrée</p>
                        <p class="font-medium">
                            @if ($dailyProgress['lastEntryAt'])
                                {{ $dailyProgress['lastEntryAt']->translatedFormat('d/m/Y') }} · {{ $dailyProgress['lastEntryAt']->diffForHumans() }}
                            @else
                                Aucune entrée pour le moment
                            @endif
                        </p>
                    </div>

                    <div class="rounded-lg border border-border bg-background px-3 py-2 text-sm">
                        <p class="text-xs uppercase text-muted-foreground">Heures codées aujourd'hui</p>
                        <p class="font-medium">
                            {{ $dailyProgress['hasEntryToday'] ? $dailyProgress['hoursToday'] : '—' }}
                        </p>
                    </div>
                </div>

                @unless ($dailyProgress['hasEntryToday'])
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-dashed border-amber-300 bg-amber-50 px-3 py-3 text-sm text-amber-900">
                        <p>Complétez votre entrée quotidienne pour conserver votre streak.</p>
                        <x-filament::button tag="a" href="{{ route('daily-challenge') }}" size="sm">
                            Renseigner ma journée
                        </x-filament::button>
                    </div>
                @endunless

                @if (! empty($dailyProgress['badges']))
                    <div class="flex flex-wrap gap-2 pt-2">
                        @foreach ($dailyProgress['badges'] as $badge)
                            <x-filament::badge :color="$badge['color'] ?? 'primary'" size="sm">
                                {{ $badge['label'] }}
                            </x-filament::badge>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-filament::card>

        @if (! empty($earnedBadges))
        <x-filament::card heading="Badges obtenus">
          <div class="flex flex-wrap gap-2">
            @foreach ($earnedBadges as $badge)
              <x-filament::badge :color="$badge['color'] ?? 'primary'">
                {{ $badge['label'] }}
              </x-filament::badge>
            @endforeach
          </div>
          <ul class="mt-4 space-y-2 text-sm text-muted-foreground">
            @foreach ($earnedBadges as $badge)
              <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-border/60 px-3 py-2">
                <div>
                  <span class="font-medium text-foreground">{{ $badge['label'] }}</span>
                  <p>{{ $badge['description'] ?? 'Badge débloqué.' }}</p>
                </div>
                <span class="text-xs">{{ optional($badge['awarded_at'])->diffForHumans() }}</span>
              </li>
            @endforeach
          </ul>
        </x-filament::card>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-filament::card heading="Total Projets">
                <div class="text-3xl font-semibold">{{ $projectCount }}</div>
                <p class="text-sm text-muted-foreground">Projets suivis pendant le défi</p>
            </x-filament::card>
            <x-filament::card heading="Total Tâches">
                <div class="text-3xl font-semibold">{{ $taskCount }}</div>
                <p class="text-sm text-muted-foreground">Tâches planifiées</p>
            </x-filament::card>
            <x-filament::card heading="Tâches complétées">
                <div class="text-3xl font-semibold">{{ $completedTaskCount }}</div>
                <p class="text-sm text-muted-foreground">{{ $taskCompletionDescription }}</p>
            </x-filament::card>
            <x-filament::card heading="Jours du défi">
                <div class="text-2xl font-semibold">{{ $challengeDayValue }}</div>
                <p class="text-sm text-muted-foreground">{{ $challengeDayDescription }}</p>
            </x-filament::card>
        </div>

        <x-filament::card heading="Progression du défi">
            @if ($active)
                @php
                    $run = $active['run'] ?? null;
                    $percent = (int) ($active['myPercent'] ?? 0);
                    $boundedPercent = max(0, min(100, $percent));
                @endphp

                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-muted-foreground">
                                {{ $run?->title ?? 'Challenge actif' }}
                            </p>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                @if ($activeDayNumber)
                                    <span>Jour {{ min($targetDays, $activeDayNumber) }}/{{ $targetDays }}</span>
                                @endif
                                @if ($daysLeft !== null)
                                    <span>•</span>
                                    <span>{{ $daysLeft }} jours restants</span>
                                @endif
                            </div>
                        </div>
                        <x-filament::badge color="primary">
                            {{ $boundedPercent }}% accompli
                        </x-filament::badge>
                    </div>

                    <div class="h-3 w-full overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-primary transition-all"
                            style="width: {{ $boundedPercent }}%"
                        ></div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if ($run)
                            <x-filament::button tag="a" href="{{ route('challenges.show', $run->id) }}">
                                Voir le challenge
                            </x-filament::button>
                        @endif
                        <x-filament::button tag="a" href="{{ route('daily-challenge') }}" color="gray">
                            Renseigner ma journée
                        </x-filament::button>
                    </div>
                </div>
            @endif
        </x-filament::card>

        <x-filament::section
            heading="Suivi quotidien"
            description="Complétez votre entrée de la journée pour garder votre dynamique."
        >
            <livewire:page.daily-challenge />
        </x-filament::section>

        <x-filament::section>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Mes projets récents</h2>
                    <p class="text-sm text-muted-foreground">Les projets sur lesquels vous avez travaillé
                        dernièrement.</p>
                </div>
                <x-filament::button tag="a" href="{{ route('projects.index') }}" color="gray">
                    Tous les projets
                </x-filament::button>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($recentProjects as $project)
                    <x-filament::card :heading="$project->name">
                        <x-slot name="description">
                            {{ $project->description ?? 'Aucune description disponible pour ce projet.' }}
                        </x-slot>

                        <div class="space-y-3 text-sm">
                            <x-filament::badge color="primary">
                                {{ $project->tasks->count() }} tâches
                            </x-filament::badge>
                            <div class="text-xs text-muted-foreground">
                                Créé le {{ $project->created_at->format('d/m/Y') }}
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <x-filament::button
                                    tag="a"
                                    href="{{ route('projects.tasks.index', ['project' => $project->id]) }}"
                                    size="sm"
                                    color="gray"
                                >
                                    Voir détails
                                </x-filament::button>
                                <x-filament::badge color="success">
                                    {{ $project->tasks->where('is_completed', true)->count() }}
                                    /{{ $project->tasks->count() }} complétées
                                </x-filament::badge>
                            </div>
                        </div>
                    </x-filament::card>
                @empty
                    <x-filament::card heading="Aucun projet">
                        <x-slot name="description">
                            Commencez par créer votre premier projet pour le défi 100DaysOfCode.
                        </x-slot>

                        <x-filament::button tag="a" href="{{ route('projects.index') }}">
                            Créer un projet
                        </x-filament::button>
                    </x-filament::card>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::card id="tasks" heading="Mes tâches récentes">
            <div class="space-y-4">
                @forelse ($recentTasks as $task)
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-medium">{{ $task->title }}</h3>
                            <p class="text-xs text-muted-foreground">
                                Projet : {{ $task->project->name }}
                            </p>
                        </div>
                        <x-filament::badge :color="$task->is_completed ? 'success' : 'gray'">
                            {{ $task->is_completed ? 'Terminée' : 'À faire' }}
                        </x-filament::badge>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-muted-foreground">
                        Vous n'avez pas encore créé de tâches pour vos projets.
                    </p>
                @endforelse
            </div>

            <div class="mt-4">
                <x-filament::button tag="a" href="#" color="gray">
                    Toutes les tâches
                </x-filament::button>
            </div>
        </x-filament::card>
    @endif
</div>
