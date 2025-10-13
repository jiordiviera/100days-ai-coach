<section class="relative overflow-hidden rounded-3xl border border-border/60 bg-card/90 p-8 shadow-lg">
    <div class="absolute -left-10 top-10 h-24 w-24 rounded-full bg-primary/15 blur-3xl"></div>
    <div class="absolute -bottom-12 right-6 h-32 w-32 rounded-full bg-secondary/10 blur-3xl"></div>

    <div class="relative grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-4">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">Support</p>
            <h2 class="text-2xl font-semibold text-foreground sm:text-3xl">
                Une question ? Un bug ? Partage ton feedback.
            </h2>
            <p class="max-w-xl text-sm text-muted-foreground sm:text-base">
                Le hub support centralise tes retours et demandes. Notre équipe répond rapidement, et selon le sujet,
                nous pouvons transformer ton message en ticket GitHub.
            </p>
            <div>
                <a
                    href="{{ route('support') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                >
                    Consulter la FAQ
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.293 2.293a1 1 0 011.414 0l4 4a1 1 0 01-.707 1.707H15a1 1 0 110-2h.586L11 3.414V5a1 1 0 11-2 0V2a1 1 0 011-1h3a1 1 0 01.707.293zM5 5a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-2a1 1 0 112 0v2a5 5 0 01-5 5H5a5 5 0 01-5-5V8a5 5 0 015-5h2a1 1 0 110 2H5z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-border/70 bg-background/70 p-4">
                    <h3 class="text-sm font-semibold text-foreground">FAQ & Guides</h3>
                    <p class="mt-1 text-xs text-muted-foreground">Consulte la base de connaissances en construction.</p>
                </div>
                <div class="rounded-2xl border border-border/70 bg-background/70 p-4">
                    <h3 class="text-sm font-semibold text-foreground">Feedback produit</h3>
                    <p class="mt-1 text-xs text-muted-foreground">Propose une idée ou signale un comportement étrange.</p>
                </div>
                <div class="rounded-2xl border border-border/70 bg-background/70 p-4">
                    <h3 class="text-sm font-semibold text-foreground">Issues GitHub</h3>
                    <p class="mt-1 text-xs text-muted-foreground">Pour les points critiques, on crée un ticket public.</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-border/60 bg-background/90 p-6 shadow-inner">
            @if ($submitted)
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-2 text-emerald-500">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M16.704 4.153a.75.75 0 01.143 1.052l-7.5 10a.75.75 0 01-1.118.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 6.97-9.292a.75.75 0 011.051-.168z"
                                  clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">Merci pour ton message !</span>
                    </div>
                    <p class="text-muted-foreground">
                        Nous avons bien reçu ton feedback et t’écrirons rapidement. Tu peux continuer à explorer la
                        plateforme pendant que nous préparons la réponse.
                    </p>
                    <button
                        type="button"
                        wire:click="$set('submitted', false)"
                        class="inline-flex items-center justify-center rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                    >
                        Envoyer un nouveau feedback
                    </button>
                </div>
            @else
                <form wire:submit.prevent="submit" class="space-y-4">
                    {{ $this->form }}
                    <x-filament::button
                        type="submit"
                        class="w-full justify-center"
                    >
                        Envoyer
                    </x-filament::button>
                </form>
            @endif
        </div>
    </div>
</section>
