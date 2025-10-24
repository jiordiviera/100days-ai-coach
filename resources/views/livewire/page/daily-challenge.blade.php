@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $formatHours = static fn($value) => rtrim(rtrim(number_format((float) $value, 1, '.', ' '), '0'), '.');
    $formatNumber = static fn($value) => number_format((int) $value, 0, '.', ' ');
@endphp

@if (!$run)
    <div class="mx-auto max-w-6xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <section class="relative overflow-hidden rounded-3xl border border-primary/20 bg-linear-to-br from-primary/10 via-primary/5 to-background shadow-xl">
            <div class="absolute -left-16 bottom-0 h-40 w-40 rounded-full bg-primary/20 blur-3xl"></div>
            <div class="absolute -right-12 top-0 h-40 w-40 rounded-full bg-primary/15 blur-3xl"></div>

            <div class="relative grid gap-8 p-8 lg:grid-cols-[1.3fr_0.7fr] lg:p-12">
                <div class="space-y-8">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-primary">
                            {{ __('Daily journal') }}
                        </span>
                        <h1 class="text-4xl font-bold text-foreground lg:text-5xl">
                            {{ __('No active challenge right now') }}
                        </h1>
                        <p class="max-w-2xl text-base text-muted-foreground">
                            {{ __('Join a #100DaysOfCode run or launch your own to start logging shipments and unlock badges.') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a wire:navigate href="{{ route('challenges.index') }}"
                            class="group inline-flex items-center gap-2 rounded-full bg-primary px-8 py-4 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-primary/30">
                            {{ __('Explore challenges') }}
                        </a>
                        <a wire:navigate href="{{ route('challenges.index') }}#create"
                            class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 bg-background px-7 py-4 text-sm font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                            Créer mon challenge
                            @if (app()->getLocale() !== 'fr')
                                <span class="sr-only">{{ __('Create my challenge') }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                <div class="space-y-6 rounded-3xl border border-primary/20 bg-background/90 p-8 shadow-2xl backdrop-blur-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">
                            Rejoindre via un code
                            @if (app()->getLocale() !== 'fr')
                                <span class="sr-only">{{ __('Join with a code') }}</span>
                            @endif
                        </p>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ __('Paste a public code or private invite to get started instantly.') }}
                        </p>
                    </div>
                    <form wire:submit.prevent="joinWithCode" class="space-y-3">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input type="text" wire:model.defer="inviteCode"
                                placeholder="{{ __('Invitation code or public challenge') }}"
                                class="flex-1 rounded-2xl border-2 border-primary/20 bg-background/50 px-5 py-3 text-sm text-foreground transition-colors focus:border-primary focus:outline-none">
                            <button type="submit" wire:loading.attr="disabled"
                                class="rounded-full bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-lg transition-all hover:scale-105 hover:shadow-xl disabled:opacity-50">
                                {{ __('Join') }}
                            </button>
                        </div>
                    </form>

                    @if ($pendingInvitations->isNotEmpty())
                        <div class="space-y-3 rounded-2xl border border-primary/20 bg-primary/5 p-5">
                            <h3 class="text-sm font-bold text-foreground">{{ __('Pending invitations') }}</h3>
                            <ul class="space-y-2">
                                @foreach ($pendingInvitations as $invitation)
                                    <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-primary/20 bg-background px-4 py-3">
                                        <div>
                                            <p class="font-semibold text-foreground">{{ $invitation->run->title }}</p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ __('Invited by :name', ['name' => $invitation->run->owner?->name ?? __('a member')]) }}
                                            </p>
                                        </div>
                                        <button type="button" wire:click="acceptInvitation('{{ $invitation->id }}')"
                                            wire:loading.attr="disabled"
                                            class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow-lg transition-all hover:scale-105 hover:shadow-xl">
                                            {{ __('Accept') }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@else
<div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
    @php
        $challengeDateParsed = Carbon::parse($challengeDate);
        $formattedDate = $challengeDateParsed->translatedFormat('d F Y');
        $dayLabel = __('Day :current of :total', ['current' => $currentDayNumber, 'total' => $run->target_days]);
        $progressPercent = $summary['completion'] ?? 0;
    @endphp

    <livewire:onboarding.daily-challenge-tour />
    
    {{-- Hero Section --}}
    <section class="relative overflow-hidden rounded-3xl border border-primary/20 bg-linear-to-br from-primary/10 via-primary/5 to-background shadow-xl">
        <div class="absolute -left-16 bottom-0 h-40 w-40 rounded-full bg-primary/20 blur-3xl"></div>
        <div class="absolute -right-12 top-0 h-40 w-40 rounded-full bg-primary/15 blur-3xl"></div>

        <div class="relative grid gap-8 p-8 lg:grid-cols-[1.3fr_0.7fr] lg:p-12">
            <div class="space-y-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-primary">
                            {{ $dayLabel }}
                        </span>
                        <h1 class="text-4xl font-bold text-foreground lg:text-5xl">{{ $formattedDate }}</h1>
                        <p class="max-w-2xl text-base text-muted-foreground">
                            {{ __('Challenge ":title" hosted by :owner. Log today\'s shipment to keep your streak alive.', [
                                'title' => $run->title ?? __('100DaysOfCode'),
                                'owner' => $run->owner->name,
                            ]) }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="goToDay('previous')" @class([
                            'rounded-full border-2 px-5 py-2.5 text-xs font-semibold transition-all',
                            'border-primary/30 text-foreground hover:border-primary hover:bg-primary/5' => $canGoPrevious,
                            'border-primary/10 text-muted-foreground/50 cursor-not-allowed' => !$canGoPrevious,
                        ]) @disabled(!$canGoPrevious)>
                            {{ __('Previous day') }}
                        </button>
                        <button type="button" wire:click="goToDay('next')" @class([
                            'rounded-full border-2 px-5 py-2.5 text-xs font-semibold transition-all',
                            'border-primary/30 text-foreground hover:border-primary hover:bg-primary/5' => $canGoNext,
                            'border-primary/10 text-muted-foreground/50 cursor-not-allowed' => !$canGoNext,
                        ]) @disabled(!$canGoNext)>
                            {{ __('Next day') }}
                        </button>
                        <button type="button" wire:click="$dispatch('daily-challenge-tour-open')"
                            class="rounded-full border-2 border-primary/30 bg-primary/10 px-5 py-2.5 text-xs font-semibold text-primary transition-all hover:border-primary hover:bg-primary/15">
                            {{ __('Open the tour again') }}
                        </button>
                    </div>
                </div>

                @php($currentStreak = max(0, $summary['streak'] ?? 0))
                @php($displayFlames = min($currentStreak, 7))

                {{-- Stats Grid --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="group rounded-2xl border border-primary/20 bg-linear-to-br from-primary/15 via-primary/10 to-primary/5 p-6 transition-all hover:border-primary/30 hover:shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Current streak') }}</p>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-linear-to-br from-primary to-primary/80 text-4xl shadow-lg shadow-primary/30">
                                🔥
                            </div>
                            <div>
                                <p class="text-4xl font-bold text-foreground">{{ $currentStreak }}</p>
                                <p class="text-xs text-muted-foreground">{{ __('days in a row') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1">
                            @for ($i = 0; $i < $displayFlames; $i++)
                                <span class="text-lg leading-none">🔥</span>
                            @endfor
                            @if ($currentStreak > 7)
                                <span class="rounded-full bg-primary/20 px-2 py-0.5 text-[11px] font-bold text-primary">
                                    +{{ $currentStreak - 7 }}
                                </span>
                            @endif
                        </div>
                        <p class="mt-3 text-xs text-muted-foreground">{{ __('Log today to keep the fire alive.') }}</p>
                    </div>
                    <div class="group rounded-2xl border border-primary/20 bg-background/80 p-6 backdrop-blur-sm transition-all hover:border-primary/30 hover:shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Logs recorded') }}</p>
                        <p class="mt-3 text-3xl font-bold text-foreground">{{ $summary['totalLogs'] ?? 0 }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">{{ __('Since the run started') }}</p>
                    </div>
                    <div class="group rounded-2xl border border-primary/20 bg-background/80 p-6 backdrop-blur-sm transition-all hover:border-primary/30 hover:shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Total hours') }}</p>
                        <p class="mt-3 text-3xl font-bold text-foreground">
                            {{ $formatHours($summary['totalHours'] ?? 0) }}<span class="text-xl text-muted-foreground">h</span>
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ __(':hours h this week', ['hours' => $formatHours($summary['hoursThisWeek'] ?? 0)]) }}
                        </p>
                    </div>
                    <div class="group rounded-2xl border border-primary/20 bg-background/80 p-6 backdrop-blur-sm transition-all hover:border-primary/30 hover:shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Progress') }}</p>
                        <p class="mt-3 text-3xl font-bold text-primary">{{ $progressPercent }}%</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ __('Target: :days days', ['days' => $run->target_days]) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="relative space-y-5 rounded-3xl border border-primary/20 bg-background/90 p-8 shadow-2xl backdrop-blur-sm">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Shortcuts') }}</p>
                    <h2 class="mt-2 text-xl font-bold text-foreground">{{ __('Quick actions') }}</h2>
                </div>
                <dl class="space-y-4 text-sm">
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3">
                        <dt class="text-muted-foreground">{{ __('Last log') }}</dt>
                        <dd class="font-semibold text-foreground">
                            @if ($summary['lastLogAt'] ?? false)
                                {{ $summary['lastLogAt']->translatedFormat('d/m/Y') }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3">
                        <dt class="text-muted-foreground">{{ __('Active projects') }}</dt>
                        <dd class="font-semibold text-foreground">{{ count($projectBreakdown) }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3">
                        <dt class="text-muted-foreground">{{ __('Entries remaining') }}</dt>
                        <dd class="font-semibold text-foreground">{{ max(0, $run->target_days - ($summary['totalLogs'] ?? 0)) }}</dd>
                    </div>
                </dl>
                @if (auth()->id() !== $run->owner_id)
                    <button type="button" wire:confirm="{{ __('Leave this challenge?') }}" wire:click="leave"
                        class="w-full rounded-full border-2 border-primary/30 px-5 py-3 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                        {{ __('Leave challenge') }}
                    </button>
                @endif
            </div>
        </div>
    </section>

    {{-- Main Content --}}
    <section class="grid gap-6 lg:grid-cols-[1.5fr_0.5fr]">
        {{-- Entry Form/Display --}}
        <article class="space-y-6 rounded-3xl border border-primary/20 bg-background p-8 shadow-lg">
            @if ($showReminder)
                <div class="rounded-2xl border-2 border-primary/30 bg-primary/10 px-6 py-4 text-sm font-medium text-foreground">
                    {{ __('No log yet today. Fill it in to keep your streak alive!') }}
                </div>
            @endif
            @if (session()->has('message'))
                <div class="rounded-2xl border-2 border-primary/30 bg-primary/10 px-6 py-4 text-sm font-medium text-primary">
                    {{ session('message') }}
                </div>
            @endif

            @if ($todayEntry && !$isEditing)
            <div class="space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="inline-flex items-center rounded-full bg-primary/20 px-4 py-2 text-xs font-bold text-primary">
                        {{ __('Entry completed for today') }}
                    </span>
                    <button type="button" wire:click="startEditing"
                        class="rounded-full border-2 border-primary/30 px-6 py-2.5 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                        {{ __('Edit my entry') }}
                    </button>
                </div>

                <div class="space-y-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Description') }}</p>
                        <p class="mt-3 whitespace-pre-line rounded-2xl border border-primary/20 bg-primary/5 px-6 py-4 text-sm text-foreground">
                            {{ $todayEntry->notes ?: '—' }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-primary/20 bg-primary/5 px-5 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Hours coded') }}</p>
                            <p class="mt-2 text-xl font-bold text-foreground">
                                {{ $formatHours($todayEntry->hours_coded) }}<span class="text-base text-muted-foreground">h</span>
                            </p>
                        </div>
                        <div class="rounded-2xl border border-primary/20 bg-primary/5 px-5 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Learnings') }}</p>
                            <p class="mt-2 text-sm text-foreground">{{ $todayEntry->learnings ?: '—' }}</p>
                        </div>
                        <div class="rounded-2xl border border-primary/20 bg-primary/5 px-5 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Challenges faced') }}</p>
                            <p class="mt-2 text-sm text-foreground">{{ $todayEntry->challenges_faced ?: '—' }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Projects worked on') }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @php($projects = collect($todayEntry->projects_worked_on ?? []))
                            @forelse ($projects as $pid)
                                @php($project = $allProjects->firstWhere('id', $pid))
                                <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-xs font-semibold text-primary">
                                    {{ $project?->name ?? __('Deleted project') }}
                                </span>
                            @empty
                                <span class="text-sm text-muted-foreground">{{ __('No project linked.') }}</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @else
            <form wire:submit.prevent="saveEntry" class="space-y-6" id="daily-log-form">
                {{ $this->form }}

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-muted-foreground">{{ __('Shortcuts:') }}</span>
                    @foreach ([0.5, 1, 2, 3, 4] as $preset)
                        @php($label = rtrim(rtrim(number_format($preset, 1, '.', ' '), '0'), '.'))
                        <button type="button" wire:click="$set('dailyForm.hours_coded', {{ $preset }})"
                            class="rounded-full border-2 border-primary/30 px-4 py-1.5 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/10">
                            {{ $label }}h
                        </button>
                    @endforeach
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit"
                        class="rounded-full bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-primary/30">
                        {{ __('Save my progress') }}
                    </button>
                    @if ($todayEntry)
                        <button type="button" wire:click="cancelEditing"
                            class="rounded-full border-2 border-primary/30 px-6 py-3 text-sm font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                            {{ __('Cancel') }}
                        </button>
                    @endif
                </div>
            </form>
            @endif

            {{-- AI Insights --}}
            @if ($todayEntry)
            <div class="rounded-3xl border border-primary/20 bg-primary/5 p-8 shadow-lg" @if($shouldPollAi) wire:poll.7s="pollAiPanel" @endif>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-foreground">{{ __('AI insights') }}</h2>
                        <p class="text-sm text-muted-foreground">
                            @if ($aiPanel['status'] === 'pending')
                                {{ __('Generation in progress...') }}
                            @elseif ($aiPanel['updated_at'])
                                {{ __('Updated on :date', ['date' => optional($aiPanel['updated_at'])->translatedFormat('d/m/Y à H\hi')]) }}
                            @else
                                {{ __('Awaiting first generation.') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap" x-data="{
                        copied: null,
                        drafts: {{ Js::from($aiPanel['share_templates'] ?? []) }},
                        fallback: {{ Js::from($aiPanel['share_draft'] ?? '') }},
                        content(type) {
                            const drafts = this.drafts || {};
                            const candidate = drafts[type] ? drafts[type].toString() : '';
                            if (candidate.trim().length > 0) return candidate;
                            if (type === 'linkedin' && this.fallback) return this.fallback.toString();
                            return '';
                        },
                        has(type) { return this.content(type).trim().length > 0; },
                        copy(type) {
                            const value = this.content(type);
                            if (!value) return;
                            navigator.clipboard.writeText(value);
                            this.copied = type;
                            setTimeout(() => { if (this.copied === type) this.copied = null; }, 2000);
                        }
                    }">
                        <button type="button" wire:click="regenerateAi" @disabled($aiPanel['status'] === 'pending')
                            class="rounded-full border-2 border-primary/30 px-5 py-2 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5 disabled:opacity-50">
                            {{ __('Regenerate AI') }}
                        </button>
                        <button type="button" @click.prevent="copy('linkedin')" x-bind:disabled="!has('linkedin')"
                            class="rounded-full bg-primary px-5 py-2 text-xs font-semibold text-primary-foreground shadow-lg transition-all hover:scale-105 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-show="copied !== 'linkedin'">{{ __('Copy LinkedIn draft') }}</span>
                            <span x-show="copied === 'linkedin'" x-cloak>{{ __('Copied!') }}</span>
                        </button>
                        <button type="button" @click.prevent="copy('x')" x-bind:disabled="!has('x')"
                            class="rounded-full border-2 border-primary/30 bg-primary/10 px-5 py-2 text-xs font-semibold text-primary shadow-lg transition-all hover:scale-105 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50">
                            <span x-show="copied !== 'x'">{{ __('Copy X draft') }}</span>
                            <span x-show="copied === 'x'" x-cloak>{{ __('Copied!') }}</span>
                        </button>
                    </div>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Summary') }}</p>
                        @if ($aiPanel['status'] === 'ready' && $aiPanel['summary'])
                            <div class="prose prose-sm mt-3 max-w-none dark:prose-invert">
                                {!! Str::markdown($aiPanel['summary'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                            </div>
                        @else
                            <div class="mt-3 h-24 animate-pulse rounded-2xl bg-primary/10" aria-hidden="true"></div>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Tags') }}</p>
                        @if ($aiPanel['status'] === 'ready' && filled($aiPanel['tags']))
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($aiPanel['tags'] as $tag)
                                    <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-xs font-semibold text-primary">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-3 flex gap-2">
                                <div class="h-7 w-20 animate-pulse rounded-full bg-primary/10"></div>
                                <div class="h-7 w-16 animate-pulse rounded-full bg-primary/8"></div>
                                <div class="h-7 w-18 animate-pulse rounded-full bg-primary/6"></div>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Coach tip') }}</p>
                            @if ($aiPanel['status'] === 'ready' && $aiPanel['coach_tip'])
                                <p class="mt-3 rounded-2xl border border-primary/20 bg-background px-5 py-4 text-sm text-foreground">
                                    {{ $aiPanel['coach_tip'] }}
                                </p>
                            @else
                                <div class="mt-3 h-20 animate-pulse rounded-2xl bg-primary/10"></div>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary/70">{{ __('Share drafts') }}</p>
                            @php($templates = $aiPanel['share_templates'] ?? [])
                            @php($linkedinTemplate = $templates['linkedin'] ?? $aiPanel['share_draft'])
                            @php($xTemplate = $templates['x'] ?? null)
                            @if ($aiPanel['status'] === 'ready' && ($linkedinTemplate || $xTemplate))
                                <div class="mt-3 space-y-3">
                                    @if ($linkedinTemplate)
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-primary/60">LinkedIn</p>
                                            <pre class="mt-2 max-h-48 overflow-auto whitespace-pre-wrap  wrap-break-word rounded-2xl border border-primary/20 bg-background px-4 py-3 text-xs">{{ $linkedinTemplate }}</pre>
                                        </div>
                                    @endif
                                    @if ($xTemplate)
                                        <div>
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-primary/60">X</p>
                                            <pre class="mt-2 max-h-40 overflow-auto whitespace-pre-wrap  wrap-break-word rounded-2xl border border-primary/20 bg-background px-4 py-3 text-xs">{{ $xTemplate }}</pre>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="mt-3 space-y-2">
                                    <div class="h-24 animate-pulse rounded-2xl bg-primary/10"></div>
                                    <div class="h-20 animate-pulse rounded-2xl bg-primary/8"></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($aiPanel['status'] === 'ready' && $aiPanel['model'])
                        <p class="text-xs text-muted-foreground">
                            {{ __("Generated with :model · :latency ms", ['model' => $aiPanel['model'], 'latency' => $aiPanel['latency_ms'] ?? '—']) }}
                            · ${{ number_format((float) ($aiPanel['cost_usd'] ?? 0), 3) }}
                        </p>
                    @endif
                </div>
            </div>
            @endif
        </article>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            {{-- GitHub Repository --}}
            @if ($githubRepository)
                <section class="rounded-3xl border border-primary/20 bg-background p-6 shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-foreground">{{ __('GitHub repository') }}</h2>
                            <p class="text-xs text-muted-foreground">{{ __('Log everything in your dedicated repo.') }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
                            {{ \Illuminate\Support\Str::ucfirst($githubRepository['visibility'] ?? 'private') }}
                        </span>
                    </div>
                    <div class="mt-5 flex items-center justify-between">
                        <span class="text-sm font-semibold text-foreground">{{ $githubRepository['label'] }}</span>
                        <a href="{{ $githubRepository['url'] }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-full border-2 border-primary/30 px-4 py-1.5 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5">
                            {{ __('Open') }}
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.293 2.293a1 1 0 011.414 0l4 4a1 1 0 01-.707 1.707H15a1 1 0 110-2h.586L11 3.414V5a1 1 0 11-2 0V2a1 1 0 011-1h3a1 1 0 01.707.293zM5 5a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-2a1 1 0 112 0v2a5 5 0 01-5 5H5a5 5 0 01-5-5V8a5 5 0 015-5h2a1 1 0 110 2H5z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </section>
            @endif

            {{-- Public Sharing --}}
            <section class="rounded-3xl border border-primary/20 bg-background p-6 shadow-lg" id="project-section">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-foreground">{{ __('Public sharing') }}</h2>
                        <p class="text-xs text-muted-foreground">
                            {{ __('Generate a read-only public link for your journal.') }}
                        </p>
                    </div>
                    @if ($publicShare && empty($publicShare['expired']))
                        <span class="inline-flex items-center rounded-full bg-primary/20 px-3 py-1 text-xs font-bold text-primary">{{ __('Active') }}</span>
                    @elseif ($publicShare && !empty($publicShare['expired']))
                        <span class="inline-flex items-center rounded-full bg-muted px-3 py-1 text-xs font-bold text-muted-foreground">{{ __('Expired') }}</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-muted px-3 py-1 text-xs font-bold text-muted-foreground">{{ __('Inactive') }}</span>
                    @endif
                </div>

                @if ($publicShare && empty($publicShare['expired']))
                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl border border-primary/20 bg-primary/5 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <span class="break-all text-xs text-foreground">{{ $publicShare['url'] }}</span>
                                <button type="button"
                                    onclick="navigator.clipboard.writeText('{{ $publicShare['url'] }}'); this.innerText='{{ __('Copied!') }}'; setTimeout(() => this.innerText='{{ __('Copy') }}', 2000);"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-full border-2 border-primary/30 px-4 py-2 text-xs font-semibold text-foreground transition-all hover:border-primary hover:bg-primary/5 sm:w-auto">
                                    {{ __('Copy') }}
                                </button>
                            </div>
                            @if (!empty($publicShare['expires_at']))
                                <p class="mt-2 text-[10px] text-muted-foreground">
                                    {{ __('Expires on :date.', ['date' => optional($publicShare['expires_at'])->translatedFormat('d F Y à H\hi')]) }}
                                </p>
                            @endif
                        </div>
                        <button type="button" wire:click="disablePublicShare" wire:loading.attr="disabled"
                            class="w-full rounded-full border-2 border-primary/30 px-5 py-2.5 text-xs font-semibold text-foreground transition-all hover:border-destructive hover:bg-destructive/5 hover:text-destructive">
                            {{ __('Disable sharing') }}
                        </button>
                    </div>
                @elseif ($publicShare && !empty($publicShare['expired']))
                    <div class="mt-5 space-y-3">
                        <p class="text-sm text-muted-foreground">
                            {{ __('This link has expired. Generate a new one to share your entry again.') }}
                        </p>
                        <button type="button" wire:click="enablePublicShare" wire:loading.attr="disabled"
                            class="w-full rounded-full bg-primary px-5 py-3 text-xs font-semibold text-primary-foreground shadow-lg transition-all hover:scale-105 hover:shadow-xl">
                            {{ __('Regenerate public link') }}
                        </button>
                    </div>
                @else
                    <div class="mt-5 space-y-3">
                        <p class="text-sm text-muted-foreground">
                            {{ __("Save today's entry, then generate a public link to share it on social media.") }}
                        </p>
                        <button type="button" wire:click="enablePublicShare" wire:loading.attr="disabled"
                            class="w-full rounded-full bg-primary px-5 py-3 text-xs font-semibold text-primary-foreground shadow-lg transition-all hover:scale-105 hover:shadow-xl">
                            {{ __('Generate public link') }}
                        </button>
                    </div>
                @endif
            </section>

            {{-- My Stats --}}
            <section class="rounded-3xl border border-primary/20 bg-background p-6 shadow-lg" id="share-section">
                <h2 class="text-lg font-bold text-foreground">{{ __('My stats') }}</h2>
                <dl class="mt-5 space-y-3">
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm">
                        <dt class="text-muted-foreground">{{ __('Current streak') }}</dt>
                        <dd class="font-bold text-foreground">{{ $summary['streak'] ?? 0 }} {{ trans_choice('day|days', $summary['streak'] ?? 0) }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm">
                        <dt class="text-muted-foreground">{{ __('Total entries') }}</dt>
                        <dd class="font-bold text-foreground">{{ $summary['totalLogs'] ?? 0 }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm">
                        <dt class="text-muted-foreground">{{ __('Total hours') }}</dt>
                        <dd class="font-bold text-foreground">{{ $formatHours($summary['totalHours'] ?? 0) }}h</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm">
                        <dt class="text-muted-foreground">{{ __('Average hours per log') }}</dt>
                        <dd class="font-bold text-foreground">{{ $formatHours($summary['averageHours'] ?? 0) }}h</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm">
                        <dt class="text-muted-foreground">{{ __('Hours this week') }}</dt>
                        <dd class="font-bold text-foreground">{{ $formatHours($summary['hoursThisWeek'] ?? 0) }}h</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm">
                        <dt class="text-muted-foreground">{{ __('Progress') }}</dt>
                        <dd class="font-bold text-primary">{{ $summary['completion'] ?? 0 }}%</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm">
                        <dt class="text-muted-foreground">{{ __('Last log') }}</dt>
                        <dd class="font-bold text-foreground">
                            @if ($summary['lastLogAt'] ?? false)
                                {{ $summary['lastLogAt']->translatedFormat('d/m/Y') }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Recent History --}}
            <section class="rounded-3xl border border-primary/20 bg-background p-6 shadow-lg">
                <h2 class="text-lg font-bold text-foreground">{{ __('Recent history') }}</h2>
                <div class="mt-4 space-y-2">
                    @forelse ($history as $entry)
                        <button type="button" wire:click="setDate('{{ $entry['date'] ?? $challengeDate }}')"
                            class="group w-full rounded-2xl border border-primary/20 bg-primary/5 px-4 py-3 text-left transition-all hover:border-primary hover:bg-primary/10">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-foreground">{{ __('Day :day', ['day' => $entry['day_number']]) }}</span>
                                    @if ($entry['retro'] ?? false)
                                        <span class="inline-flex items-center rounded-full bg-primary/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary">
                                            {{ __('Retro') }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-muted-foreground">
                                    {{ $entry['date'] ? Carbon::parse($entry['date'])->translatedFormat('d/m') : '—' }}
                                </span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                                <span>{{ $formatHours($entry['hours']) }}h</span>
                                <span>{{ count($entry['projects']) }} {{ trans_choice('project|projects', count($entry['projects'])) }}</span>
                            </div>
                        </button>
                    @empty
                        <p class="py-4 text-center text-sm text-muted-foreground">{{ __('No history to display yet.') }}</p>
                    @endforelse
                </div>
            </section>

            {{-- Most Active Projects --}}
            <section class="rounded-3xl border border-primary/20 bg-background p-6 shadow-lg">
                <h2 class="text-lg font-bold text-foreground">{{ __('Most active projects') }}</h2>
                <div class="mt-4 space-y-2">
                    @forelse ($projectBreakdown as $project)
                        <div class="flex items-center justify-between rounded-2xl border border-primary/20 bg-primary/5 px-4 py-3">
                            <span class="font-semibold text-foreground">{{ $project['name'] }}</span>
                            <span class="text-xs font-bold text-primary">
                                {{ $project['count'] }} {{ trans_choice('day|days', $project['count']) }}
                            </span>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-muted-foreground">
                            {{ __('Link your journal to a project to see the breakdown here.') }}
                        </p>
                    @endforelse
                </div>
            </section>
        </aside>
    </section>
</div>
@endif