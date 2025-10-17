@php
    use Illuminate\Support\Carbon;use Illuminate\Support\Str;

    $formatHours = static fn ($value) => rtrim(rtrim(number_format((float) $value, 1, '.', ' '), '0'), '.');
    $formatNumber = static fn ($value) => number_format((int) $value, 0, '.', ' ');
@endphp


@if (!$run)
    <div class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6 lg:px-0">
        <section
            class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
            <div class="absolute inset-0">
                <div class="absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
                <div class="absolute -right-12 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
            </div>

            <div class="relative grid gap-10 p-8 lg:grid-cols-[1.2fr_0.8fr] lg:p-10">
                <div class="space-y-6">
                    <div class="space-y-2">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
              {{ __('Daily journal') }}
            </span>
                        <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ __('No active challenge right now') }}</h1>
                        <p class="max-w-xl text-sm text-muted-foreground sm:text-base">
                            {{ __('Join a #100DaysOfCode run or launch your own to start logging shipments and unlock badges.') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a
                            wire:navigate
                            href="{{ route('challenges.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
                        >
                            {{ __('Explore challenges') }}
                        </a>
                        <a
                            wire:navigate
                            href="{{ route('challenges.index') }}#create"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                        >
                            {{ __('Create my challenge') }}
                        </a>
                    </div>
                </div>

                <div class="space-y-5 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Join with a code') }}</p>
                        <p class="text-sm text-muted-foreground">{{ __('Paste a public code or private invite to get started instantly.') }}</p>
                    </div>
                    <form wire:submit.prevent="joinWithCode" class="space-y-3">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input
                                type="text"
                                wire:model.defer="inviteCode"
                                placeholder="{{ __('Invitation code or public challenge') }}"
                                class="flex-1 rounded-2xl border border-border/70 bg-background px-4 py-2 text-sm text-foreground focus:border-primary focus:outline-none"
                            >
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow transition hover:shadow-lg"
                            >
                                {{ __('Join') }}
                            </button>
                        </div>
                    </form>

                    @if ($pendingInvitations->isNotEmpty())
                        <div class="space-y-3 rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
                            <h3 class="text-sm font-semibold text-foreground">{{ __('Pending invitations') }}</h3>
                            <ul class="space-y-2 text-sm">
                                @foreach ($pendingInvitations as $invitation)
                                    <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border/60 bg-background px-3 py-2">
                                        <div>
                                            <p class="font-medium text-foreground">{{ $invitation->run->title }}</p>
                                            <p class="text-xs text-muted-foreground">{{ __('Invited by :name', ['name' => $invitation->run->owner?->name ?? __('a member')]) }}</p>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="acceptInvitation('{{ $invitation->id }}')"
                                            wire:loading.attr="disabled"
                                            class="rounded-full bg-primary px-3 py-1.5 text-xs font-semibold text-primary-foreground shadow hover:shadow-lg"
                                        >
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
    <div class="mx-auto max-w-6xl space-y-12 px-4 sm:px-6 ">
        @php
            $challengeDateParsed = Carbon::parse($challengeDate);
            $formattedDate = $challengeDateParsed->translatedFormat('d F Y');
            $dayLabel = __('Day :current of :total', ['current' => $currentDayNumber, 'total' => $run->target_days]);
            $progressPercent = $summary['completion'] ?? 0;
        @endphp

        <livewire:onboarding.daily-challenge-tour />
        <section
            class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
            <div class="absolute inset-0">
                <div class="absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
                <div class="absolute -right-12 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
            </div>

            <div class="relative grid gap-10 p-8 lg:grid-cols-[1.25fr_0.75fr] lg:p-10">
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-2">
              <span
                  class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
                {{ $dayLabel }}
              </span>
                            <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ $formattedDate }}</h1>
                            <p class="max-w-xl text-sm text-muted-foreground sm:text-base">
                                {{ __('Challenge “:title” hosted by :owner. Log today’s shipment to keep your streak alive.', [
                                    'title' => $run->title ?? __('100DaysOfCode'),
                                    'owner' => $run->owner->name,
                                ]) }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="goToDay('previous')"
                                @class([
                                    'rounded-full border px-4 py-2 text-xs font-semibold transition',
                                    'border-border/70 text-muted-foreground hover:border-primary hover:text-primary' => $canGoPrevious,
                                    'border-border/40 text-muted-foreground/70 cursor-not-allowed' => ! $canGoPrevious,
                                ])
                                @disabled(! $canGoPrevious)
                            >
                                {{ __('Previous day') }}
                            </button>
                            <button
                                type="button"
                                wire:click="goToDay('next')"
                                @class([
                                    'rounded-full border px-4 py-2 text-xs font-semibold transition',
                                    'border-border/70 text-muted-foreground hover:border-primary hover:text-primary' => $canGoNext,
                                    'border-border/40 text-muted-foreground/70 cursor-not-allowed' => ! $canGoNext,
                                ])
                                @disabled(! $canGoNext)
                            >
                                {{ __('Next day') }}
                            </button>
                            <button
                                type="button"
                                wire:click="$dispatch('daily-challenge-tour-open')"
                                class="rounded-full border border-primary/40 bg-primary/10 px-4 py-2 text-xs font-semibold text-primary transition hover:border-primary/60 hover:bg-primary/20"
                            >
                                {{ __('Open the tour again') }}
                            </button>
                        </div>
                    </div>

                    @php($currentStreak = max(0, $summary['streak'] ?? 0))
                    @php($displayFlames = min($currentStreak, 7))

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-border/70 bg-gradient-to-br from-amber-500/10 via-orange-400/10 to-primary/10 p-4">
                            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Current streak') }}</p>
                            <div class="mt-3 flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-rose-400 text-3xl shadow-inner shadow-amber-500/40">
                                    🔥
                                </div>
                                <div>
                                    <p class="text-3xl font-semibold text-foreground">{{ $currentStreak }}</p>
                                    <p class="text-xs uppercase tracking-[0.28em] text-muted-foreground">
                                        {{ __('days in a row') }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-1">
                                @for ($i = 0; $i < $displayFlames; $i++)
                                    <span class="text-lg leading-none">🔥</span>
                                @endfor
                                @if ($currentStreak > 7)
                                    <span class="rounded-full bg-amber-500/15 px-2 py-0.5 text-[11px] font-semibold text-amber-600">
                                        +{{ $currentStreak - 7 }}
                                    </span>
                                @endif
                            </div>
                            <p class="mt-3 text-xs text-muted-foreground">{{ __('Log today to keep the fire alive.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Logs recorded') }}</p>
                            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $summary['totalLogs'] ?? 0 }}</p>
                            <p class="text-xs text-muted-foreground">{{ __('Since the run started') }}</p>
                        </div>
                        <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Total hours') }}</p>
                            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $formatHours($summary['totalHours'] ?? 0) }} {{ __('h') }}</p>
                            <p class="text-xs text-muted-foreground">{{ __(':hours h this week', ['hours' => $formatHours($summary['hoursThisWeek'] ?? 0)]) }}</p>
                        </div>
                        <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
                            <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Progress') }}</p>
                            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $progressPercent }}%</p>
                            <p class="text-xs text-muted-foreground">{{ __('Target: :days days', ['days' => $run->target_days]) }}</p>
                        </div>
                    </div>
                </div>

                    <div class="relative space-y-4 rounded-3xl border border-border/60 bg-card/90 p-3 shadow-xl">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Shortcuts') }}</p>
                        <h2 class="mt-1 text-lg font-semibold text-foreground">{{ __('Quick actions') }}</h2>
                    </div>
                    <dl class="space-y-3 text-sm text-muted-foreground">
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Last log') }}</dt>
                            <dd>
                                @if ($summary['lastLogAt'] ?? false)
                                    {{ $summary['lastLogAt']->translatedFormat('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Active projects') }}</dt>
                            <dd>{{ count($projectBreakdown) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Entries remaining') }}</dt>
                            <dd>{{ max(0, $run->target_days - ($summary['totalLogs'] ?? 0)) }}</dd>
                        </div>
                    </dl>
                    @if (auth()->id() !== $run->owner_id)
                        <button
                            type="button"
                            wire:confirm="{{ __('Leave this challenge?') }}"
                            wire:click="leave"
                            class="w-full rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                        >
                            {{ __('Leave challenge') }}
                        </button>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
            <article class="space-y-6 rounded-3xl border border-border/60 bg-card/90 p-3 shadow-sm">
                @if ($showReminder)
                    <div class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        {{ __('No log yet today. Fill it in to keep your streak alive!') }}
                    </div>
                @endif
                @if (session()->has('message'))
                    <div class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('message') }}
                    </div>
                @endif

                @if ($todayEntry && ! $isEditing)
                    <div class="space-y-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
              <span
                  class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">
                {{ __('Entry completed for today') }}
              </span>
                            <button
                                type="button"
                                wire:click="startEditing"
                                class="rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                            >
                                {{ __('Edit my entry') }}
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Description') }}</p>
                                <p class="mt-1 whitespace-pre-line rounded-2xl border border-border/70 bg-background/80 px-4 py-3 text-sm">
                                    {{ $todayEntry->notes ?: '—' }}
                                </p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
                                    <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Hours coded') }}</p>
                                    <p class="mt-1 text-base font-semibold text-foreground">{{ $formatHours($todayEntry->hours_coded) }}</p>
                                </div>
                                <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
                                    <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Learnings') }}</p>
                                    <p class="mt-1 text-sm text-foreground">{{ $todayEntry->learnings ?: '—' }}</p>
                                </div>
                                <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
                                    <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Challenges faced') }}</p>
                                    <p class="mt-1 text-sm text-foreground">{{ $todayEntry->challenges_faced ?: '—' }}</p>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Projects worked on') }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @php($projects = collect($todayEntry->projects_worked_on ?? []))
                                    @forelse ($projects as $pid)
                                        @php($project = $allProjects->firstWhere('id', $pid))
                                        <span
                                            class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $project?->name ?? __('Deleted project') }}</span>
                                    @empty
                                        <span class="text-sm text-muted-foreground">{{ __('No project linked.') }}</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <form wire:submit.prevent="saveEntry" class="space-y-5" id="daily-log-form">
                        {{ $this->form }}

                        <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                            <span>{{ __('Shortcuts:') }}</span>
                            @foreach ([0.5, 1, 2, 3, 4] as $preset)
                                @php($label = rtrim(rtrim(number_format($preset, 1, '.', ' '), '0'), '.'))
                                <button
                                    type="button"
                                    wire:click="$set('dailyForm.hours_coded', {{ $preset }})"
                                    class="rounded-full border border-border/70 px-3 py-1 text-foreground transition hover:border-primary hover:text-primary"
                                >
                                    {{ $label }} {{ __('h') }}
                                </button>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="submit"
                                class="rounded-full bg-primary px-6 py-2 text-sm font-semibold text-primary-foreground shadow transition hover:shadow-lg"
                            >
                                {{ __('Save my progress') }}
                            </button>
                            @if ($todayEntry)
                                <button
                                    type="button"
                                    wire:click="cancelEditing"
                                    class="rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                                >
                                    {{ __('Cancel') }}
                                </button>
                            @endif
                        </div>
                    </form>
                @endif

                @if ($todayEntry)
                    <div class="rounded-3xl border border-border/60 bg-background/90 p-6 shadow-sm"
                         @if($shouldPollAi) wire:poll.7s="pollAiPanel" @endif>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-foreground">{{ __('AI insights') }}</h2>
                                <p class="text-xs text-muted-foreground">
                                    @if ($aiPanel['status'] === 'pending')
                                        {{ __('Generation in progress...') }}
                                    @elseif ($aiPanel['updated_at'])
                                        {{ __('Updated on :date', ['date' => optional($aiPanel['updated_at'])->translatedFormat('d/m/Y à H\hi')]) }}
                                    @else
                                        {{ __('Awaiting first generation.') }}
                                    @endif
                                </p>
                            </div>
                            <div
                                class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center"
                                x-data="{
                    copied: null,
                    drafts: {{ Js::from($aiPanel['share_templates'] ?? []) }},
                    fallback: {{ Js::from($aiPanel['share_draft'] ?? '') }},
                    content(type) {
                      const drafts = this.drafts || {};
                      const candidate = drafts[type] ? drafts[type].toString() : '';

                      if (candidate.trim().length > 0) {
                        return candidate;
                      }

                      if (type === 'linkedin' && this.fallback) {
                        return this.fallback.toString();
                      }

                      return '';
                    },
                    has(type) {
                      return this.content(type).trim().length > 0;
                    },
                    copy(type) {
                      const value = this.content(type);

                      if (! value) {
                        return;
                      }

                      navigator.clipboard.writeText(value);
                      this.copied = type;
                      setTimeout(() => {
                        if (this.copied === type) {
                          this.copied = null;
                        }
                      }, 2000);
                    }
                }"
                            >
                                <button
                                    type="button"
                                    wire:click="regenerateAi"
                                    @disabled($aiPanel['status'] === 'pending')
                                    class="w-full rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary sm:w-auto"
                                >
                                    {{ __('Regenerate AI') }}
                                </button>
                                <button
                                    type="button"
                                    @click.prevent="copy('linkedin')"
                                    x-bind:disabled="! has('linkedin')"
                                    class="w-full rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow transition hover:shadow-lg disabled:cursor-not-allowed disabled:bg-primary/40 sm:w-auto"
                                >
                                    <span x-show="copied !== 'linkedin'">{{ __('Copy LinkedIn draft') }}</span>
                                    <span x-show="copied === 'linkedin'" x-cloak>{{ __('Copied!') }}</span>
                                </button>
                                <button
                                    type="button"
                                    @click.prevent="copy('x')"
                                    x-bind:disabled="! has('x')"
                                    class="w-full rounded-full bg-secondary px-4 py-2 text-xs font-semibold text-secondary-foreground shadow transition hover:shadow-lg disabled:cursor-not-allowed disabled:bg-secondary/40 sm:w-auto"
                                >
                                    <span x-show="copied !== 'x'">{{ __('Copy X draft') }}</span>
                                    <span x-show="copied === 'x'" x-cloak>{{ __('Copied!') }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Summary') }}</p>
                                @if ($aiPanel['status'] === 'ready' && $aiPanel['summary'])
                                    <div class="prose prose-sm max-w-none dark:prose-invert">
                                        {!! Str::markdown($aiPanel['summary'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                                    </div>
                                @else
                                    <div class="h-20 rounded-2xl bg-muted/70 animate-pulse" aria-hidden="true"></div>
                                @endif
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Tags') }}</p>
                                @if ($aiPanel['status'] === 'ready' && filled($aiPanel['tags']))
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($aiPanel['tags'] as $tag)
                                            <span
                                                class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex gap-2">
                                        <div class="h-6 w-20 rounded-full bg-muted/70 animate-pulse"></div>
                                        <div class="h-6 w-14 rounded-full bg-muted/60 animate-pulse"></div>
                                        <div class="h-6 w-16 rounded-full bg-muted/40 animate-pulse"></div>
                                    </div>
                                @endif
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Coach tip') }}</p>
                                    @if ($aiPanel['status'] === 'ready' && $aiPanel['coach_tip'])
                                        <p class="mt-1 rounded-2xl border border-border/70 bg-background/80 px-4 py-2 text-sm">{{ $aiPanel['coach_tip'] }}</p>
                                    @else
                                        <div class="h-16 rounded-2xl bg-muted/60 animate-pulse"></div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Share drafts') }}</p>
                                    @php($templates = $aiPanel['share_templates'] ?? [])
                                    @php($linkedinTemplate = $templates['linkedin'] ?? $aiPanel['share_draft'])
                                    @php($xTemplate = $templates['x'] ?? null)
                                    @if ($aiPanel['status'] === 'ready' && ($linkedinTemplate || $xTemplate))
                                        <div class="mt-1 space-y-3">
                                            @if ($linkedinTemplate)
                                                <div>
                                                    <p class="text-[11px] font-semibold uppercase tracking-widest text-muted-foreground/70">
                                                        LinkedIn</p>
                                                    <pre
                                                        class="mt-1 max-h-48 overflow-auto whitespace-pre-wrap break-words rounded-2xl border border-border/70 bg-background/80 px-4 py-2 text-xs">{{ $linkedinTemplate }}</pre>
                                                </div>
                                            @endif
                                            @if ($xTemplate)
                                                <div>
                                                    <p class="text-[11px] font-semibold uppercase tracking-widest text-muted-foreground/70">
                                                        X</p>
                                                    <pre
                                                        class="mt-1 max-h-40 overflow-auto whitespace-pre-wrap break-words rounded-2xl border border-border/70 bg-background/80 px-4 py-2 text-xs">{{ $xTemplate }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="space-y-2">
                                            <div class="h-20 rounded-2xl bg-muted/60 animate-pulse"></div>
                                            <div class="h-16 rounded-2xl bg-muted/40 animate-pulse"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($aiPanel['status'] === 'ready' && $aiPanel['model'])
                                <p class="text-xs text-muted-foreground">
                                    {{ __('Generated with :model · :latency ms', ['model' => $aiPanel['model'], 'latency' => $aiPanel['latency_ms'] ?? '—']) }} ·
                                    ${{ number_format((float) ($aiPanel['cost_usd'] ?? 0), 3) }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </article>

            <aside class="space-y-6">
                @if ($githubRepository)
                    <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-foreground">{{ __('GitHub repository') }}</h2>
                                <p class="text-xs text-muted-foreground">{{ __('Log everything in your dedicated repo.') }}</p>
                            </div>
                            <span
                                class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                {{ \Illuminate\Support\Str::ucfirst($githubRepository['visibility'] ?? 'private') }}
              </span>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="font-medium text-foreground">{{ $githubRepository['label'] }}</span>
                            <a
                                href="{{ $githubRepository['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-full border border-border/70 px-3 py-1 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                            >
                                {{ __('Open') }}
                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M12.293 2.293a1 1 0 011.414 0l4 4a1 1 0 01-.707 1.707H15a1 1 0 110-2h.586L11 3.414V5a1 1 0 11-2 0V2a1 1 0 011-1h3a1 1 0 01.707.293zM5 5a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-2a1 1 0 112 0v2a5 5 0 01-5 5H5a5 5 0 01-5-5V8a5 5 0 015-5h2a1 1 0 110 2H5z"
                                          clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </section>
                @endif

                <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm" id="project-section">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">{{ __('Public sharing') }}</h2>
                            <p class="text-xs text-muted-foreground">{{ __('Generate a read-only public link for your journal.') }}</p>
                        </div>
                        @if ($publicShare && empty($publicShare['expired']))
                            <span
                                class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">{{ __('Active') }}</span>
                        @elseif ($publicShare && ! empty($publicShare['expired']))
                            <span
                                class="inline-flex items-center rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-600">{{ __('Expired') }}</span>
                        @else
                            <span
                                class="inline-flex items-center rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-600">{{ __('Inactive') }}</span>
                        @endif
                    </div>

                    @if ($publicShare && empty($publicShare['expired']))
                        <div class="mt-4 space-y-3">
                            <div class="rounded-2xl border border-border/70 bg-background/80 p-3 text-xs">
                                <div
                                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-2">
                                    <span class="break-all">{{ $publicShare['url'] }}</span>
                                    <button
                                        type="button"
                                        onclick="navigator.clipboard.writeText('{{ $publicShare['url'] }}'); this.innerText='{{ __('Copied!') }}'; setTimeout(() => this.innerText='{{ __('Copy') }}', 2000);"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border/70 px-3 py-1 font-semibold text-xs text-muted-foreground transition hover:border-primary/50 hover:text-primary sm:w-auto"
                                    >
                                        {{ __('Copy') }}
                                    </button>
                                </div>
                                @if (! empty($publicShare['expires_at']))
                                    <p class="mt-2 text-[11px] text-muted-foreground">
                                        {{ __('Expires on :date.', ['date' => optional($publicShare['expires_at'])->translatedFormat('d F Y à H\hi')]) }}
                                    </p>
                                @endif
                            </div>
                            <button
                                type="button"
                                wire:click="disablePublicShare"
                                wire:loading.attr="disabled"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-destructive/50 hover:text-destructive sm:w-auto"
                            >
                                {{ __('Disable sharing') }}
                            </button>
                        </div>
                    @elseif ($publicShare && ! empty($publicShare['expired']))
                        <div class="mt-4 space-y-3">
                            <p class="text-xs text-muted-foreground">
                                {{ __('This link has expired. Generate a new one to share your entry again.') }}
                            </p>
                            <button
                                type="button"
                                wire:click="enablePublicShare"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow transition hover:shadow-lg"
                            >
                                {{ __('Regenerate public link') }}
                            </button>
                        </div>
                    @else
                        <div class="mt-4 space-y-3">
                            <p class="text-xs text-muted-foreground">
                                {{ __('Save today’s entry, then generate a public link to share it on social media.') }}
                            </p>
                            <button
                                type="button"
                                wire:click="enablePublicShare"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow transition hover:shadow-lg"
                            >
                                {{ __('Generate public link') }}
                            </button>
                        </div>
                    @endif
                </section>

                <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm" id="share-section">
                    <h2 class="text-lg font-semibold text-foreground">{{ __('My stats') }}</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Current streak') }}</dt>
                            <dd>{{ $summary['streak'] ?? 0 }} {{ trans_choice(':count day|:count days', $summary['streak'] ?? 0, ['count' => $summary['streak'] ?? 0]) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Total entries') }}</dt>
                            <dd>{{ $summary['totalLogs'] ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Total hours') }}</dt>
                            <dd>{{ $formatHours($summary['totalHours'] ?? 0) }} {{ __('h') }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Average hours per log') }}</dt>
                            <dd>{{ $formatHours($summary['averageHours'] ?? 0) }} {{ __('h') }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Hours this week') }}</dt>
                            <dd>{{ $formatHours($summary['hoursThisWeek'] ?? 0) }} {{ __('h') }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Progress') }}</dt>
                            <dd>{{ $summary['completion'] ?? 0 }}%</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt>{{ __('Last log') }}</dt>
                            <dd>
                                @if ($summary['lastLogAt'] ?? false)
                                    {{ $summary['lastLogAt']->translatedFormat('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-foreground">{{ __('Recent history') }}</h2>
                    <div class="mt-3 space-y-2 text-sm">
                        @forelse ($history as $entry)
                            <button
                                type="button"
                                wire:click="setDate('{{ $entry['date'] ?? $challengeDate }}')"
                                class="w-full rounded-2xl border border-border/60 bg-background/80 px-4 py-2 text-left transition hover:border-primary/50 hover:text-primary"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span>{{ __('Day :day', ['day' => $entry['day_number']]) }}</span>
                                        @if ($entry['retro'] ?? false)
                                            <span
                                                class="inline-flex items-center rounded-full bg-amber-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-400">
                        {{ __('Retro') }}
                      </span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-muted-foreground">
                    {{ $entry['date'] ? Carbon::parse($entry['date'])->translatedFormat('d/m') : '—' }}
                  </span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-xs text-muted-foreground">
                                    <span>{{ $formatHours($entry['hours']) }} {{ __('h') }}</span>
                                    <span>{{ count($entry['projects']) }} {{ trans_choice(':count project|:count projects', count($entry['projects']), ['count' => count($entry['projects'])]) }}</span>
                                </div>
                            </button>
                        @empty
                            <p class="text-xs text-muted-foreground">{{ __('No history to display yet.') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-foreground">{{ __('Most active projects') }}</h2>
                    <div class="mt-3 space-y-2 text-sm">
                        @forelse ($projectBreakdown as $project)
                            <div
                                class="flex items-center justify-between rounded-2xl border border-border/60 bg-background/80 px-3 py-2">
                                <span>{{ $project['name'] }}</span>
                                <span
                                    class="text-xs text-muted-foreground">{{ $project['count'] }} {{ trans_choice(':count day|:count days', $project['count'], ['count' => $project['count']]) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-muted-foreground">{{ __('Link your journal to a project to see the breakdown here.') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </section>
    </div>
@endif
