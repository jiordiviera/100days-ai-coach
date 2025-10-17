@php
    $isAuthenticated = auth()->check();

    $heroCtaPrimary = $isAuthenticated
        ? ['label' => __('Access my dashboard'), 'route' => route('dashboard')]
        : (Route::has('register')
            ? ['label' => __('Start the challenge'), 'route' => route('register')]
            : ['label' => __('Explore the app'), 'route' => route('home')]);

    $heroCtaSecondary = $isAuthenticated
        ? ['label' => __('Open the Daily Challenge'), 'route' => route('daily-challenge')]
        : ['label' => __('Learn how it works'), 'route' => '#how-it-works'];

    $heroHighlights = [
        ['label' => __('Daily tracking'), 'icon' => 'calendar'],
        ['label' => __('Streak & badges'), 'icon' => 'sparkles'],
        ['label' => __('AI coaching'), 'icon' => 'bot'],
    ];

    $howItWorks = [
        [
            'title' => __('01. Define your roadmap'),
            'description' => __('Choose your focus for the next 100 days and bootstrap your first projects. The app provides a clear framework to plan your shipments.'),
            'icon' => 'flag',
        ],
        [
            'title' => __('02. Log every day'),
            'description' => __('Guided entry, AI suggestions, and automatic reminders. One minute is enough to document what you learned.'),
            'icon' => 'pencil',
        ],
        [
            'title' => __('03. Analyse & share'),
            'description' => __('Visualise your progression, unlock badges, and export a weekly recap for your community.'),
            'icon' => 'chart',
        ],
    ];

    $featureGrid = [
        [
            'title' => __('Streak dashboard'),
            'description' => __('A cockpit to visualise your consistency, catch-up days, and completed micro-goals.'),
        ],
        [
            'title' => __('Project & task management'),
            'description' => __('Structure your challenge into actionable missions, assign them to a run, and track progress.'),
        ],
        [
            'title' => __('Invitations & private runs'),
            'description' => __('Join team #100DaysOfCode runs, share a public code, and collaborate on your shipments.'),
        ],
        [
            'title' => __('Integrated AI assistant'),
            'description' => __('Automatically generates punchlines, summaries, and progression plans in your preferred tone.'),
        ],
    ];

    $testimonials = [
        [
            'initials' => 'JV',
            'name' => 'Jiordi Viera',
            'role' => 'Founder & Software Engineer',
            'quote' => __("I have never kept a logbook this long. The reminders and AI help me ship even on busy days."),
        ],
        [
            'initials' => 'CD',
            'name' => 'Claire Deborah',
            'role' => 'Web Developer',
            'quote' => __('The log + project combo helps me truly measure progress. No more “tomorrow”. We ship.'),
        ],
        [
            'initials' => 'DF',
            'name' => 'Darwin Fotso',
            'role' => 'Backend Engineer',
            'quote' => __('We onboarded the whole cohort on the platform. Everyone keeps their rhythm and we share insights at the end of the week.'),
        ],
    ];
@endphp

