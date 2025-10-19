<div class="mx-auto max-w-6xl space-y-16 px-4 py-12 sm:px-6 lg:px-0">
    <header class="rounded-3xl border border-border/60 bg-gradient-to-br from-primary/10 via-background to-background p-10 shadow-lg">
        <div class="mx-auto max-w-3xl space-y-4 text-center">
            <span class="rounded-full border border-primary/30 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">{{ __('Support Hub') }}</span>
            <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">
                {!! __('Support & feedback center') !!}
            </h1>
            <p class="text-base text-muted-foreground sm:text-lg">
                {{ __('Find quick answers about the challenge, the integrated AI, and how feedback is handled. Need something specific? The form below creates a ticket tracked by the team.') }}
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="#faq" class="inline-flex items-center justify-center rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary">
                    {{ __('View the FAQ') }}
                </a>
                <a href="#feedback" class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30">
                    {{ __('Send feedback') }}
                </a>
            </div>
        </div>
    </header>

    <section id="faq" class="space-y-10">
        <div class="space-y-2">
            <h2 class="text-2xl font-semibold text-foreground sm:text-3xl">{{ __('Frequently asked questions') }}</h2>
            <p class="max-w-2xl text-sm text-muted-foreground sm:text-base">
                {{ __('Answers cover onboarding, daily logs, and support operations. If something is missing, let us know via the form.') }}
            </p>
        </div>

        <div class="space-y-8">
            @foreach ($sections as $section)
                <div class="space-y-4 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-foreground">{{ $section['title'] ?? __('Section') }}</h3>
                    <div class="space-y-3">
                        @foreach (($section['items'] ?? []) as $item)
                            <details class="group rounded-2xl border border-border/50 bg-background/90 p-4 transition hover:border-primary/40">
                                <summary class="flex cursor-pointer items-center justify-between gap-4 text-sm font-semibold text-foreground">
                                    <span>{{ $item['question'] ?? __('Question') }}</span>
                                    <svg class="h-4 w-4 text-muted-foreground transition group-open:rotate-45" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                        <path d="M10 4v12" stroke-width="1.5" stroke-linecap="round" />
                                        <path d="M4 10h12" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </summary>
                                <div class="mt-3 text-sm text-muted-foreground">
                                    {!! nl2br(e($item['answer'] ?? '')) !!}
                                </div>
                            </details>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    @if ($resources->isNotEmpty())
        <section class="space-y-6">
            <div class="space-y-2">
                <h2 class="text-2xl font-semibold text-foreground sm:text-3xl">{{ __('Quick resources') }}</h2>
                <p class="max-w-2xl text-sm text-muted-foreground sm:text-base">
                    {{ __('Guides, checklists, and the public roadmap to follow platform updates.') }}
                </p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($resources as $resource)
                    <a
                        href="{{ $resource['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex flex-col justify-between rounded-3xl border border-border/60 bg-card/80 p-6 shadow-sm transition hover:border-primary/50 hover:shadow-lg"
                    >
                        <div class="space-y-3">
                            <h3 class="text-lg font-semibold text-foreground group-hover:text-primary">{{ $resource['title'] ?? __('Resource') }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $resource['description'] ?? '' }}</p>
                        </div>
                        <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-primary">
                            {{ __('Open') }}
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.293 2.293a1 1 0 011.414 0l4 4a1 1 0 01-.707 1.707H15a1 1 0 110-2h.586L11 3.414V5a1 1 0 11-2 0V2a1 1 0 011-1h3a1 1 0 01.707.293zM5 5a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-2a1 1 0 112 0v2a5 5 0 01-5 5H5a5 5 0 01-5-5V8a5 5 0 015-5h2a1 1 0 110 2H5z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section id="feedback">
        <livewire:support.feedback-form />
    </section>
</div>
