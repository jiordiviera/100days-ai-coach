@php
    use App\Models\ChallengeRun;
    use Illuminate\Support\Str;

    /**
     * @var ChallengeRun $run
     */
    $isOwner = auth()->id() === $run->owner_id;
    $startDateFormatted = optional($run->start_date)?->format('d/m/Y');
    $targetDays = max(1, (int) $run->target_days);
    $statusLabel = match ($run->status) {
        'completed' => __('Completed'),
        'paused' => __('Paused'),
        default => __('Active'),
    };
    $publicJoinCode = $run->is_public && $run->public_join_code;
    $calendarColumns = 10;
    $calendarChunks = array_chunk(range(1, $targetDays), $calendarColumns);
    $publicUrl = $run->is_public && $run->public_slug ? route('public.challenge', ['slug' => $run->public_slug]) : null;
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
            <div class="absolute -right-12 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
        </div>

        <div class="relative grid gap-10 p-8 lg:grid-cols-[1.25fr_0.75fr] lg:p-10">
            <div class="space-y-6">
                <div class="space-y-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
                        {{ $statusLabel }}
                    </span>
                    <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ $run->title ?? __('100DaysOfCode challenge') }}</h1>
                    <p class="max-w-xl text-sm text-muted-foreground sm:text-base">
                        @if ($activeDayNumber)
                            {{ __('Day :day of :total.', ['day' => $activeDayNumber, 'total' => $targetDays]) }}
                        @endif
                        {{ __('Hosted by :name — log every shipment to keep your streak alive.', ['name' => $run->owner->name]) }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        wire:navigate
                        href="{{ route('daily-challenge') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
                    >
                        {{ __('Daily log') }}
                    </a>
                    <a
                        wire:navigate
                        href="{{ route('challenges.insights', $run->id) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                    >
                        {{ __('Insights') }}
                    </a>
                    <a
                        wire:navigate
                        href="{{ route('challenges.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                    >
                        {{ __('Back to challenges') }}
                    </a>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Participants') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-foreground">{{ $participantsCount }}</p>
                        <p class="text-xs text-muted-foreground">{{ __('Including you and :name', ['name' => $run->owner->name]) }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Global progress') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-foreground">{{ $globalPercent }}%</p>
                        <p class="text-xs text-muted-foreground">{{ __('Total logs versus collective target') }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/70 bg-gradient-to-br from-amber-500/10 via-orange-500/10 to-rose-400/10 p-4">
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Your streak') }}</p>
                        <div class="mt-3 flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-rose-400 text-3xl shadow-inner shadow-amber-500/40">
                                🔥
                            </div>
                            <div>
                                <p class="text-3xl font-semibold text-foreground">{{ $myStreak }}</p>
                                <p class="text-xs uppercase tracking-[0.28em] text-muted-foreground">{{ __('days in a row') }}</p>
                            </div>
                        </div>
                        @php($streakFlames = min($myStreak, 7))
                        <div class="mt-4 flex items-center gap-1">
                            @for ($i = 0; $i < $streakFlames; $i++)
                                <span class="text-lg leading-none">🔥</span>
                            @endfor
                            @if ($myStreak > 7)
                                <span class="rounded-full bg-amber-500/15 px-2 py-0.5 text-[11px] font-semibold text-amber-600">
                                    +{{ $myStreak - 7 }}
                                </span>
                            @endif
                        </div>
                        <p class="mt-3 text-xs text-muted-foreground">{{ __('Log today to keep the fire alive.') }}</p>
                    </div>
                    <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Days remaining') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-foreground">{{ $daysRemaining ?? 0 }}</p>
                        <p class="text-xs text-muted-foreground">{{ __('Stay steady until day :total.', ['total' => $targetDays]) }}</p>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -right-6 top-6 h-16 w-16 rounded-full bg-primary/20 blur-3xl"></div>
                <div class="relative h-full rounded-3xl border border-border/60 bg-card/85 p-6 shadow-xl">
                    <dl class="space-y-3 text-sm text-muted-foreground">
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Start date') }}</dt>
                            <dd class="font-medium text-foreground">{{ $startDateFormatted ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Target') }}</dt>
                            <dd class="font-medium text-foreground">{{ $targetDays }} {{ __('days') }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Visibility') }}</dt>
                            <dd class="font-medium text-foreground">{{ $run->is_public ? __('Public') : __('Private') }}</dd>
                        </div>
                    </dl>

                    @if ($isOwner)
                        <div class="mt-4 space-y-3">
                            <button
                                type="button"
                                wire:click="toggleVisibility"
                                wire:loading.attr="disabled"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-full {{ $run->is_public ? 'bg-amber-500/10 text-amber-600 hover:bg-amber-500/20' : 'bg-primary text-primary-foreground hover:bg-primary/90' }} px-4 py-2 text-xs font-semibold transition"
                            >
                                {{ $run->is_public ? __('Disable public page') : __('Enable public page') }}
                            </button>
                            @if ($publicUrl)
                                <a
                                    href="{{ $publicUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                                >
                                    {{ __('View public page') }}
                                </a>
                            @endif
                        </div>
                    @endif

                    @if (! $isOwner)
                        <div class="mt-4 rounded-2xl border border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground">
                            {{ __('You are a participant. Use the Daily Challenge to log your progress.') }}
                        </div>
                    @endif

                    @if ($publicJoinCode)
                        <div class="mt-4 space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">{{ __('Public join code') }}</p>
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $run->public_join_code }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-foreground">{{ __('Participants') }}</h2>
                    <p class="text-xs text-muted-foreground">{{ __('Individual progress and streaks.') }}</p>
                </div>
            </div>

            <div class="mt-4 space-y-4">
                @foreach ($progress as $item)
                    @php($participantLink = $run->participantLinks->firstWhere('user_id', $item['user']->id))
                    <div class="space-y-2 rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-foreground">{{ $item['user']->name }}</span>
                                @if ($item['user']->id === $run->owner_id)
                                    <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">{{ __('Owner') }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-3 py-1 font-semibold text-amber-600">
                                    <span class="text-base leading-none">🔥</span>
                                    <span>{{ __('Streak :count', ['count' => $item['streak']]) }}</span>
                                </span>
                                <span>{{ __(':done / :total (:percent%)', ['done' => $item['done'], 'total' => $targetDays, 'percent' => $item['percent']]) }}</span>
                            </div>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $item['percent'] }}%"></div>
                        </div>
                        @if ($isOwner && $participantLink && $item['user']->id !== $run->owner_id)
                            <div class="flex justify-end">
                                <x-filament::button
                                    color="danger"
                                    size="sm"
                                    wire:confirm="{{ __('Remove this participant from the challenge?') }}"
                                    wire:click="removeParticipant('{{ $participantLink->getKey() }}')"
                                >
                                    {{ __('Remove') }}
                                </x-filament::button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>

        <article class="space-y-6">
            @if ($isOwner)
                <div class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-foreground">{{ __('Invite participants') }}</h2>
                    <p class="text-xs text-muted-foreground">{{ __('One person per active run. Existing participants must finish their challenge before joining.') }}</p>

                    <form wire:submit.prevent="sendInvite" class="mt-4 flex flex-wrap items-center gap-3">
                        <div class="min-w-60 grow">
                            {{ $this->form }}
                        </div>
                        <x-filament::button type="submit">{{ __('Send invitation') }}</x-filament::button>
                    </form>

                    @if ($lastInviteLink)
                        <p class="mt-3 text-xs text-muted-foreground">
                            {{ __('Generated link:') }}
                            <x-filament::link class="font-mono text-primary" href="{{ $lastInviteLink }}">{{ Str::limit($lastInviteLink, 40) }}</x-filament::link>
                        </p>
                    @endif

                    <div class="mt-4">
                        <h3 class="text-sm font-semibold text-foreground">{{ __('Pending invitations') }}</h3>
                        <ul class="mt-2 space-y-2 text-sm">
                            @forelse ($pendingInvites as $inv)
                                <li class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-border/60 bg-background/80 px-4 py-2">
                                    <span>{{ $inv->email }}</span>
                                    <span class="flex items-center gap-2">
                                        <x-filament::button size="sm" wire:click="copyLink('{{ route('challenges.accept', $inv->token) }}')">
                                            {{ __('Copy') }}
                                        </x-filament::button>
                                        <x-filament::button
                                            size="sm"
                                            color="danger"
                                            wire:confirm="{{ __('Revoke this invitation?') }}"
                                            wire:click="revokeInvite('{{ $inv->getKey() }}')"
                                        >
                                            {{ __('Revoke') }}
                                        </x-filament::button>
                                    </span>
                                </li>
                            @empty
                                <li class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground">
                                    {{ __('No pending invitations.') }}
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @else
                <div class="rounded-3xl border border-border/60 bg-card/90 p-6 text-sm text-muted-foreground shadow-sm">
                    <p class="font-semibold text-foreground">{{ __('Reminder') }}</p>
                    <p class="mt-2">{{ __('You participate in this challenge. Use the Daily Challenge to log your shipments and monitor your streak badges.') }}</p>
                </div>
            @endif

            <div class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-foreground">{{ __('My latest logs') }}</h2>
                    @if (! $isOwner)
                        <x-filament::button
                            color="gray"
                            size="sm"
                            wire:click="leave"
                            wire:confirm="{{ __('Leave this challenge?') }}"
                        >
                            {{ __('Leave challenge') }}
                        </x-filament::button>
                    @endif
                </div>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($myRecentLogs as $log)
                        <li class="flex items-center justify-between rounded-2xl border border-border/70 bg-background/80 px-4 py-2">
                            <span>
                                {{ __('Day :day', ['day' => $log->day_number]) }}
                                @if ($log->date)
                                    · {{ $log->date->format('d/m/Y') }}
                                @endif
                            </span>
                            <span class="text-muted-foreground">{{ $log->hours_coded }} h</span>
                        </li>
                    @empty
                        <li class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground">
                            {{ __('No log yet. Fill in today’s journal.') }}
                        </li>
                    @endforelse
                </ul>
            </div>
        </article>
    </section>

    <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">{{ __('Challenge calendar') }}</h2>
        <p class="text-xs text-muted-foreground">{{ __('Visualise your progress over :days days. Filled squares represent completed logs.', ['days' => $targetDays]) }}</p>

        <div class="mt-4 space-y-2">
            @foreach ($calendarChunks as $chunk)
                <div class="grid grid-cols-10 gap-1">
                    @foreach ($chunk as $day)
                        @php($done = in_array($day, $myDoneDays, true))
                        <div
                            class="flex h-7 items-center justify-center rounded bg-muted text-foreground {{ $done ? 'text-xl' : 'text-[10px]' }}"
                            title="{{ __('Day :day', ['day' => $day]) }}"
                        >
                            {{ $done ? '🔥' : $day }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>
</div>
