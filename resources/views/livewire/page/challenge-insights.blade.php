@php
    use Illuminate\Support\Str;

    $targetDays = $overview['targetDays'] ?? 0;
    $daysElapsed = $overview['daysElapsed'] ?? null;
    $daysRemaining = $overview['daysRemaining'] ?? null;
    $completionAverage = $overview['completionAverage'] ?? 0;
    $statusLabel = match ($run->status) {
        'completed' => 'Terminé',
        'paused' => 'En pause',
        default => 'Actif',
    };
@endphp

<div class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6 lg:px-0">
  <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
    <div class="absolute inset-0">
      <div class="absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
      <div class="absolute -right-12 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
    </div>

    <div class="relative grid gap-10 p-8 lg:grid-cols-[1.25fr_0.75fr] lg:p-10">
      <div class="space-y-6">
        <div class="space-y-2">
          <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
            {{ $statusLabel }}
          </span>
          <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">Insights — {{ $run->title ?? 'Challenge 100DaysOfCode' }}</h1>
          <p class="max-w-2xl text-sm text-muted-foreground sm:text-base">
            {{ $run->owner->name }} pilote ce run. Voici la santé de l'équipe, les badges à portée et les prochains jalons sur {{ $targetDays }} jours.
          </p>
        </div>

        <div class="flex flex-wrap gap-3">
          <a
            wire:navigate
            href="{{ route('challenges.show', $run->id) }}"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
          >
            Voir le challenge
          </a>
          <a
            wire:navigate
            href="{{ route('daily-challenge') }}"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
          >
            Journal du jour
          </a>
          <a
            wire:navigate
            href="{{ route('challenges.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
          >
            Retour à la liste
          </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Participants</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $overview['totalParticipants'] }}</p>
            <p class="text-xs text-muted-foreground">Owner inclus</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Logs cumulés</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $overview['totalLogs'] }}</p>
            <p class="text-xs text-muted-foreground">{{ $overview['totalHours'] }} h codées</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Complétion moyenne</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $completionAverage }}%</p>
            <p class="text-xs text-muted-foreground">Sur un objectif de {{ $targetDays }} jours</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Progression</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">
              @if ($daysElapsed)
                Jour {{ $daysElapsed }}
              @else
                —
              @endif
            </p>
            <p class="text-xs text-muted-foreground">
              @if ($daysRemaining !== null)
                {{ $daysRemaining }} jours restants
              @else
                Date de départ inconnue
              @endif
            </p>
          </div>
        </div>
      </div>

      <div class="relative space-y-4 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl">
        <div>
          <p class="text-xs uppercase tracking-widest text-muted-foreground">Focus</p>
          <h2 class="mt-1 text-lg font-semibold text-foreground">Vue synthétique</h2>
        </div>
        <dl class="space-y-3 text-sm text-muted-foreground">
          <div class="flex items-center justify-between">
            <dt>Heures moyennes / log</dt>
            <dd class="font-medium text-foreground">{{ $overview['averageHours'] }} h</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>Heures / participant</dt>
            <dd class="font-medium text-foreground">{{ $overview['hoursPerParticipant'] }} h</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>Projets suivis</dt>
            <dd class="font-medium text-foreground">{{ $overview['projectsCount'] }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>Tâches complétées</dt>
            <dd class="font-medium text-foreground">{{ $overview['tasksCompleted'] }}/{{ $overview['tasksTotal'] }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>Commentaires</dt>
            <dd class="font-medium text-foreground">{{ $overview['commentsCount'] }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </section>

  <section class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
    <article class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-foreground">Participants & streaks</h2>
          <p class="text-xs text-muted-foreground">Classement basé sur le nombre de logs et la complétion.</p>
        </div>
      </div>
      <div class="mt-4 space-y-3">
        @forelse ($participantStats as $row)
          <div class="space-y-2 rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-foreground">{{ $row['user']->name }}</span>
                @if ($row['user']->id === $run->owner_id)
                  <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">Owner</span>
                @endif
              </div>
              <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                <span class="rounded-full bg-emerald-500/10 px-3 py-1 font-semibold text-emerald-600">Streak {{ $row['streak'] }}</span>
                <span>{{ $row['logs'] }} logs · {{ $row['hours'] }} h</span>
                <span>{{ $row['percent'] }}%</span>
              </div>
            </div>
            <p class="text-xs text-muted-foreground">
              Dernier log :
              @if ($row['lastLogAt'])
                {{ $row['lastLogAt']->translatedFormat('d/m/Y') }} · {{ $row['lastLogAt']->diffForHumans() }}
              @else
                aucun log
              @endif
            </p>
          </div>
        @empty
          <div class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-6 text-center text-sm text-muted-foreground">
            Pas encore de logs pour analyser la progression.
          </div>
        @endforelse
      </div>
    </article>

    <article class="space-y-6">
      <div class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">Jalons du défi</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          @foreach ($milestones as $milestone)
            <div class="space-y-2 rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
              <p class="font-semibold text-foreground">{{ $milestone['label'] }}</p>
              <p class="text-muted-foreground">Jour cible : {{ $milestone['targetDay'] }}</p>
              <p class="text-muted-foreground">
                Date estimée :
                @if ($milestone['expectedDate'])
                  {{ $milestone['expectedDate']->translatedFormat('d/m/Y') }}
                @else
                  —
                @endif
              </p>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $milestone['achieved'] ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600' }}">
                {{ $milestone['achieved'] ? 'Atteint' : 'À venir' }}
              </span>
            </div>
          @endforeach
        </div>
      </div>

      <div class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">Activité récente</h2>
        <div class="mt-3 space-y-2 text-sm">
          @forelse ($activity as $entry)
            <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-border/70 bg-background/80 px-4 py-2">
              <span>{{ $entry['date']->translatedFormat('d F Y') }}</span>
              <span class="flex gap-3 text-xs uppercase text-muted-foreground">
                <span>{{ $entry['logs'] }} {{ Str::plural('log', $entry['logs']) }}</span>
                <span>{{ $entry['hours'] }} h</span>
              </span>
            </div>
          @empty
            <div class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground">
              Pas encore d'activité configurée.
            </div>
          @endforelse
        </div>
      </div>
    </article>
  </section>

  <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-foreground">Projets du challenge</h2>
    <p class="text-xs text-muted-foreground">Suivi des tâches associées aux projets du run.</p>
    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      @forelse ($projectStats as $projectStat)
        <article class="flex h-full flex-col justify-between rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
          <div class="space-y-2">
            <h3 class="text-base font-semibold text-foreground">{{ $projectStat['project']->name }}</h3>
            <p class="text-muted-foreground">{{ $projectStat['project']->description ?? 'Pas de description fournie.' }}</p>
          </div>
          <div class="mt-3 flex items-center justify-between text-xs text-muted-foreground">
            <span>{{ $projectStat['tasksCompleted'] }}/{{ $projectStat['tasksTotal'] }} tâches complétées</span>
            <span class="rounded-full bg-primary/10 px-3 py-1 text-primary">{{ $projectStat['completion'] }}%</span>
          </div>
        </article>
      @empty
        <article class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-6 text-center text-sm text-muted-foreground">
          Aucun projet lié pour l'instant. Crée un projet depuis l'espace challenges.
        </article>
      @endforelse
    </div>
  </section>
</div>
