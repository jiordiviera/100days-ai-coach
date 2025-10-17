<div class="mx-auto max-w-3xl space-y-10 px-4 py-12 sm:px-6 lg:px-0">
  <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl sm:p-8">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">{{ __('Onboarding') }}</p>
        <h1 class="mt-1 text-2xl font-semibold text-foreground">{{ __('Welcome!') }}</h1>
        <p class="text-sm text-muted-foreground">{{ __('Quick steps to personalise your #100DaysOfCode experience.') }}</p>
      </div>
      <span class="rounded-full border border-primary/40 bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
        {{ __('Step :current of 3', ['current' => $step]) }}
      </span>
    </div>

      <div class="mt-6">
          <form wire:submit.prevent="submit" class="space-y-6">
              {{ $this->form }}

              <div class="flex items-center justify-between pt-2">
                  <button
                      type="button"
                      wire:click="previous"
                      disabled="{{$step === 1}}"
                      class="inline-flex items-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary disabled:cursor-not-allowed disabled:border-border/40 disabled:text-muted-foreground/60"
                  >
                      {{ __('Back') }}
                  </button>

                  <div class="flex items-center gap-2">
                      @if ($step < 3)
                          <button
                              type="submit"
                              class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-2 text-xs font-semibold text-primary-foreground shadow transition hover:shadow-lg"
                          >
                              {{ __('Continue') }}
                          </button>
                      @else
                          <button
                              type="submit"
                              class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-6 py-2 text-xs font-semibold text-emerald-50 shadow transition hover:brightness-105"
                          >
                              {{ __('Finish and open my journal') }}
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
