<div class="mx-auto max-w-6xl space-y-6 py-6">
  <x-filament::section>
    <x-slot name="heading">
      Insights du challenge
    </x-slot>
    <x-slot name="description">
      Analyse de la progression collective pour « {{ $run->title ?? '100 Days of Code' }} ».
    </x-slot>

    <div class="flex flex-wrap gap-2">
      <x-filament::button tag="a" wire:navigate href="{{ route('challenges.show', $run->id) }}" color="gray">
        Retour au challenge
      </x-filament::button>
      <x-filament::button tag="a" wire:navigate href="{{ route('daily-challenge') }}">
        Journal du jour
      </x-filament::button>
    </div>
  </x-filament::section>

  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <x-filament::card heading="Participants">
      <div class="text-3xl font-semibold">{{ $overview['totalParticipants'] }}</div>
      <p class="text-sm text-muted-foreground">Membres du challenge (owner inclus)</p>
    </x-filament::card>
    <x-filament::card heading="Logs enregistrés">
      <div class="text-3xl font-semibold">{{ $overview['totalLogs'] }}</div>
      <p class="text-sm text-muted-foreground">Entrées quotidiennes cumulées</p>
    </x-filament::card>
    <x-filament::card heading="Heures totales">
      <div class="text-3xl font-semibold">{{ $overview['totalHours'] }}</div>
      <p class="text-sm text-muted-foreground">{{ $overview['hoursPerParticipant'] }} h / participant</p>
    </x-filament::card>
    <x-filament::card heading="Progression moyenne">
      <div class="text-3xl font-semibold">{{ $overview['completionAverage'] }}%</div>
      <p class="text-sm text-muted-foreground">Basée sur {{ $overview['targetDays'] }} jours</p>
    </x-filament::card>
    <x-filament::card heading="Projets">
      <div class="text-3xl font-semibold">{{ $overview['projectsCount'] }}</div>
      <p class="text-sm text-muted-foreground">{{ $overview['tasksCompleted'] }}/{{ $overview['tasksTotal'] }} tâches complétées</p>
    </x-filament::card>
    <x-filament::card heading="Commentaires">
      <div class="text-3xl font-semibold">{{ $overview['commentsCount'] }}</div>
      <p class="text-sm text-muted-foreground">Échanges sur les tâches</p>
    </x-filament::card>
    <x-filament::card heading="Jours écoulés">
      <div class="text-3xl font-semibold">{{ $overview['daysElapsed'] ?? '—' }}</div>
      <p class="text-sm text-muted-foreground">
        @if ($overview['daysRemaining'] !== null)
          {{ $overview['daysRemaining'] }} jours restants
        @else
          Date de début inconnue
        @endif
      </p>
    </x-filament::card>
    <x-filament::card heading="Heures moyennes">
      <div class="text-3xl font-semibold">{{ $overview['averageHours'] }}</div>
      <p class="text-sm text-muted-foreground">Par entrée quotidienne</p>
    </x-filament::card>
  </div>

  <x-filament::section heading="Classement des participants">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[32rem] text-sm">
        <thead class="text-left text-xs uppercase text-muted-foreground">
          <tr class="border-b border-border/70">
            <th class="py-2">Participant</th>
            <th class="py-2">Logs</th>
            <th class="py-2">Heures</th>
            <th class="py-2">Streak</th>
            <th class="py-2">Complétion</th>
            <th class="py-2">Dernier log</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border/60">
          @foreach ($participantStats as $row)
            <tr>
              <td class="py-2">
                <div class="flex items-center gap-2">
                  <span class="font-medium">{{ $row['user']->name }}</span>
                  @if ($row['user']->id === $run->owner_id)
                    <x-filament::badge color="primary" size="sm">Owner</x-filament::badge>
                  @endif
                </div>
              </td>
              <td class="py-2">{{ $row['logs'] }}</td>
              <td class="py-2">{{ $row['hours'] }} h</td>
              <td class="py-2">{{ $row['streak'] }} {{ \Illuminate\Support\Str::plural('jour', $row['streak']) }}</td>
              <td class="py-2">{{ $row['percent'] }}%</td>
              <td class="py-2">
                @if ($row['lastLogAt'])
                  {{ $row['lastLogAt']->translatedFormat('d/m/Y') }} · {{ $row['lastLogAt']->diffForHumans() }}
                @else
                  —
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </x-filament::section>

  <x-filament::section heading="Jalons du défi">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
      @foreach ($milestones as $milestone)
        <x-filament::card>
          <x-slot name="heading">{{ $milestone['label'] }}</x-slot>
          <div class="space-y-2 text-sm">
            <p>Jour cible : {{ $milestone['targetDay'] }}</p>
            <p>
              Date estimée :
              @if ($milestone['expectedDate'])
                {{ $milestone['expectedDate']->translatedFormat('d/m/Y') }}
              @else
                —
              @endif
            </p>
            <x-filament::badge :color="$milestone['achieved'] ? 'success' : 'gray'">
              {{ $milestone['achieved'] ? 'Atteint' : 'À venir' }}
            </x-filament::badge>
          </div>
        </x-filament::card>
      @endforeach
    </div>
  </x-filament::section>

  <x-filament::section heading="Activité récente">
    <div class="space-y-2 text-sm">
      @forelse ($activity as $entry)
        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-border/60 bg-muted/40 px-3 py-2">
          <div>
            {{ $entry['date']->translatedFormat('d F Y') }}
          </div>
          <div class="flex gap-4 text-xs uppercase text-muted-foreground">
            <span>{{ $entry['logs'] }} {{ \Illuminate\Support\Str::plural('log', $entry['logs']) }}</span>
            <span>{{ $entry['hours'] }} h</span>
          </div>
        </div>
      @empty
        <p class="text-xs text-muted-foreground">Pas encore d'activité enregistrée.</p>
      @endforelse
    </div>
  </x-filament::section>

  <x-filament::section heading="Projets du challenge">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      @forelse ($projectStats as $projectStat)
        <x-filament::card :heading="$projectStat['project']->name">
          <div class="space-y-3 text-sm">
            <p>{{ $projectStat['project']->description ?? 'Pas de description fournie.' }}</p>
            <div class="flex items-center justify-between">
              <span>{{ $projectStat['tasksCompleted'] }}/{{ $projectStat['tasksTotal'] }} tâches complétées</span>
              <x-filament::badge color="primary">{{ $projectStat['completion'] }}%</x-filament::badge>
            </div>
          </div>
        </x-filament::card>
      @empty
        <x-filament::card heading="Aucun projet">
          <p class="text-sm text-muted-foreground">Ajoutez un projet pour suivre les tâches liées au challenge.</p>
        </x-filament::card>
      @endforelse
    </div>
  </x-filament::section>
</div>
