@php
    $legal = config('legal');
    $editor = collect($legal['editor'] ?? []);
    $hosting = collect($legal['hosting'] ?? []);
    $hasEditorData = $editor->filter(fn ($value) => filled($value))->isNotEmpty();
    $hasHostingData = $hosting->filter(fn ($value) => filled($value))->isNotEmpty();
    $lastUpdate = $legal['last_update']
        ? \Illuminate\Support\Carbon::parse($legal['last_update'])->locale(app()->getLocale())->isoFormat('LL')
        : now()->locale(app()->getLocale())->isoFormat('LL');
@endphp

<div class="mx-auto max-w-5xl space-y-10 px-4 py-12 sm:px-6 lg:px-0">
    <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
        <div class="absolute inset-0">
            <div class="absolute -left-24 top-12 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
            <div class="absolute -right-20 bottom-12 h-40 w-40 rounded-full bg-secondary/15 blur-3xl"></div>
        </div>
        <div class="relative space-y-6 px-6 py-10 sm:px-10">
            <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
                {{ __('legal.heading') }}
            </span>
            <div class="space-y-3">
                <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ config('app.name') }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ __('legal.last_updated', ['date' => $lastUpdate]) }}
                </p>
            </div>
            <p class="max-w-3xl text-sm text-muted-foreground sm:text-base">
                {{ __('legal.intro') }}
            </p>
        </div>
    </section>

    @unless ($hasEditorData && $hasHostingData)
        <section class="rounded-3xl border border-amber-400/60 bg-amber-50/80 p-5 text-sm text-amber-900 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-[0.24em] text-amber-700">{{ __('legal.warning_title') }}</h2>
            <p class="mt-2">{!! __('legal.warning_body') !!}</p>
        </section>
    @endunless

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground">{{ __('legal.editor.title') }}</h2>
            <p class="mt-3 text-sm text-muted-foreground">
                {!! __('legal.editor.subtitle', ['app' => '<strong>'.e(config('app.name')).'</strong>']) !!}
            </p>
            <dl class="mt-4 space-y-2 text-sm text-muted-foreground">
                <div class="flex justify-between gap-4">
                    <dt class="font-medium text-foreground">{{ __('legal.editor.fields.name') }}</dt>
                    <dd class="text-right">{{ $editor['company'] ?? $editor['name'] ?? 'À compléter' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-medium text-foreground">{{ __('legal.editor.fields.address') }}</dt>
                    <dd class="text-right">
                        @if ($editor['address'] ?? false)
                            {{ $editor['address'] }}<br/>
                            {{ trim(($editor['postal_code'] ?? '').' '.($editor['city'] ?? '')) }}<br/>
                            {{ $editor['country'] ?? '' }}
                        @else
                            À compléter
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-medium text-foreground">{{ __('legal.editor.fields.contact') }}</dt>
                    <dd class="text-right">
                        @if ($editor['email'] ?? false)
                            <a href="mailto:{{ $editor['email'] }}" class="text-primary hover:underline">{{ $editor['email'] }}</a>
                            @if ($editor['phone'] ?? false)
                                <br/>{{ $editor['phone'] }}
                            @endif
                        @else
                            {!! __('legal.editor.contact_fallback', ['link' => '<a href="https://github.com/jiordiviera/100days-ai-coach/issues" class="text-primary hover:underline" target="_blank" rel="noopener">GitHub</a>']) !!}
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-medium text-foreground">{{ __('legal.editor.fields.publication_director') }}</dt>
                    <dd class="text-right">{{ $editor['publication_director'] ?? ($editor['name'] ?? 'À préciser') }}</dd>
                </div>
            </dl>
        </article>

        <article class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground">{{ __('legal.hosting.title') }}</h2>
            <p class="mt-3 text-sm text-muted-foreground">{{ __('legal.hosting.subtitle') }}</p>
            @if ($hasHostingData)
                <dl class="mt-4 space-y-2 text-sm text-muted-foreground">
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-foreground">{{ __('legal.editor.fields.name') }}</dt>
                        <dd class="text-right">{{ $hosting['name'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-foreground">{{ __('legal.editor.fields.address') }}</dt>
                        <dd class="text-right">
                            {{ $hosting['address'] }}<br/>
                            {{ trim(($hosting['postal_code'] ?? '').' '.($hosting['city'] ?? '')) }}<br/>
                            {{ $hosting['country'] }}
                        </dd>
                    </div>
                </dl>
            @else
                <p class="mt-4 rounded-2xl border border-border/70 bg-muted/40 px-4 py-3 text-sm text-muted-foreground">
                    {!! __('legal.hosting.missing') !!}
                </p>
            @endif

            <p class="mt-6 text-xs text-muted-foreground">
                {{ __('legal.hosting.local_note') }}
            </p>
        </article>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground">{{ __('legal.ip.title') }}</h2>
            <p class="mt-3 text-sm text-muted-foreground">
                {{ __('legal.ip.body') }}
            </p>
        </article>

        <article class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground">{{ __('legal.data.title') }}</h2>
            <p class="mt-3 text-sm text-muted-foreground">
                {!! __('legal.data.body', ['link' => '<a href="'.route('privacy.policy').'" wire:navigate class="text-primary hover:underline">'.__('privacy.heading').'</a>']) !!}
            </p>
        </article>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">{{ __('legal.law.title') }}</h2>
        <p class="mt-3 text-sm text-muted-foreground">
            {{ __('legal.law.body') }}
        </p>
    </section>
</div>
