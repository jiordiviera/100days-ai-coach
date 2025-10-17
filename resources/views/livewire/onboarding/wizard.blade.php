@php
    $stepsMeta = [
        1 => [
            'label' => __('Profile setup'),
            'description' => __('Define your public profile and focus area.'),
        ],
        2 => [
            'label' => __('Challenge configuration'),
            'description' => __('Plan your run timeline and goals.'),
        ],
        3 => [
            'label' => __('Daily reminders'),
            'description' => __('Pick notification channels and reminder time.'),
        ],
    ];
    $totalSteps = count($stepsMeta);
@endphp

<div class="mx-auto max-w-5xl space-y-10 px-4 py-12 sm:px-6 lg:px-0">
    <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">{{ __('Onboarding') }}</p>
                    <h1 class="mt-1 text-2xl font-semibold text-foreground lg:text-3xl">{{ __('Welcome!') }}</h1>
                    <p class="text-sm text-muted-foreground">{{ __('Quick steps to personalise your #100DaysOfCode experience.') }}</p>
                </div>

                <div class="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-primary-foreground">{{ $step }}</span>
                    <span>{{ __('Step :current of :total', ['current' => $step, 'total' => $totalSteps]) }}</span>
                </div>
            </div>

            <ol class="relative flex flex-1 flex-col gap-4 border-l border-border/60 pl-6 lg:max-w-xs">
                @foreach ($stepsMeta as $index => $meta)
                    @php
                        $isCurrent = $index === $step;
                        $isCompleted = $index < $step;
                    @endphp
                    <li class="group">
                        <div class="absolute -left-[13px] mt-1 flex h-6 w-6 items-center justify-center rounded-full border text-xs font-semibold
                            {{ $isCurrent ? 'border-primary bg-primary text-primary-foreground' : ($isCompleted ? 'border-emerald-500 bg-emerald-500/10 text-emerald-600' : 'border-border/70 bg-background text-muted-foreground') }}">
                            {{ $index }}
                        </div>
                        <div class="ml-2 rounded-2xl border border-border/60 bg-card/80 p-3 transition group-hover:border-primary/50">
                            <p class="text-sm font-semibold text-foreground">{{ $meta['label'] }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $meta['description'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="mt-8">
            <form wire:submit.prevent="submit" class="space-y-6">
                <div class="rounded-2xl border border-border/70 bg-background/70 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">{{ __('Current focus') }}</p>
                    <h2 class="mt-2 text-lg font-semibold text-foreground">{{ $stepsMeta[$step]['label'] }}</h2>
                    <p class="text-sm text-muted-foreground">{{ $stepsMeta[$step]['description'] }}</p>
                </div>

                {{ $this->form }}

                <div class="flex flex-col gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <button
                        type="button"
                        wire:click="previous"
                        @disabled($step === 1)
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        class="inline-flex items-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary disabled:cursor-not-allowed disabled:border-border/40 disabled:text-muted-foreground/60"
                    >
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.53 4.47a.75.75 0 010 1.06L8.81 9.25H16a.75.75 0 010 1.5H8.81l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 0z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Back') }}
                    </button>

                    <div class="flex items-center gap-2">
                        @if ($step < $totalSteps)
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-2 text-xs font-semibold text-primary-foreground shadow transition hover:shadow-lg disabled:opacity-70"
                                wire:loading.attr="disabled"
                                wire:target="submit"
                            >
                                <span class="hidden h-3 w-3 animate-spin rounded-full border-2 border-primary-foreground/70 border-t-transparent" wire:loading.class.remove="hidden" wire:target="submit"></span>
                                <span>{{ __('Continue') }}</span>
                            </button>
                        @else
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-6 py-2 text-xs font-semibold text-emerald-50 shadow transition hover:brightness-105 disabled:opacity-70"
                                wire:loading.attr="disabled"
                                wire:target="submit"
                            >
                                <span class="hidden h-3 w-3 animate-spin rounded-full border-2 border-emerald-100 border-t-transparent" wire:loading.class.remove="hidden" wire:target="submit"></span>
                                <span>{{ __('Finish and open my journal') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="rounded-3xl border border-border/60 bg-background/80 p-6 text-sm text-muted-foreground shadow-sm">
        <h2 class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">{{ __('Good to know') }}</h2>
        <ul class="mt-3 list-disc space-y-2 pl-5">
            <li>{{ __('You can adjust these settings later in your preferences.') }}</li>
            <li>{{ __('An active challenge will be created automatically to track your streak.') }}</li>
            <li>{{ __('Once onboarding is complete, you’ll be redirected to the Daily Challenge to log your first day.') }}</li>
        </ul>
    </section>
</div>
