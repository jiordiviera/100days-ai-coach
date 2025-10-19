@php
    $user = auth()->user();
    $firstName = $user ? explode(' ', trim($user->name))[0] : 'Maker';
    $active = $stats['active'] ?? null;
    $projectCount = $stats['projectCount'] ?? 0;
    $taskCount = $stats['taskCount'] ?? 0;
    $completedTaskCount = $stats['completedTaskCount'] ?? 0;
    $taskCompletionRate = $taskCount > 0 ? round(($completedTaskCount / max($taskCount, 1)) * 100) : 0;
    $challengePercent = max(0, min(100, (int) ($active['myPercent'] ?? 0)));

    $dailyCompletionPercent = max(0, min(100, (int) ($dailyProgress['completionPercent'] ?? 0)));
    $streakDays = (int) ($dailyProgress['streak'] ?? 0);
    $hasEntryToday = (bool) ($dailyProgress['hasEntryToday'] ?? false);
    $hoursToday = $dailyProgress['hoursToday'];
    $totalLogs = (int) ($dailyProgress['totalLogs'] ?? 0);
    $lastEntryAt = $dailyProgress['lastEntryAt'] ?? null;

    $activeDayNumber = $active['dayNumber'] ?? null;
    $targetDays = $active['targetDays'] ?? 100;
    $daysLeft = $activeDayNumber ? max(0, $targetDays - $activeDayNumber) : null;
    $challengeDaySummary = $activeDayNumber
        ? __('Day :current of :total', ['current' => min($targetDays, $activeDayNumber), 'total' => $targetDays])
        : __('No active challenge');

    $taskCompletionDescription = $taskCount > 0
        ? __(':rate% of tasks completed', ['rate' => $taskCompletionRate])
        : __('No tasks created yet');
@endphp

