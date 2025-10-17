@php
    $user = auth()->user();
    $ownedCount = $owned->count();
    $joinedCount = $joined->count();
    $activeRun = $activeRun ?? null;
    $activeRunOwned = $activeRun && $activeRun->owner_id === $user->id;

    $heroBadge = $activeRun
        ? ($activeRunOwned ? __('Owner run') : __('Participant run'))
        : __('Ready for a new run?');

    $heroTitle = $activeRun
        ? ($activeRun->title ?: __('100DaysOfCode challenge'))
        : __('Start your #100DaysOfCode challenge');

    $activeDayNumber = $activeRun
        ? (int) (\Illuminate\Support\Carbon::parse($activeRun->start_date)->startOfDay()->diffInDays(now()->startOfDay()) + 1)
        : null;

    $heroDescription = $activeRun
        ? __('Day :day of :total. Document every shipment to maintain your streak.', [
            'day' => $activeDayNumber,
            'total' => $activeRun->target_days,
        ])
        : __('Plan your next run, invite your crew, and use the intelligent journal to track daily shipments.');

    $ctaPrimary = $activeRun
        ? ['label' => __('Open the challenge'), 'route' => route('challenges.show', $activeRun)]
        : ['label' => __('Create a challenge'), 'route' => '#challenge-create'];

    $ctaSecondary = $activeRun
        ? ['label' => __('Daily log'), 'route' => route('daily-challenge')]
        : ['label' => __('View joined challenges'), 'route' => '#joined-list'];

    $ctaSecondarySupportsNavigate = ! str_starts_with($ctaSecondary['route'], '#');

    $activeRestrictionMessage = $hasActiveChallenge
        ? ($activeRunOwned
            ? __('You already have an active challenge. Finish it before launching another one.')
            : __('You already participate in an active challenge. Finish it before starting a new one.'))
        : null;
@endphp

