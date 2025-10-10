<div>
@if ($visible)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-md">
    <div class="relative w-full max-w-lg space-y-6 rounded-3xl border border-border/60 bg-card/95 p-6 shadow-2xl">
        <button type="button"
                class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-border/60 text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                wire:click="skip">
            <span class="sr-only">Fermer</span>
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M18 6L6 18"></path>
                <path d="M6 6l12 12"></path>
            </svg>
        </button>

        @php
            $step = $steps[$currentStep] ?? null;
            $total = count($steps);
        @endphp

        @if ($step)
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">Guide</p>
                <h2 class="text-xl font-semibold text-foreground">{{ $step['title'] }}</h2>
                <p class="text-sm text-muted-foreground">{{ $step['description'] }}</p>
            </div>

            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">
                <span>Étape {{ $currentStep + 1 }} / {{ $total }}</span>
                <div class="mx-3 h-1 flex-1 rounded-full bg-border">
                    <div class="h-full rounded-full bg-primary transition-all" style="width: {{ (($currentStep + 1) / $total) * 100 }}%"></div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex gap-2">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary disabled:cursor-not-allowed disabled:border-border/40 disabled:text-muted-foreground/60"
                            wire:click="previous" @disabled($currentStep === 0)>
                        Précédent
                    </button>
                    <button type="button"
                            class="inline-flex items-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                            wire:click="skip">
                        Passer
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    @if ($currentStep < $total - 1)
                        <button type="button"
                                class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2 text-xs font-semibold text-primary-foreground shadow transition hover:shadow-lg"
                                wire:click="next">
                            Continuer
                        </button>
                    @else
                        <button type="button"
                                class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold text-emerald-50 shadow transition hover:brightness-105"
                                wire:click="finish">
                            Terminer
                        </button>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between rounded-2xl border border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground">
                <span>{{ $step['action_label'] ?? 'Découvrir' }}</span>
                <button type="button"
                        class="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-3 py-1 font-semibold text-primary transition hover:border-primary/60 hover:bg-primary/20"
                        wire:click="performAction">
                    <span>Aller</span>
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
</div>

    @once
        <script>
            document.addEventListener('livewire:initialized', () => {
                window.addEventListener('tour-scroll-to', event => {
                    const { target } = event.detail || {};
                    if (!target) {
                        return;
                    }
                    const el = document.getElementById(target);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.classList.add('outline', 'outline-2', 'outline-primary');
                        setTimeout(() => el.classList.remove('outline', 'outline-2', 'outline-primary'), 2000);
                    }
                });

                window.addEventListener('tour-open-external', event => {
                    const { url } = event.detail || {};
                    if (url) {
                        window.open(url, '_blank');
                    }
                });
            });
        </script>
    @endonce
@endif
</div>