<div class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6 lg:px-0">
    <section
        class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
        <div class="absolute -top-10 right-10 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
        <div class="absolute -bottom-16 left-4 h-36 w-36 rounded-full bg-secondary/20 blur-3xl"></div>

        <div class="relative grid gap-10 p-8 lg:grid-cols-[1.2fr_0.8fr] lg:p-10">
            <div class="space-y-6">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">{{ __('Dashboard') }}</p>
                    <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ __('Hello :name, ready for the next shipment?', ['name' => $firstName]) }}</h1>
                    <p class="max-w-xl text-sm text-muted-foreground sm:text-base">
                        {{ __('Track your streak, projects, and unlocked badges. Every entry keeps the 100-day rhythm alive.') }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        wire:navigate
                        href="{{ route('daily-challenge') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
                    >
                        {{ __('Daily log') }}
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z"
                                  clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a
                        wire:navigate
                        href="{{ route('projects.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                    >
                        {{ __('Manage my projects') }}
                    </a>
                    <a
                        wire:navigate
                        href="{{ route('challenges.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                    >
                        {{ __('Challenges') }}
                    </a>
                </div>

                <livewire:partials.github-template-setup />

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Streak') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ trans_choice(':count day|:count days', $streakDays, ['count' => $streakDays]) }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ $hasEntryToday ? __('Today’s entry completed') : __('Add your log to keep the streak alive') }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Projects') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-foreground">{{ $projectCount }}</p>
                        <p class="text-xs text-muted-foreground">{{ __('Aligned with your challenge') }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Tasks') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-foreground">{{ $completedTaskCount }}/{{ $taskCount }}</p>
                        <p class="text-xs text-muted-foreground">{{ $taskCompletionDescription }}</p>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -right-6 top-8 h-16 w-16 rounded-full bg-primary/20 blur-3xl"></div>
                <div
                    class="relative flex h-full flex-col justify-between rounded-3xl border border-border/60 bg-card/80 p-6 shadow-xl">
                    <div class="space-y-3">
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Active challenge') }}</p>
                        <h2 class="text-lg font-semibold text-foreground">{{ $challengeDaySummary }}</h2>
                        @if ($daysLeft !== null)
                            <p class="text-sm text-muted-foreground">
                                {{ trans_choice(':count day remaining to finish your run.|:count days remaining to finish your run.', $daysLeft, ['count' => $daysLeft]) }}
                            </p>
                        @else
                            <p class="text-sm text-muted-foreground">{{ __('Join or create a challenge to start your streak.') }}</p>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between text-xs uppercase tracking-widest text-muted-foreground">
                            <span>{{ __('Progress') }}</span>
                            <span
                                class="rounded-full bg-primary/10 px-2 py-0.5 text-primary">{{ $challengePercent }}%</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all"
                                 style="width: {{ $challengePercent }}%"></div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a
                                wire:navigate
                                href="{{ route('daily-challenge') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-4 py-2 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                            >
                                {{ __('Open the Daily Challenge') }}
                            </a>
                            @if ($active && ($active['run'] ?? null))
                                <a
                                    wire:navigate
                                    href="{{ route('challenges.show', $active['run']->id) }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-4 py-2 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                                >
                                    {{ __('View the challenge') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if (! ($onboardingChecklist['all_completed'] ?? true))
        <section class="rounded-3xl border border-primary bg-primary/20 p-6 shadow-sm">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="max-w-xl space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-foreground">{{ __('Day 0') }}</p>
                    <h2 class="text-lg font-semibold text-foreground/80">{{ __('Kickoff checklist') }}</h2>
                    <p class="text-sm text-foreground">{{ __('Complete these actions to launch your streak. You can revisit this checklist anytime.') }}</p>
                </div>

                <ul class="flex-1 space-y-3">
                    @foreach ($onboardingChecklist['items'] as $item)
                        <li class="flex flex-col justify-between gap-3 rounded-2xl border border-primary/60 bg-secondary/80 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center">
                            <div class="flex items-start gap-3">
                <span
                    class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full {{ $item['completed'] ? 'bg-green-700 text-green-50' : 'bg-white text-accent border border-secondary' }}">
                  @if ($item['completed'])
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd"
                            d="M16.704 5.29a1 1 0 010 1.415l-7.429 7.428a1 1 0 01-1.414 0L3.296 9.57A1 1 0 014.71 8.154l2.433 2.433 6.722-6.724a1 1 0 011.415 0z"
                            clip-rule="evenodd" />
                    </svg>
                    @else
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                      <circle cx="10" cy="10" r="7"></circle>
                    </svg>
                    @endif
                </span>
                                <div>
                                    <p class="font-semibold text-foreground">{{ $item['label'] }}</p>
                                    <p class="text-xs text-foreground/80">{{ $item['description'] }}</p>
                                </div>
                            </div>

                            @if ($item['completed'])
                                <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">
                                    {{ __('Done') }}
                                </span>
                            @else
                                <a
                                    href="{{ $item['url'] }}"
                                    wire:navigate
                                    class="inline-flex items-center gap-2 rounded-full border border-primary/80 px-4 py-1.5 text-xs font-semibold text-accent-foreground/80 transition hover:border-primary hover:text-accent-foreground"
                                >
                                    {{ __('Go to action') }}
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                              d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z"
                                              clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if (! $active)
        <section class="space-y-4 rounded-3xl border border-dashed border-primary/40 bg-primary/5 p-8 text-center">
            <h2 class="text-2xl font-semibold text-foreground">{{ __('No active challenge right now') }}</h2>
            <p class="text-sm text-muted-foreground">
                {{ __('Join a #100DaysOfCode run or launch your own to unlock the daily journal and badges.') }}
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a
                    wire:navigate
                    href="{{ route('challenges.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
                >
                    {{ __('Explore challenges') }}
                </a>
                <a
                    wire:navigate
                    href="{{ route('projects.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                >
                    {{ __('Prepare my projects') }}
                </a>
            </div>
        </section>
    @endif

    @if ($active)
        @if (!empty($newBadges))
            <section class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 text-emerald-900 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-background">{{ __('🎉 New badges unlocked') }}</p>
                        <p class="text-xs text-emerald-800">
                            {{ __('Keep up the momentum! Here are the rewards since your last visit.') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($newBadges as $badge)
                            <span
                                class="inline-flex items-center rounded-full bg-emerald-600/10 px-3 py-1 text-xs font-semibold text-background">{{ $badge['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <article class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">{{ __('Daily progress') }}</h2>
                        <p class="text-xs text-muted-foreground">
                            {{ $hasEntryToday ? __('Entry completed for today.') : __('Add your entry to keep your streak alive.') }}
                        </p>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full {{ $hasEntryToday ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600' }} px-3 py-1 text-xs font-semibold">
                        {{ $hasEntryToday ? __('Daily journal completed') : __('Pending entry') }}
          </span>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <div
                            class="flex items-center justify-between text-xs uppercase tracking-widest text-muted-foreground">
                            <span>{{ __('Run progress') }}</span>
                            <span>{{ $dailyCompletionPercent }}%</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all"
                                 style="width: {{ $dailyCompletionPercent }}%"></div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-border/70 bg-background/80 p-4">
                            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Last entry') }}</p>
                            <p class="mt-1 text-sm font-semibold text-foreground">
                                @if ($lastEntryAt)
                                    {{ $lastEntryAt->translatedFormat('d/m/Y') }} · {{ $lastEntryAt->diffForHumans() }}
                                @else
                                    {{ __('No recent entry') }}
                                @endif
                            </p>
                        </div>
                        <div class="rounded-2xl border border-border/70 bg-background/80 p-4">
                            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Hours coded') }}</p>
                            <p class="mt-1 text-sm font-semibold text-foreground">{{ $hasEntryToday ? $hoursToday : '—' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 text-xs text-muted-foreground">
                        <span>{{ __('Streak:') }}
                            <span class="font-semibold text-foreground">
                                {{ trans_choice(':count day|:count days', $streakDays, ['count' => $streakDays]) }}
                            </span>
                        </span>
                        <span>•</span>
                        <span>
                            {{ trans_choice(':count total entry|:count total entries', $totalLogs, ['count' => $totalLogs]) }}
                        </span>
                    </div>

                    @unless ($hasEntryToday)
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-dashed border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                            <p>{{ __('Add today’s log so you don’t break your streak.') }}</p>
                            <a
                                wire:navigate
                                href="{{ route('daily-challenge') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-full bg-amber-600 px-4 py-2 text-xs font-semibold text-white shadow hover:brightness-105"
                            >
                                {{ __('Complete today’s entry') }}
                            </a>
                        </div>
                    @endunless

                    @if (! empty($dailyProgress['badges']))
                        <div class="flex flex-wrap gap-2">
                            @foreach ($dailyProgress['badges'] as $badge)
                                <span
                                    class="inline-flex items-center rounded-full border border-border/60 bg-background/90 px-3 py-1 text-xs font-semibold text-muted-foreground">
                  {{ $badge['label'] }}
                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>

            <article class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-foreground">{{ __('Challenge recap') }}</h2>
                <p class="text-xs text-muted-foreground">{{ __('Summary of your current run.') }}</p>
                <ul class="mt-6 space-y-4 text-sm text-muted-foreground">
                    <li class="flex items-center justify-between rounded-2xl border border-border/70 bg-background/70 px-4 py-3">
                        <span>{{ __('Total progress') }}</span>
                        <span class="font-semibold text-foreground">{{ $challengePercent }}%</span>
                    </li>
                    <li class="flex items-center justify-between rounded-2xl border border-border/70 bg-background/70 px-4 py-3">
                        <span>{{ __('Current day') }}</span>
                        <span
                            class="font-semibold text-foreground">{{ $activeDayNumber ? min($targetDays, $activeDayNumber) . ' / ' . $targetDays : '—' }}</span>
                    </li>
                    <li class="flex items-center justify-between rounded-2xl border border-border/70 bg-background/70 px-4 py-3">
                        <span>{{ __('Days remaining') }}</span>
                        <span class="font-semibold text-foreground">{{ $daysLeft !== null ? $daysLeft : '—' }}</span>
                    </li>
                    <li class="flex items-center justify-between rounded-2xl border border-border/70 bg-background/70 px-4 py-3">
                        <span>{{ __('Entries logged') }}</span>
                        <span class="font-semibold text-foreground">{{ $totalLogs }}</span>
                    </li>
                </ul>

                @if (! empty($earnedBadges))
                    <div class="mt-6 space-y-3">
                        <h3 class="text-sm font-semibold text-foreground">{{ __('Earned badges') }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($earnedBadges as $badge)
                                <span
                                    class="inline-flex items-center rounded-full border border-border/70 bg-background/80 px-3 py-1 text-xs font-semibold">
                  {{ $badge['label'] }}
                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>
        </section>

        <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold text-foreground">{{ __('Daily tracker') }}</h2>
                    <p class="text-xs text-muted-foreground">{{ __('Complete your entry, track projects, and let the AI summarise your day.') }}</p>
                </div>
                <x-filament::link
                    wire:navigate
                    href="{{ route('daily-challenge') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                >
                    {{ __('Open full screen') }}
                </x-filament::link>
            </div>
            <div class="mt-6 overflow-auto rounded-2xl border border-border/60">
                <livewire:page.daily-challenge />
            </div>
        </section>
    @endif

    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-foreground">{{ __('My recent projects') }}</h2>
                <p class="text-xs text-muted-foreground">{{ __('Latest projects touched during the challenge.') }}</p>
            </div>
            <a
                wire:navigate
                href="{{ route('projects.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
                {{ __('All projects') }}
            </a>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($recentProjects as $project)
                <article
                    class="flex h-full flex-col justify-between rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm transition hover:border-primary/50 hover:shadow-md">
                    <div class="space-y-2">
                        <h3 class="text-base font-semibold text-foreground">{{ $project->name }}</h3>
                        <p class="text-sm text-muted-foreground">{{ $project->description ?? __('No description provided.') }}</p>
                    </div>
                    <div class="mt-4 space-y-3 text-xs text-muted-foreground">
                        <div class="flex items-center justify-between">
                            <span>
                                {{ trans_choice(':count task|:count tasks', $project->tasks->count(), ['count' => $project->tasks->count()]) }}
                            </span>
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-primary">
                {{ $project->tasks->where('is_completed', true)->count() }}/{{ $project->tasks->count() }} {{ __('completed') }}
              </span>
                        </div>
                        <p>{{ __('Created on :date', ['date' => $project->created_at->format('d/m/Y')]) }}</p>
                        <a
                            wire:navigate
                            href="{{ route('projects.tasks.index', ['project' => $project->id]) }}"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-primary hover:underline"
                        >
                            {{ __('View details') }}
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z"
                                      clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </article>
            @empty
                <article class="rounded-3xl border border-dashed border-border/70 bg-card/80 p-6 text-center shadow-sm">
                    <h3 class="text-base font-semibold text-foreground">{{ __('No projects yet') }}</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ __('Create a project to track shipments and link your daily entries.') }}
                    </p>
                    <a
                        wire:navigate
                        href="{{ route('projects.index') }}"
                        class="mt-4 inline-flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-2 text-xs font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
                    >
                        {{ __('Create my first project') }}
                    </a>
                </article>
            @endforelse
        </div>
    </section>

    <section class="space-y-4 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-foreground">{{ __('My recent tasks') }}</h2>
                <p class="text-xs text-muted-foreground">{{ __('Latest tasks added or completed.') }}</p>
            </div>
            <a
                wire:navigate
                href="{{ route('projects.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
                {{ __('Manage tasks') }}
            </a>
        </div>

        @forelse ($recentTasks as $task)
            <div
                class="flex flex-wrap items-center justify-between gap-4 border-b border-border/50 py-4 last:border-b-0">
                <div>
                    <h3 class="text-sm font-semibold text-foreground">{{ $task->title }}</h3>
                    <p class="text-xs text-muted-foreground">{{ __('Project: :name', ['name' => $task->project->name]) }}</p>
                </div>
                <span
                    class="inline-flex items-center rounded-full {{ $task->is_completed ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600' }} px-3 py-1 text-xs font-semibold">
          {{ $task->is_completed ? __('Completed') : __('To do') }}
        </span>
            </div>
        @empty
            <p class="py-4 text-center text-sm text-muted-foreground">{{ __('No tasks recorded yet.') }}</p>
        @endforelse
    </section>
</div>