<div x-data="{ heroVisible: false }" x-init="heroVisible = true" class="space-y-24 pb-24">
  <section class="relative overflow-hidden bg-gradient-to-b from-background via-background to-background/80">
    <div class="absolute inset-0">
      <div class="absolute -top-20 right-20 h-32 w-32 rounded-full bg-primary/10 blur-3xl"></div>
      <div class="absolute bottom-0 left-10 h-40 w-40 rounded-full bg-secondary/10 blur-3xl"></div>
    </div>

    <div class="relative mx-auto grid max-w-7xl gap-12 px-4 py-24 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:px-8">
      <div class="space-y-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-primary">
          <span>#100DaysOfCode</span>
          <span class="h-2 w-2 rounded-full bg-primary"></span>
          <span>{{ __('AI coach assistant') }}</span>
        </div>
        <h1 class="text-4xl font-bold leading-tight text-foreground sm:text-5xl lg:text-6xl">
          {{ __('Ship smarter with your AI coach.') }}
        </h1>
        <p class="max-w-xl text-lg text-muted-foreground">
          {{ __('An intelligent journal to keep your streak, steer your projects, and never lose track of your daily learnings.') }}
        </p>
        <div class="flex flex-wrap gap-4">
          <a
            wire:navigate
            href="{{ $heroCtaPrimary['route'] }}"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
          >
            {{ $heroCtaPrimary['label'] }}
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
            </svg>
          </a>
          <a
            wire:navigate
            href="{{ $heroCtaSecondary['route'] }}"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-6 py-3 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
          >
            {{ $heroCtaSecondary['label'] }}
          </a>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">
          @foreach ($heroHighlights as $highlight)
            <span class="inline-flex items-center gap-2 rounded-full border border-border/70 px-3 py-1">
              @switch($highlight['icon'])
                @case('calendar')
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                    <path d="M16 2v4"></path>
                    <path d="M8 2v4"></path>
                    <path d="M3 10h18"></path>
                  </svg>
                  @break
                @case('sparkles')
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M5 3l2 5 5 2-5 2-2 5-2-5-5-2 5-2z"></path>
                    <path d="M18 9l1 3 3 1-3 1-1 3-1-3-3-1 3-1z"></path>
                  </svg>
                  @break
                @case('bot')
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <rect x="3" y="7" width="18" height="13" rx="2"></rect>
                    <line x1="12" y1="4" x2="12" y2="7"></line>
                    <circle cx="8" cy="13" r="1"></circle>
                    <circle cx="16" cy="13" r="1"></circle>
                  </svg>
                  @break
              @endswitch
              <span>{{ $highlight['label'] }}</span>
            </span>
          @endforeach
        </div>
      </div>

      <div class="relative mx-auto w-full max-w-lg">
        <div class="absolute -left-6 -top-6 h-16 w-16 rounded-full bg-primary/10 blur-2xl"></div>
        <div class="absolute -right-8 -bottom-8 h-20 w-20 rounded-full bg-secondary/20 blur-2xl"></div>
        <div class="relative overflow-hidden rounded-3xl border border-border/60 bg-card/70 shadow-2xl">
          <div class="border-b border-border/60 bg-gradient-to-r from-primary/10 via-primary/5 to-transparent px-6 py-4">
            <span class="text-xs font-semibold uppercase tracking-widest text-primary">{{ __('Today') }}</span>
            <h3 class="mt-1 text-lg font-semibold text-foreground">{{ __('Your log #87 is ready') }}</h3>
            <p class="text-xs text-muted-foreground">{{ __('Write your insight of the day in under 60 seconds.') }}</p>
          </div>
          <div class="space-y-6 px-6 py-8">
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Focus') }}</span>
                <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">{{ __('Shipping') }}</span>
              </div>
              <p class="text-sm text-muted-foreground">{{ __('Extend the tasks API for automated check-ins.') }}</p>
            </div>
            <div class="space-y-3">
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">{{ __('Streak') }}</span>
                <span class="font-semibold text-primary">86 {{ __('days') }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">{{ __('Last shipment') }}</span>
                <span class="font-semibold text-foreground">{{ __('CI/CD automation on main') }}</span>
              </div>
            </div>
            <div class="flex items-center justify-between rounded-2xl border border-border/70 bg-background/70 px-4 py-3">
              <div>
                <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('AI Coach') }}</p>
                <p class="text-sm text-foreground">{{ __('“Need a punchline for today’s log?”') }}</p>
              </div>
              <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path d="M12 12l8-4-8-4-8 4 8 4z"></path>
                  <path d="M4 12l8 4 8-4"></path>
                  <path d="M4 16l8 4 8-4"></path>
                </svg>
              </span>
            </div>
            <button
              type="button"
              class="w-full rounded-full border border-primary/40 bg-primary/10 py-2 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/20"
            >
              {{ __('Preview a log') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="how-it-works" class="mx-auto max-w-6xl space-y-12 px-4 sm:px-6 lg:px-0">
    <div class="flex flex-col gap-3 text-center">
      <span class="self-center rounded-full border border-border/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">{{ __('How it works') }}</span>
      <h2 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ __('A simple routine, a complete framework') }}</h2>
      <p class="mx-auto max-w-3xl text-base text-muted-foreground sm:text-lg">
        {{ __('The app structures your challenge end-to-end: plan, log, analyse. No scattered docs or improvised spreadsheets.') }}
      </p>
    </div>
    <div class="grid gap-8 lg:grid-cols-3">
      @foreach ($howItWorks as $step)
        <div class="flex flex-col gap-4 rounded-3xl border border-border/70 bg-card/80 p-6 shadow-sm transition hover:border-primary/50 hover:shadow-md">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
            @switch($step['icon'])
              @case('flag')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path d="M5 3v18"></path>
                  <path d="M19 5l-7 4 7 4V5z"></path>
                </svg>
                @break
              @case('pencil')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path d="M12 20h9"></path>
                  <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                </svg>
                @break
              @case('chart')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path d="M3 3v18h18"></path>
                  <path d="M7 16l4-4 3 3 6-6"></path>
                </svg>
                @break
            @endswitch
          </div>
          <h3 class="text-lg font-semibold text-foreground">{{ $step['title'] }}</h3>
          <p class="text-sm text-muted-foreground">{{ $step['description'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <section class="mx-auto max-w-6xl space-y-10 px-4 sm:px-6 lg:px-0">
    <div class="flex flex-col gap-3 text-center">
      <span class="self-center rounded-full border border-border/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">{{ __('Key features') }}</span>
      <h2 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ __('A digital coach built for makers') }}</h2>
      <p class="mx-auto max-w-3xl text-base text-muted-foreground sm:text-lg">
        {{ __('Tools designed to prioritise action: focus on shipping while the app takes care of the rest.') }}
      </p>
    </div>
    <div class="grid gap-6 md:grid-cols-2">
      @foreach ($featureGrid as $feature)
        <div class="flex flex-col gap-3 rounded-3xl border border-border/70 bg-card/80 p-6 shadow-sm transition hover:border-primary/50 hover:shadow-md">
          <h3 class="text-xl font-semibold text-foreground">{{ $feature['title'] }}</h3>
          <p class="text-sm text-muted-foreground">{{ $feature['description'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <section class="mx-auto max-w-6xl space-y-10 px-4 sm:px-6 lg:px-0">
    <div class="flex flex-col gap-3 text-center">
      <span class="self-center rounded-full border border-border/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">{{ __('Loved by makers') }}</span>
      <h2 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ __('They keep their streak with the app') }}</h2>
      <p class="mx-auto max-w-3xl text-base text-muted-foreground sm:text-lg">
        {{ __('Developers, makers, and mentors documenting every learning and sharing their insights.') }}
      </p>
    </div>
    <div class="grid gap-6 lg:grid-cols-3">
      @foreach ($testimonials as $testimonial)
        <figure class="flex h-full flex-col justify-between rounded-3xl border border-border/70 bg-card/80 p-6 text-left shadow-sm transition hover:border-primary/50 hover:shadow-md">
          <blockquote class="text-sm text-muted-foreground">“{{ $testimonial['quote'] }}”</blockquote>
          <figcaption class="mt-6 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/15 text-sm font-semibold text-primary">{{ $testimonial['initials'] }}</span>
            <span>
              <span class="block text-sm font-semibold text-foreground">{{ $testimonial['name'] }}</span>
              <span class="block text-xs uppercase tracking-widest text-muted-foreground">{{ $testimonial['role'] }}</span>
            </span>
          </figcaption>
        </figure>
      @endforeach
    </div>
  </section>

  <section id="support" class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-0">
    <livewire:support.feedback-form />
  </section>

  <section class="mx-auto max-w-5xl rounded-3xl border border-primary/30 bg-gradient-to-r from-primary/10 via-primary/5 to-transparent px-6 py-12 text-center shadow-lg sm:px-10">
    <div class="flex flex-col items-center gap-6">
      <span class="rounded-full border border-primary/30 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">{{ __('Ready for day 01?') }}</span>
      <h2 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ __('We start together tonight') }}</h2>
      <p class="max-w-2xl text-base text-muted-foreground sm:text-lg">
        {{ __('Sign up, set your focus, the app creates your first log and schedules a reminder. The next 100 shipments are yours.') }}
      </p>
      <div class="flex flex-wrap justify-center gap-4">
        <a
          wire:navigate
          href="{{ $heroCtaPrimary['route'] }}"
          class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
        >
          {{ $heroCtaPrimary['label'] }}
        </a>
        <a
          wire:navigate
          href="{{ $heroCtaSecondary['route'] }}"
          class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-6 py-3 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
        >
          {{ $heroCtaSecondary['label'] }}
        </a>
      </div>
    </div>
  </section>
</div>