<div class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6 lg:px-0">
  @if (session()->has('message'))
    <div class="rounded-2xl border border-primary/30 bg-primary/10 px-4 py-3 text-sm text-primary shadow-sm">
      {{ session('message') }}
    </div>
  @endif

  <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
    <div class="absolute inset-0">
      <div class="absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
      <div class="absolute -right-10 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
    </div>

    <div class="relative grid gap-10 p-8 lg:grid-cols-[1.2fr_0.8fr] lg:p-10">
      <div class="space-y-6">
        <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
          {{ $heroBadge }}
        </span>

        <div class="space-y-3">
          <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ $heroTitle }}</h1>
          <p class="max-w-xl text-sm text-muted-foreground sm:text-base">{{ $heroDescription }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
          <a
            wire:navigate
            href="{{ $ctaPrimary['route'] }}"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
          >
            {{ $ctaPrimary['label'] }}
          </a>

          @if ($ctaSecondarySupportsNavigate)
            <a
              wire:navigate
              href="{{ $ctaSecondary['route'] }}"
              class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
              {{ $ctaSecondary['label'] }}
            </a>
          @else
            <a
              href="{{ $ctaSecondary['route'] }}"
              class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
              {{ $ctaSecondary['label'] }}
            </a>
          @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Created challenges') }}</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $ownedCount }}</p>
            <p class="text-xs text-muted-foreground">{{ \Illuminate\Support\Str::plural(__('challenge'), $ownedCount) }} {{ __('owned') }}</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Joined challenges') }}</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $joinedCount }}</p>
            <p class="text-xs text-muted-foreground">{{ __('Runs where you are a participant') }}</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Streak mode') }}</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $activeRun ? __('Active') : __('Ready to launch') }}</p>
            <p class="text-xs text-muted-foreground">{{ __('Plan your next challenge now.') }}</p>
          </div>
        </div>
      </div>

      <div class="relative">
        <div class="absolute -right-6 top-6 h-16 w-16 rounded-full bg-primary/20 blur-3xl"></div>
        <div class="relative h-full rounded-3xl border border-border/60 bg-card/85 p-6 shadow-xl" id="challenge-create">
          <div class="space-y-3">
            <h2 class="text-lg font-semibold text-foreground">{{ __('Create a challenge') }}</h2>
            <p class="text-xs text-muted-foreground">
              {{ __('Pick a start date, duration, and decide if your run is public. You can invite teammates afterwards.') }}
            </p>
          </div>

          <form wire:submit.prevent="create" class="mt-4 space-y-4">
            {{ $this->form }}

            @if ($activeRestrictionMessage)
              <p class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-900">
                {{ $activeRestrictionMessage }}
              </p>
            @endif

            <div class="flex justify-end">
              <x-filament::button type="submit" :disabled="$hasActiveChallenge">
                {{ __('Launch the challenge') }}
              </x-filament::button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <section class="grid gap-6 lg:grid-cols-2">
    <article class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-foreground">{{ __('My challenges') }}</h2>
          <p class="text-xs text-muted-foreground">{{ __('All runs you own.') }}</p>
        </div>
        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $ownedCount }}</span>
      </div>

      <div class="mt-4 space-y-3">
        @forelse ($owned as $run)
          @php($isActive = $activeRun && $activeRun->id === $run->id)
          <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border {{ $isActive ? 'border-primary/60' : 'border-border/70' }} bg-background/80 px-4 py-3 text-sm">
            <div>
              <p class="font-semibold text-foreground">{{ $run->title ?? __('100DaysOfCode challenge') }}</p>
              <p class="text-xs text-muted-foreground">
                {{ __('Started on :date · :days days · :status', [
                    'date' => $run->start_date->format('d/m/Y'),
                    'days' => $run->target_days,
                    'status' => strtoupper($run->status),
                ]) }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              @if ($isActive)
                <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">{{ __('Active run') }}</span>
              @elseif ($run->status === 'active')
                <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">{{ __('Active') }}</span>
              @endif
              <a
                wire:navigate
                href="{{ route('challenges.show', $run) }}"
                class="inline-flex items-center gap-2 rounded-full border {{ $isActive ? 'border-primary/50 text-primary' : 'border-border/70 text-muted-foreground' }} px-3 py-1.5 text-xs font-semibold transition hover:border-primary/50 hover:text-primary"
              >
                {{ $isActive ? __('Continue') : __('Open') }}
              </a>
            </div>
          </div>
        @empty
          <div class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-6 text-center text-sm text-muted-foreground">
            {{ __('No challenge created yet. Start your first run to unlock the daily journal.') }}
          </div>
        @endforelse
      </div>
    </article>

    <article class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm" id="joined-list">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-foreground">{{ __('Joined challenges') }}</h2>
          <p class="text-xs text-muted-foreground">{{ __('Runs you joined via invitation.') }}</p>
        </div>
        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $joinedCount }}</span>
      </div>

      <div class="mt-4 space-y-3">
        @forelse ($joined as $run)
          @php($isActive = $activeRun && $activeRun->id === $run->id)
          <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border {{ $isActive ? 'border-primary/60' : 'border-border/70' }} bg-background/80 px-4 py-3 text-sm">
            <div>
              <p class="font-semibold text-foreground">{{ $run->title ?? __('100DaysOfCode challenge') }}</p>
              <p class="text-xs text-muted-foreground">
                {{ __('Host: :name · Started on :date · :days days', [
                    'name' => $run->owner?->name ?? __('Unknown'),
                    'date' => $run->start_date->format('d/m/Y'),
                    'days' => $run->target_days,
                ]) }}
              </p>
            </div>
            <a
              wire:navigate
              href="{{ route('challenges.show', $run) }}"
              class="inline-flex items-center gap-2 rounded-full border {{ $isActive ? 'border-primary/50 text-primary' : 'border-border/70 text-muted-foreground' }} px-3 py-1.5 text-xs font-semibold transition hover:border-primary/50 hover:text-primary"
            >
              {{ $isActive ? __('Continue') : __('Join the run') }}
            </a>
          </div>
        @empty
          <div class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-6 text-center text-sm text-muted-foreground">
            {{ __('No joined challenge yet. Once you accept an invitation, it will appear here.') }}
          </div>
        @endforelse
      </div>
    </article>
  </section>
</div>
