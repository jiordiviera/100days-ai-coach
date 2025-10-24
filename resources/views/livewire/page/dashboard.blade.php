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

<div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
    {{-- Hero Section --}}
    <section class="relative overflow-hidden rounded-3xl border border-primary/20 bg-gradient-to-br from-primary/10 via-primary/5 to-background shadow-xl">
        <div class="absolute -top-10 right-10 h-40 w-40 rounded-full bg-primary/20 blur-3xl"></div>
        <div class="absolute -bottom-10 left-10 h-40 w-40 rounded-full bg-primary/15 blur-3xl"></div>

        <div class="relative grid gap-8 p-8 lg:grid-cols-[1.3fr_0.7fr] lg:p-12">
            {{-- Left Column --}}
            <div class="space-y-8">
                <div class="space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-primary/70">{{ __('Dashboard') }}</p>
                    <h1 class="text-4xl font-bold text-foreground lg:text-5xl">
                        {{ __('Hello :name, ready for the next shipment?', ['name' => $firstName]) }}
                    </h1>
                    <p class="max-w-2xl text-base text-muted-foreground">
                        {{ __('Track your streak, projects, and unlocked badges. Every entry keeps the 100-day rhythm alive.') }}
                    </p>
                </div>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap gap-3">
                    <a wire:navigate href="{{ route('daily-challenge') }}"
                       class="group inline-flex items-center gap-2 rounded-full bg-primary px-7 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-primary/30">
                        {{ __('Daily log') }}
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('projects.index') }}"
                       class="inline-flex items-center gap-2 rounded-full border-2 border-primary/20 bg-background/50 px-6 py-3 text-sm font-semibold text-foreground backdrop-blur-sm transition-all hover:border-primary/40 hover:bg-primary/5">
                        {{ __('Manage my projects') }}
                    </a>
                    <a wire:navigate href="{{ route('challenges.index') }}"
                       class="inline-flex items-center gap-2 rounded-full border-2 border-primary/20 bg-background/50 px-6 py-3 text-sm font-semibold text-foreground backdrop-blur-sm transition-all hover:border-primary/40 hover:bg-primary/5">
                        {{ __('Challenges') }}
                    </a>
                </div>

                <livewire:partials.github-template-setup />

                {{-- Stats Grid --}}
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="group rounded-2xl border border-primary/20 bg-background/80 p-5 backdrop-blur-sm transition-all hover:border-primary/30 hover:shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Streak') }}</p>
                        <p class="mt-2 text-3xl font-bold text-foreground">
                            {{ $streakDays }}
                            <span class="text-lg font-normal text-muted-foreground">{{ trans_choice('day|days', $streakDays) }}</span>
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ $hasEntryToday ? __('Today\'s entry completed') : __('Add your log to keep the streak alive') }}
                        </p>
                    </div>
                    <div class="group rounded-2xl border border-primary/20 bg-background/80 p-5 backdrop-blur-sm transition-all hover:border-primary/30 hover:shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Projects') }}</p>
                        <p class="mt-2 text-3xl font-bold text-foreground">{{ $projectCount }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">{{ __('Aligned with your challenge') }}</p>
                    </div>
                    <div class="group rounded-2xl border border-primary/20 bg-background/80 p-5 backdrop-blur-sm transition-all hover:border-primary/30 hover:shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Tasks') }}</p>
                        <p class="mt-2 text-3xl font-bold text-foreground">
                            {{ $completedTaskCount }}<span class="text-xl text-muted-foreground">/{{ $taskCount }}</span>
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">{{ $taskCompletionDescription }}</p>
                    </div>
                </div>
            </div>

            {{-- Right Column - Challenge Card --}}
            <div class="relative">
                <div class="absolute -right-4 top-6 h-24 w-24 rounded-full bg-primary/15 blur-3xl"></div>
                <div class="relative flex h-full flex-col justify-between rounded-3xl border border-primary/20 bg-background/90 p-8 shadow-2xl backdrop-blur-sm">
                    <div class="space-y-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Active challenge') }}</p>
                        <h2 class="text-2xl font-bold text-foreground">{{ $challengeDaySummary }}</h2>
                        @if ($daysLeft !== null)
                            <p class="text-sm text-muted-foreground">
                                {{ trans_choice(':count day remaining to finish your run.|:count days remaining to finish your run.', $daysLeft, ['count' => $daysLeft]) }}
                            </p>
                        @else
                            <p class="text-sm text-muted-foreground">{{ __('Join or create a challenge to start your streak.') }}</p>
                        @endif
                    </div>

                    <div class="space-y-5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Progress') }}</span>
                            <span class="rounded-full bg-primary px-3 py-1 text-sm font-bold text-primary-foreground">{{ $challengePercent }}%</span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-primary/10">
                            <div class="h-full rounded-full bg-gradient-to-r from-primary to-primary/80 transition-all duration-500" style="width: {{ $challengePercent }}%"></div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a wire:navigate href="{{ route('daily-challenge') }}"
                               class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 px-5 py-2 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                                {{ __('Open the Daily Challenge') }}
                            </a>
                            @if ($active && ($active['run'] ?? null))
                                <a wire:navigate href="{{ route('challenges.show', $active['run']->id) }}"
                                   class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 px-5 py-2 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                                    {{ __('View the challenge') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Onboarding Checklist --}}
    @if (! ($onboardingChecklist['all_completed'] ?? true))
        <section class="rounded-3xl border-2 border-primary/30 bg-gradient-to-br from-primary/10 to-primary/5 p-8 shadow-lg">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="max-w-xl space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-primary">{{ __('Day 0') }}</p>
                    <h2 class="text-2xl font-bold text-foreground">{{ __('Kickoff checklist') }}</h2>
                    <p class="text-sm text-muted-foreground">{{ __('Complete these actions to launch your streak. You can revisit this checklist anytime.') }}</p>
                </div>

                <ul class="flex-1 space-y-3">
                    @foreach ($onboardingChecklist['items'] as $item)
                        <li class="flex flex-col justify-between gap-3 rounded-2xl border border-primary/20 bg-background/90 px-5 py-4 backdrop-blur-sm sm:flex-row sm:items-center">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $item['completed'] ? 'bg-primary text-primary-foreground' : 'border-2 border-primary/30 bg-background text-primary' }}">
                                    @if ($item['completed'])
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.429 7.428a1 1 0 01-1.414 0L3.296 9.57A1 1 0 014.71 8.154l2.433 2.433 6.722-6.724a1 1 0 011.415 0z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="10" cy="10" r="7"></circle>
                                        </svg>
                                    @endif
                                </span>
                                <div>
                                    <p class="font-semibold text-foreground">{{ $item['label'] }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $item['description'] }}</p>
                                </div>
                            </div>

                            @if ($item['completed'])
                                <span class="inline-flex items-center rounded-full bg-primary/20 px-4 py-1.5 text-xs font-bold text-primary">
                                    {{ __('Done') }}
                                </span>
                            @else
                                <a href="{{ $item['url'] }}" wire:navigate
                                   class="group inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2 text-xs font-semibold text-primary-foreground transition-all hover:scale-105 hover:shadow-lg">
                                    {{ __('Go to action') }}
                                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- No Active Challenge --}}
    @if (! $active)
        <section class="space-y-6 rounded-3xl border-2 border-dashed border-primary/30 bg-primary/5 p-12 text-center">
            <div class="mx-auto max-w-2xl space-y-3">
                <h2 class="text-3xl font-bold text-foreground">{{ __('No active challenge right now') }}</h2>
                <p class="text-base text-muted-foreground">
                    {{ __('Join a #100DaysOfCode run or launch your own to unlock the daily journal and badges.') }}
                </p>
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                <a wire:navigate href="{{ route('challenges.index') }}"
                   class="group inline-flex items-center gap-2 rounded-full bg-primary px-8 py-4 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-primary/30">
                    {{ __('Explore challenges') }}
                </a>
                <a wire:navigate href="{{ route('projects.index') }}"
                   class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 bg-background px-7 py-4 text-sm font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                    {{ __('Prepare my projects') }}
                </a>
            </div>
        </section>
    @endif

    {{-- Active Challenge Content --}}
    @if ($active)
        {{-- New Badges --}}
        @if (!empty($newBadges))
            <section class="rounded-3xl border-2 border-primary/30 bg-gradient-to-br from-primary/10 to-primary/5 p-8 shadow-lg">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-2">
                        <p class="text-lg font-bold text-foreground">🎉 {{ __('New badges unlocked') }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ __('Keep up the momentum! Here are the rewards since your last visit.') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($newBadges as $badge)
                            <span class="inline-flex items-center rounded-full border-2 border-primary/30 bg-background px-4 py-2 text-xs font-bold text-primary">
                                {{ $badge['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Daily Progress & Challenge Recap --}}
        <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            {{-- Daily Progress --}}
            <article class="rounded-3xl border border-primary/20 bg-background p-8 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-foreground">{{ __('Daily progress') }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ $hasEntryToday ? __('Entry completed for today.') : __('Add your entry to keep your streak alive.') }}
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full {{ $hasEntryToday ? 'bg-primary/20 text-primary' : 'bg-muted text-muted-foreground' }} px-4 py-2 text-xs font-bold">
                        {{ $hasEntryToday ? __('Daily journal completed') : __('Pending entry') }}
                    </span>
                </div>

                <div class="mt-8 space-y-6">
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-primary/70">
                            <span>{{ __('Run progress') }}</span>
                            <span class="text-primary">{{ $dailyCompletionPercent }}%</span>
                        </div>
                        <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-primary/10">
                            <div class="h-full rounded-full bg-gradient-to-r from-primary to-primary/80 transition-all duration-500" style="width: {{ $dailyCompletionPercent }}%"></div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-primary/20 bg-primary/5 p-5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Last entry') }}</p>
                            <p class="mt-2 text-sm font-semibold text-foreground">
                                @if ($lastEntryAt)
                                    {{ $lastEntryAt->translatedFormat('d/m/Y') }} · {{ $lastEntryAt->diffForHumans() }}
                                @else
                                    {{ __('No recent entry') }}
                                @endif
                            </p>
                        </div>
                        <div class="rounded-2xl border border-primary/20 bg-primary/5 p-5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Hours coded') }}</p>
                            <p class="mt-2 text-sm font-semibold text-foreground">{{ $hasEntryToday ? $hoursToday : '—' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm text-muted-foreground">
                        <span>{{ __('Streak:') }}
                            <span class="font-bold text-primary">{{ trans_choice(':count day|:count days', $streakDays, ['count' => $streakDays]) }}</span>
                        </span>
                        <span class="text-primary/30">•</span>
                        <span>
                            {{ trans_choice(':count total entry|:count total entries', $totalLogs, ['count' => $totalLogs]) }}
                        </span>
                    </div>

                    @unless ($hasEntryToday)
                        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border-2 border-primary/30 bg-primary/5 p-5">
                            <p class="text-sm font-medium text-foreground">{{ __("Add today's log so you don't break your streak.") }}</p>
                            <a wire:navigate href="{{ route('daily-challenge') }}"
                               class="group inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-xs font-semibold text-primary-foreground shadow-lg transition-all hover:scale-105 hover:shadow-xl">
                                {{ __("Complete today's entry") }}
                            </a>
                        </div>
                    @endunless

                    @if (! empty($dailyProgress['badges']))
                        <div class="flex flex-wrap gap-2">
                            @foreach ($dailyProgress['badges'] as $badge)
                                <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/5 px-4 py-2 text-xs font-semibold text-primary">
                                    {{ $badge['label'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>

            {{-- Challenge Recap --}}
            <article class="rounded-3xl border border-primary/20 bg-background p-8 shadow-lg">
                <h2 class="text-xl font-bold text-foreground">{{ __('Challenge recap') }}</h2>
                <p class="text-sm text-muted-foreground">{{ __('Summary of your current run.') }}</p>
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center justify-between rounded-2xl border border-primary/20 bg-primary/5 px-5 py-4">
                        <span class="text-sm text-muted-foreground">{{ __('Total progress') }}</span>
                        <span class="text-lg font-bold text-primary">{{ $challengePercent }}%</span>
                    </li>
                    <li class="flex items-center justify-between rounded-2xl border border-primary/20 bg-primary/5 px-5 py-4">
                        <span class="text-sm text-muted-foreground">{{ __('Current day') }}</span>
                        <span class="text-lg font-bold text-foreground">{{ $activeDayNumber ? min($targetDays, $activeDayNumber) . ' / ' . $targetDays : '—' }}</span>
                    </li>
                    <li class="flex items-center justify-between rounded-2xl border border-primary/20 bg-primary/5 px-5 py-4">
                        <span class="text-sm text-muted-foreground">{{ __('Days remaining') }}</span>
                        <span class="text-lg font-bold text-foreground">{{ $daysLeft !== null ? $daysLeft : '—' }}</span>
                    </li>
                    <li class="flex items-center justify-between rounded-2xl border border-primary/20 bg-primary/5 px-5 py-4">
                        <span class="text-sm text-muted-foreground">{{ __('Entries logged') }}</span>
                        <span class="text-lg font-bold text-foreground">{{ $totalLogs }}</span>
                    </li>
                </ul>

                @if (! empty($earnedBadges))
                    <div class="mt-8 space-y-4">
                        <h3 class="text-sm font-bold text-foreground">{{ __('Earned badges') }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($earnedBadges as $badge)
                                <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/5 px-4 py-2 text-xs font-semibold text-primary">
                                    {{ $badge['label'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>
        </section>

        {{-- Daily Tracker --}}
        <section class="rounded-3xl border border-primary/20 bg-background p-8 shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-xl font-bold text-foreground">{{ __('Daily tracker') }}</h2>
                    <p class="text-sm text-muted-foreground">{{ __('Complete your entry, track projects, and let the AI summarise your day.') }}</p>
                </div>
                <x-filament::link wire:navigate href="{{ route('daily-challenge') }}"
                    class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 px-5 py-2.5 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                    {{ __('Open full screen') }}
                </x-filament::link>
            </div>
            <div class="mt-6 overflow-auto rounded-2xl border border-primary/20">
                <livewire:page.daily-challenge />
            </div>
        </section>
    @endif

    {{-- Recent Projects --}}
    <section class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-foreground">{{ __('My recent projects') }}</h2>
                <p class="text-sm text-muted-foreground">{{ __('Latest projects touched during the challenge.') }}</p>
            </div>
            <a wire:navigate href="{{ route('projects.index') }}"
               class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 px-5 py-2.5 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                {{ __('All projects') }}
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($recentProjects as $project)
                <article class="group flex h-full flex-col justify-between rounded-3xl border border-primary/20 bg-background p-6 shadow-lg transition-all hover:border-primary/40 hover:shadow-xl">
                    <div class="space-y-3">
                        <h3 class="text-lg font-bold text-foreground">{{ $project->name }}</h3>
                        <p class="text-sm text-muted-foreground">{{ $project->description ?? __('No description provided.') }}</p>
                    </div>
                    <div class="mt-6 space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">
                                {{ trans_choice(':count task|:count tasks', $project->tasks->count(), ['count' => $project->tasks->count()]) }}
                            </span>
                            <span class="rounded-full bg-primary/20 px-3 py-1 text-xs font-bold text-primary">
                                {{ $project->tasks->where('is_completed', true)->count() }}/{{ $project->tasks->count() }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground">{{ __('Created on :date', ['date' => $project->created_at->format('d/m/Y')]) }}</p>
                        <a wire:navigate href="{{ route('projects.tasks.index', ['project' => $project->id]) }}"
                           class="group/link inline-flex items-center gap-2 text-sm font-semibold text-primary transition-all hover:gap-3">
                            {{ __('View details') }}
                            <svg class="h-4 w-4 transition-transform group-hover/link:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </article>
            @empty
                <article class="rounded-3xl border-2 border-dashed border-primary/30 bg-primary/5 p-8 text-center shadow-lg">
                    <h3 class="text-lg font-bold text-foreground">{{ __('No projects yet') }}</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ __('Create a project to track shipments and link your daily entries.') }}
                    </p>
                    <a wire:navigate href="{{ route('projects.index') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-primary/30">
                        {{ __('Create my first project') }}
                    </a>
                </article>
            @endforelse
        </div>
    </section>

    {{-- Recent Tasks --}}
    <section class="space-y-6 rounded-3xl border border-primary/20 bg-background p-8 shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-foreground">{{ __('My recent tasks') }}</h2>
                <p class="text-sm text-muted-foreground">{{ __('Latest tasks added or completed.') }}</p>
            </div>
            <a wire:navigate href="{{ route('projects.index') }}"
               class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 px-5 py-2.5 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                {{ __('Manage tasks') }}
            </a>
        </div>

        @forelse ($recentTasks as $task)
            <div class="group flex flex-wrap items-center justify-between gap-4 border-b border-primary/10 py-5 last:border-b-0">
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold text-foreground">{{ $task->title }}</h3>
                    <p class="text-xs text-muted-foreground">{{ __('Project: :name', ['name' => $task->project->name]) }}</p>
                </div>
                <span class="inline-flex items-center rounded-full {{ $task->is_completed ? 'bg-primary/20 text-primary' : 'bg-muted text-muted-foreground' }} px-4 py-2 text-xs font-bold">
                    {{ $task->is_completed ? __('Completed') : __('To do') }}
                </span>
            </div>
        @empty
            <p class="py-8 text-center text-sm text-muted-foreground">{{ __('No tasks recorded yet.') }}</p>
        @endforelse
    </section>
</div>