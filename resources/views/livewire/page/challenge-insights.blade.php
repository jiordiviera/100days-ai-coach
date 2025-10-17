@php
    use Illuminate\Support\Str;

    $targetDays = $overview['targetDays'] ?? 0;
    $daysElapsed = $overview['daysElapsed'] ?? null;
    $daysRemaining = $overview['daysRemaining'] ?? null;
    $completionAverage = $overview['completionAverage'] ?? 0;
    $statusLabel = match ($run->status) {
        'completed' => __('Completed'),
        'paused' => __('Paused'),
        default => __('Active'),
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
          <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ __('Insights — :title', ['title' => $run->title ?? __('100DaysOfCode challenge')]) }}</h1>
          <p class="max-w-2xl text-sm text-muted-foreground sm:text-base">
            {{ __(':owner leads this run. Monitor team health, upcoming badges, and milestones across :days days.', [
                'owner' => $run->owner->name,
                'days' => $targetDays,
            ]) }}
          </p>
        </div>

        <div class="flex flex-wrap gap-3">
          <a
            wire:navigate
            href="{{ route('challenges.show', $run->id) }}"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
          >
            {{ __('View the challenge') }}
          </a>
          <a
            wire:navigate
            href="{{ route('daily-challenge') }}"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
          >
            {{ __('Daily log') }}
          </a>
          <a
            wire:navigate
            href="{{ route('challenges.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
          >
            {{ __('Back to challenges') }}
          </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Participants') }}</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $overview['totalParticipants'] }}</p>
            <p class="text-xs text-muted-foreground">{{ __('Including owner') }}</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Total logs') }}</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $overview['totalLogs'] }}</p>
            <p class="text-xs text-muted-foreground">{{ __(':hours hours coded', ['hours' => $overview['totalHours']]) }}</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Average completion') }}</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $completionAverage }}%</p>
            <p class="text-xs text-muted-foreground">{{ __('Based on a :days-day target', ['days' => $targetDays]) }}</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Progress') }}</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">
              @if ($daysElapsed)
                {{ __('Day :day', ['day' => $daysElapsed]) }}
              @else
                —
              @endif
            </p>
            <p class="text-xs text-muted-foreground">
              @if ($daysRemaining !== null)
                {{ trans_choice(':count day remaining|:count days remaining', $daysRemaining, ['count' => $daysRemaining]) }}
              @else
                {{ __('Start date unknown') }}
              @endif
            </p>
          </div>
        </div>
      </div>

      <div class="relative space-y-4 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl">
        <div>
          <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Focus') }}</p>
          <h2 class="mt-1 text-lg font-semibold text-foreground">{{ __('Snapshot view') }}</h2>
        </div>
        <dl class="space-y-3 text-sm text-muted-foreground">
          <div class="flex items-center justify-between">
            <dt>{{ __('Average hours per log') }}</dt>
            <dd class="font-medium text-foreground">{{ $overview['averageHours'] }} {{ __('h') }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>{{ __('Hours per participant') }}</dt>
            <dd class="font-medium text-foreground">{{ $overview['hoursPerParticipant'] }} {{ __('h') }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>{{ __('Projects tracked') }}</dt>
            <dd class="font-medium text-foreground">{{ $overview['projectsCount'] }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>{{ __('Completed tasks') }}</dt>
            <dd class="font-medium text-foreground">{{ $overview['tasksCompleted'] }}/{{ $overview['tasksTotal'] }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>{{ __('Comments') }}</dt>
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
          <h2 class="text-lg font-semibold text-foreground">{{ __('Participants & streaks') }}</h2>
          <p class="text-xs text-muted-foreground">{{ __('Ranking based on logs and completion rate.') }}</p>
        </div>
      </div>
      <div class="mt-4 space-y-3">
        @forelse ($participantStats as $row)
          <div class="space-y-2 rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-foreground">{{ $row['user']->name }}</span>
                @if ($row['user']->id === $run->owner_id)
                  <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">{{ __('Owner') }}</span>
                @endif
              </div>
              <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-3 py-1 font-semibold text-amber-600">
                  <span class="text-base leading-none">🔥</span>
                  <span>{{ __('Streak :count', ['count' => $row['streak']]) }}</span>
                </span>
                <span>{{ __(':logs logs · :hours h', ['logs' => $row['logs'], 'hours' => $row['hours']]) }}</span>
                <span>{{ $row['percent'] }}%</span>
              </div>
            </div>
            <p class="text-xs text-muted-foreground">
              {{ __('Last log:') }}
              @if ($row['lastLogAt'])
                {{ $row['lastLogAt']->translatedFormat('d/m/Y') }} · {{ $row['lastLogAt']->diffForHumans() }}
              @else
                {{ __('No log yet') }}
              @endif
            </p>
          </div>
        @empty
          <div class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-6 text-center text-sm text-muted-foreground">
            {{ __('No logs yet to analyse progress.') }}
          </div>
        @endforelse
      </div>
    </article>

    <article class="space-y-6">
      <div class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">{{ __('Challenge milestones') }}</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          @foreach ($milestones as $milestone)
            <div class="space-y-2 rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
              <p class="font-semibold text-foreground">{{ $milestone['label'] }}</p>
              <p class="text-muted-foreground">{{ __('Target day: :day', ['day' => $milestone['targetDay']]) }}</p>
              <p class="text-muted-foreground">
                {{ __('Estimated date:') }}
                @if ($milestone['expectedDate'])
                  {{ $milestone['expectedDate']->translatedFormat('d/m/Y') }}
                @else
                  —
                @endif
              </p>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $milestone['achieved'] ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600' }}">
                {{ $milestone['achieved'] ? __('Achieved') : __('Upcoming') }}
              </span>
            </div>
          @endforeach
        </div>
      </div>

      <div class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">{{ __('Recent activity') }}</h2>
        <div class="mt-3 space-y-2 text-sm">
          @forelse ($activity as $entry)
            <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-border/70 bg-background/80 px-4 py-2">
              <span>{{ $entry['date']->translatedFormat('d F Y') }}</span>
              <span class="flex gap-3 text-xs uppercase text-muted-foreground">
                <span>{{ trans_choice(':count log|:count logs', $entry['logs'], ['count' => $entry['logs']]) }}</span>
                <span>{{ __(':hours h', ['hours' => $entry['hours']]) }}</span>
              </span>
            </div>
          @empty
            <div class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground">
              {{ __('No activity recorded yet.') }}
            </div>
          @endforelse
        </div>
      </div>
    </article>
  </section>

  <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-foreground">{{ __('Challenge projects') }}</h2>
    <p class="text-xs text-muted-foreground">{{ __('Track tasks linked to the run’s projects.') }}</p>
    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      @forelse ($projectStats as $projectStat)
        <article class="flex h-full flex-col justify-between rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
          <div class="space-y-2">
            <h3 class="text-base font-semibold text-foreground">{{ $projectStat['project']->name }}</h3>
            <p class="text-muted-foreground">{{ $projectStat['project']->description ?? __('No description provided.') }}</p>
          </div>
          <div class="mt-3 flex items-center justify-between text-xs text-muted-foreground">
            <span>{{ __(':done/:total tasks completed', ['done' => $projectStat['tasksCompleted'], 'total' => $projectStat['tasksTotal']]) }}</span>
            <span class="rounded-full bg-primary/10 px-3 py-1 text-primary">{{ $projectStat['completion'] }}%</span>
          </div>
        </article>
      @empty
        <article class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-6 text-center text-sm text-muted-foreground">
          {{ __('No linked projects yet. Create one from the challenges area.') }}
        </article>
      @endforelse
    </div>
  </section>
</div>
