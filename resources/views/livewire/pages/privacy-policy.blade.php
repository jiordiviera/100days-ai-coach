@php
    $legal = config('legal');
    $editor = collect($legal['editor'] ?? []);
    $contactEmail = $editor['email'] ?? null;
    $contactLink = $contactEmail
        ? 'mailto:'.$contactEmail
        : 'https://github.com/jiordiviera/100days-ai-coach/issues';
    $contactLabel = $contactEmail ? $contactEmail : 'GitHub Issues';
    $lastUpdate = $legal['last_update']
        ? \Illuminate\Support\Carbon::parse($legal['last_update'])->locale(app()->getLocale())->isoFormat('LL')
        : now()->locale(app()->getLocale())->isoFormat('LL');
@endphp

<div class="mx-auto max-w-5xl space-y-10 px-4 py-12 sm:px-6 lg:px-0">
    <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-secondary/10 via-background to-background shadow-lg">
        <div class="absolute inset-0">
            <div class="absolute -left-24 bottom-0 h-40 w-40 rounded-full bg-secondary/20 blur-3xl"></div>
            <div class="absolute -right-16 top-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
        </div>
        <div class="relative space-y-6 px-6 py-10 sm:px-10">
            <span class="inline-flex items-center gap-2 rounded-full border border-secondary/40 bg-secondary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-secondary-foreground">
                {{ __('privacy.heading') }}
            </span>
            <div class="space-y-3">
                <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ __('privacy.tagline') }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ __('privacy.last_updated', ['date' => $lastUpdate]) }}
                </p>
            </div>
            <p class="max-w-3xl text-sm text-muted-foreground sm:text-base">
                {!! __('privacy.intro', ['app' => '<strong>'.e(config('app.name')).'</strong>']) !!}
            </p>
        </div>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">{{ __('privacy.controller.title') }}</h2>
        <p class="mt-3 text-sm text-muted-foreground">
            {!! __('privacy.controller.body', ['link' => '<a href="'.$contactLink.'" class="text-primary hover:underline" target="'.($contactEmail ? '_self' : '_blank').'" rel="'.($contactEmail ? '' : 'noopener').'">'.$contactLabel.'</a>']) !!}
        </p>
        <p class="mt-2 text-xs text-muted-foreground">
            {{ __('privacy.controller.note') }}
        </p>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground">{{ __('privacy.collected.title') }}</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                <li>{{ __('privacy.collected.items.account') }}</li>
                <li>{{ __('privacy.collected.items.content') }}</li>
                <li>{{ __('privacy.collected.items.integrations') }}</li>
                <li>{{ __('privacy.collected.items.technical') }}</li>
            </ul>
        </article>

        <article class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground">{{ __('privacy.purposes.title') }}</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                <li>{{ __('privacy.purposes.items.core') }}</li>
                <li>{{ __('privacy.purposes.items.ai') }}</li>
                <li>{{ __('privacy.purposes.items.sync') }}</li>
                <li>{{ __('privacy.purposes.items.maintenance') }}</li>
                <li>{{ __('privacy.purposes.items.support') }}</li>
            </ul>
        </article>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">{{ __('privacy.sharing.title') }}</h2>
        <p class="mt-3 text-sm text-muted-foreground">
            {{ __('privacy.sharing.intro') }}
        </p>
        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
            <li>{{ __('privacy.sharing.items.ai') }}</li>
            <li>{{ __('privacy.sharing.items.wakatime') }}</li>
            <li>{{ __('privacy.sharing.items.hosting') }}</li>
            <li>{{ __('privacy.sharing.items.law') }}</li>
        </ul>
        <p class="mt-4 rounded-2xl border border-border/70 bg-muted/40 px-4 py-3 text-xs text-muted-foreground">
            {{ __('privacy.sharing.warning') }}
        </p>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground">{{ __('privacy.retention.title') }}</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                <li>{{ __('privacy.retention.items.account') }}</li>
                <li>{{ __('privacy.retention.items.logs') }}</li>
                <li>{{ __('privacy.retention.items.tokens') }}</li>
                <li>{{ __('privacy.retention.items.tech') }}</li>
            </ul>
        </article>

        <article class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-foreground">{{ __('privacy.security.title') }}</h2>
            <p class="mt-3 text-sm text-muted-foreground">
                {{ __('privacy.security.body') }}
            </p>
        </article>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">{{ __('privacy.rights.title') }}</h2>
        <p class="mt-3 text-sm text-muted-foreground">
            {{ __('privacy.rights.body') }}
        </p>
    </section>

    <section class="rounded-3xl border border-border/70 bg-card/95 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">{{ __('privacy.changes.title') }}</h2>
        <p class="mt-3 text-sm text-muted-foreground">
            {{ __('privacy.changes.body') }}
        </p>
    </section>
</div>
