<section class="relative overflow-hidden rounded-3xl border border-border/60 bg-card/90 p-8 shadow-2xl backdrop-blur-sm sm:p-12">
    {{-- Background decorations --}}
    <div class="absolute -left-10 top-10 h-24 w-24 rounded-full bg-primary/15 blur-3xl animate-pulse"></div>
    <div class="absolute -bottom-12 right-6 h-32 w-32 rounded-full bg-secondary/10 blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    <div class="absolute top-1/2 left-1/2 h-20 w-20 rounded-full bg-primary/5 blur-3xl animate-pulse" style="animation-delay: 2s;"></div>

    <div class="relative grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-start">
        {{-- Left Column: Content --}}
        <div class="space-y-8">
            {{-- Header --}}
            <div class="space-y-4">
                <span class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-card/50 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground backdrop-blur-sm">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path>
                    </svg>
                    {{ __('Support') }}
                </span>
                <h2 class="text-3xl font-bold leading-tight text-foreground sm:text-4xl">
                    {{ __('Questions, bugs, or ideas?') }}
                    <span class="block text-primary">{{ __('Share your feedback.') }}</span>
                </h2>
                <p class="max-w-xl text-base leading-relaxed text-muted-foreground">
                    {{ __('The support hub centralises your feedback. We reply quickly and, when needed, turn your message into a GitHub ticket.') }}
                </p>
            </div>

            {{-- CTA Link --}}
            <div>
                <a href="{{ route('support') }}" wire:navigate
                    class="group inline-flex items-center gap-2 rounded-full border-2 border-primary/30 bg-primary/5 px-5 py-2.5 text-sm font-semibold text-primary transition-all hover:border-primary/50 hover:bg-primary/10 hover:shadow-lg hover:shadow-primary/20">
                    {{ __('Browse the FAQ') }}
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            {{-- Feature Cards --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="group rounded-2xl border border-border/70 bg-background/70 p-5 backdrop-blur-sm transition-all hover:border-primary/50 hover:shadow-lg hover:shadow-primary/5">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary transition-all group-hover:scale-110 group-hover:bg-primary/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-foreground">{{ __('FAQ & Guides') }}</h3>
                    <p class="mt-2 text-xs leading-relaxed text-muted-foreground">{{ __('Explore the growing knowledge base.') }}</p>
                </div>

                <div class="group rounded-2xl border border-border/70 bg-background/70 p-5 backdrop-blur-sm transition-all hover:border-primary/50 hover:shadow-lg hover:shadow-primary/5">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary transition-all group-hover:scale-110 group-hover:bg-primary/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-foreground">{{ __('Product feedback') }}</h3>
                    <p class="mt-2 text-xs leading-relaxed text-muted-foreground">
                        {{ __('Suggest an idea or report unexpected behaviour.') }}</p>
                </div>

                <div class="group rounded-2xl border border-border/70 bg-background/70 p-5 backdrop-blur-sm transition-all hover:border-primary/50 hover:shadow-lg hover:shadow-primary/5">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary transition-all group-hover:scale-110 group-hover:bg-primary/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-foreground">{{ __('GitHub issues') }}</h3>
                    <p class="mt-2 text-xs leading-relaxed text-muted-foreground">
                        {{ __('For critical topics, we open a public ticket.') }}</p>
                </div>
            </div>

            {{-- Stats or Trust Indicators --}}
            <div class="flex flex-wrap items-center gap-6 border-t border-border/50 pt-6 text-sm">
                <div class="flex items-center gap-2 text-muted-foreground">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                        <svg class="h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                        </svg>
                    </div>
                    <span class="font-medium">{{ __('Quick response') }}</span>
                </div>
                <div class="flex items-center gap-2 text-muted-foreground">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                        <svg class="h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <span class="font-medium">{{ __('Secure & private') }}</span>
                </div>
                <div class="flex items-center gap-2 text-muted-foreground">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                        <svg class="h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="font-medium">{{ __('Issue tracking') }}</span>
                </div>
            </div>
        </div>

        {{-- Right Column: Form --}}
        <div class="rounded-3xl border border-border/60 bg-background/95 p-6 shadow-xl backdrop-blur-sm sm:p-8">
            @if ($submitted)
                <div class="space-y-6 text-center">
                    {{-- Success Icon --}}
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500/20 to-emerald-500/10 ring-4 ring-emerald-500/10">
                        <svg class="h-8 w-8 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.704 4.153a.75.75 0 01.143 1.052l-7.5 10a.75.75 0 01-1.118.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 6.97-9.292a.75.75 0 011.051-.168z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>

                    {{-- Success Message --}}
                    <div class="space-y-3">
                        <h3 class="text-xl font-bold text-foreground">{{ __('Thanks for your message!') }}</h3>
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            {{ __('We received your feedback and will get back to you shortly. Feel free to keep exploring the platform in the meantime.') }}
                        </p>
                    </div>

                    {{-- Action Button --}}
                    <button type="button" wire:click="$set('submitted', false)"
                        class="group inline-flex items-center justify-center gap-2 rounded-full border-2 border-border/70 bg-background px-6 py-3 text-sm font-semibold text-foreground transition-all hover:border-primary/50 hover:bg-primary/5 hover:text-primary">
                        <svg class="h-4 w-4 transition-transform group-hover:-rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                        {{ __('Send another feedback') }}
                    </button>
                </div>
            @else
                <div class="space-y-6">
                    {{-- Form Header --}}
                    <div class="space-y-2 border-b border-border/50 pb-4">
                        <h3 class="text-lg font-bold text-foreground">{{ __('Send us a message') }}</h3>
                        <p class="text-xs text-muted-foreground">{{ __('We typically respond within 24 hours') }}</p>
                    </div>

                    {{-- Form --}}
                    <form wire:submit.prevent="submit" class="space-y-5">
                        {{ $this->form }}
                        
                        <x-filament::button 
                            type="submit" 
                            class="w-full justify-center !bg-primary !text-primary-foreground hover:!shadow-lg hover:!shadow-primary/25 transition-all hover:!scale-[1.02]"
                        >
                            <span class="flex items-center gap-2">
                                {{ __('Send') }}
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </span>
                        </x-filament::button>
                    </form>

                    {{-- Footer Note --}}
                    <div class="flex items-start gap-2 rounded-xl border border-border/50 bg-muted/30 p-3 text-xs text-muted-foreground">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <p class="leading-relaxed">
                            {{ __('Your feedback helps us improve. Critical issues may be tracked publicly on GitHub.') }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>