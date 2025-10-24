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
            'icon' => 'dashboard',
        ],
        [
            'title' => __('Project & task management'),
            'description' => __('Structure your challenge into actionable missions, assign them to a run, and track progress.'),
            'icon' => 'tasks',
        ],
        [
            'title' => __('Invitations & private runs'),
            'description' => __('Join team #100DaysOfCode runs, share a public code, and collaborate on your shipments.'),
            'icon' => 'users',
        ],
        [
            'title' => __('Integrated AI assistant'),
            'description' => __('Automatically generates punchlines, summaries, and progression plans in your preferred tone.'),
            'icon' => 'ai',
        ],
    ];

    $testimonials = [
        [
            'initials' => 'JV',
            'name' => 'Jiordi Viera',
            'role' => __('Founder & Software Engineer'),
            'quote' => __("I have never kept a logbook this long. The reminders and AI help me ship even on busy days."),
        ],
        [
            'initials' => 'CD',
            'name' => 'Claire Deborah',
            'role' => __('Web Developer'),
            'quote' => __('The log + project combo helps me truly measure progress. No more "tomorrow". We ship.'),
        ],
        [
            'initials' => 'DF',
            'name' => 'Darwin Fotso',
            'role' => __('Backend Engineer'),
            'quote' => __('We onboarded the whole cohort on the platform. Everyone keeps their rhythm and we share insights at the end of the week.'),
        ],
    ];

    $stats = [
        ['value' => '10,000+', 'label' => __('Daily logs')],
        ['value' => '87%', 'label' => __('Completion rate')],
        ['value' => '2,500+', 'label' => __('Active makers')],
        ['value' => '100', 'label' => __('Days to ship')],
    ];
@endphp

<div x-data="{ heroVisible: false }" x-init="setTimeout(() => heroVisible = true, 100)" class="space-y-32 pb-32">
  {{-- Hero Section --}}
  <section class="relative overflow-hidden bg-linear-to-b from-background via-background to-background/80">
    {{-- Animated background gradients --}}
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute -top-24 right-20 h-96 w-96 rounded-full bg-primary/5 blur-3xl animate-pulse"></div>
      <div class="absolute bottom-0 left-10 h-80 w-80 rounded-full bg-secondary/5 blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
      <div class="absolute top-1/2 left-1/3 h-64 w-64 rounded-full bg-primary/3 blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative mx-auto grid max-w-7xl gap-16 px-4 py-20 sm:px-6 sm:py-28 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:px-8">
      <div 
        class="space-y-8 transition-all duration-1000"
        x-bind:class="heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
      >
        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary backdrop-blur-sm">
          <span>#100DaysOfCode</span>
          <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
          </span>
          <span>{{ __('AI coach assistant') }}</span>
        </div>

        {{-- Main heading --}}
        <h1 class="text-4xl font-bold leading-tight text-foreground sm:text-5xl lg:text-6xl xl:text-7xl">
          {{ __('Ship smarter with your') }}
          <span class="relative inline-block">
            <span class="relative z-10 bg-linear-to-r from-primary to-primary/70 bg-clip-text text-transparent">{{ __('AI coach') }}</span>
            <span class="absolute -bottom-2 left-0 h-3 w-full bg-primary/20 blur-sm"></span>
          </span>
        </h1>

        {{-- Subtitle --}}
        <p class="max-w-xl text-lg leading-relaxed text-muted-foreground sm:text-xl">
          {{ __('An intelligent journal to keep your streak, steer your projects, and never lose track of your daily learnings.') }}
        </p>

        {{-- CTA Buttons --}}
        <div class="flex flex-wrap gap-4">
          <a
            wire:navigate
            href="{{ $heroCtaPrimary['route'] }}"
            class="group inline-flex items-center justify-center gap-2 rounded-full bg-primary px-8 py-4 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-primary/40"
          >
            {{ $heroCtaPrimary['label'] }}
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
            </svg>
          </a>
          <a
            wire:navigate
            href="{{ $heroCtaSecondary['route'] }}"
            class="group inline-flex items-center justify-center gap-2 rounded-full border-2 border-border/70 bg-background/50 px-8 py-4 text-sm font-semibold text-foreground backdrop-blur-sm transition-all hover:border-primary/50 hover:bg-primary/5"
          >
            {{ $heroCtaSecondary['label'] }}
          </a>
        </div>

        {{-- Highlights --}}
        <div class="flex flex-wrap items-center gap-3">
          @foreach ($heroHighlights as $highlight)
            <span class="group inline-flex items-center gap-2 rounded-full border border-border/70 bg-card/50 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground backdrop-blur-sm transition-all hover:border-primary/50 hover:text-primary hover:shadow-md">
              @switch($highlight['icon'])
                @case('calendar')
                  <svg class="h-4 w-4 transition-transform group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                  </svg>
                  @break
                @case('sparkles')
                  <svg class="h-4 w-4 transition-transform group-hover:rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 3l2 5 5 2-5 2-2 5-2-5-5-2 5-2zM18 9l1 3 3 1-3 1-1 3-1-3-3-1 3-1z"></path>
                  </svg>
                  @break
                @case('bot')
                  <svg class="h-4 w-4 transition-transform group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

      {{-- Hero Card --}}
      <div 
        class="relative mx-auto w-full max-w-lg transition-all duration-1000 delay-300"
        x-bind:class="heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
      >
        <div class="absolute -left-8 -top-8 h-20 w-20 rounded-full bg-primary/10 blur-2xl animate-pulse"></div>
        <div class="absolute -right-10 -bottom-10 h-24 w-24 rounded-full bg-secondary/15 blur-2xl animate-pulse" style="animation-delay: 1.5s;"></div>
        
        <div class="relative overflow-hidden rounded-3xl border border-border/60 bg-card/80 shadow-2xl backdrop-blur-sm transition-all hover:shadow-primary/10 hover:shadow-3xl">
          {{-- Card Header --}}
          <div class="border-b border-border/60 bg-linear-to-r from-primary/10 via-primary/5 to-transparent px-6 py-5">
            <div class="flex items-center justify-between">
              <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-primary">
                <span class="relative flex h-2 w-2">
                  <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"></span>
                  <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
                </span>
                {{ __('Today') }}
              </span>
              <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">#87</span>
            </div>
            <h3 class="mt-2 text-lg font-semibold text-foreground">{{ __('Your log is ready') }}</h3>
            <p class="mt-1 text-xs text-muted-foreground">{{ __('Write your insight of the day in under 60 seconds.') }}</p>
          </div>

          {{-- Card Content --}}
          <div class="space-y-6 px-6 py-8">
            {{-- Focus Section --}}
            <div class="space-y-2 rounded-2xl border border-border/50 bg-background/50 p-4">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">{{ __('Focus') }}</span>
                <span class="rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold text-primary">{{ __('Shipping') }}</span>
              </div>
              <p class="text-sm font-medium text-foreground">{{ __('Extend the tasks API for automated check-ins.') }}</p>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 gap-4">
              <div class="rounded-xl border border-border/50 bg-background/50 p-4">
                <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Streak') }}</p>
                <p class="mt-1 text-2xl font-bold text-primary">86</p>
                <p class="text-xs text-muted-foreground">{{ __('days') }}</p>
              </div>
              <div class="rounded-xl border border-border/50 bg-background/50 p-4">
                <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Progress') }}</p>
                <p class="mt-1 text-2xl font-bold text-foreground">87%</p>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                  <div class="h-full w-[87%] rounded-full bg-linear-to-r from-primary to-primary/70"></div>
                </div>
              </div>
            </div>

            {{-- Last Shipment --}}
            <div class="flex items-center gap-3 rounded-xl border border-border/50 bg-background/50 p-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                <svg class="h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-xs uppercase tracking-widest text-muted-foreground">{{ __('Last shipment') }}</p>
                <p class="truncate text-sm font-medium text-foreground">{{ __('CI/CD automation on main') }}</p>
              </div>
            </div>

            {{-- AI Coach --}}
            <div class="flex items-center justify-between rounded-2xl border border-primary/20 bg-linear-to-r from-primary/10 to-primary/5 p-4">
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 12l8-4-8-4-8 4 8 4zM4 12l8 4 8-4M4 16l8 4 8-4"></path>
                  </svg>
                  <p class="text-xs font-semibold uppercase tracking-widest text-primary">{{ __('AI Coach') }}</p>
                </div>
                <p class="mt-1 text-sm text-foreground">{{ __('Need a punchline for todays log?') }}</p>
              </div>
              <button class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary transition-all hover:bg-primary/25 hover:scale-110">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M5 3l14 9-14 9V3z"></path>
                </svg>
              </button>
            </div>

            {{-- CTA Button --}}
            <button
              type="button"
              class="group w-full rounded-full border-2 border-primary/40 bg-primary/10 py-3 text-sm font-semibold text-primary transition-all hover:border-primary hover:bg-primary/20 hover:shadow-lg hover:shadow-primary/20"
            >
              <span class="flex items-center justify-center gap-2">
                {{ __('Preview a log') }}
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                </svg>
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Stats Section --}}
  {{-- <section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($stats as $stat)
        <div class="group relative overflow-hidden rounded-2xl border border-border/70 bg-card/80 p-6 text-center backdrop-blur-sm transition-all hover:border-primary/50 hover:shadow-lg">
          <div class="absolute inset-0 bg-linear-to-br from-primary/5 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
          <p class="relative text-4xl font-bold text-primary">{{ $stat['value'] }}</p>
          <p class="relative mt-2 text-sm uppercase tracking-widest text-muted-foreground">{{ $stat['label'] }}</p>
        </div>
      @endforeach
    </div>
  </section> --}}

  {{-- How It Works Section --}}
  <section id="how-it-works" class="mx-auto max-w-6xl space-y-16 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 text-center">
      <span class="self-center rounded-full border border-border/60 bg-card/50 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground backdrop-blur-sm">{{ __('How it works') }}</span>
      <h2 class="text-3xl font-bold text-foreground sm:text-4xl lg:text-5xl">{{ __('A simple routine, a complete framework') }}</h2>
      <p class="mx-auto max-w-3xl text-lg text-muted-foreground">
        {{ __('The app structures your challenge end-to-end: plan, log, analyse. No scattered docs or improvised spreadsheets.') }}
      </p>
    </div>
    
    <div class="grid gap-8 lg:grid-cols-3">
      @foreach ($howItWorks as $index => $step)
        <div class="group relative flex flex-col gap-5 rounded-3xl border border-border/70 bg-card/80 p-8 backdrop-blur-sm transition-all hover:border-primary/50 hover:shadow-xl hover:shadow-primary/5">
          {{-- Connector Line --}}
          @if ($index < 2)
            <div class="absolute -right-4 top-1/2 hidden h-0.5 w-8 bg-linear-to-r from-border/70 to-transparent lg:block"></div>
          @endif
          
          <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-linear-to-br from-primary/20 to-primary/10 text-primary ring-4 ring-primary/10 transition-all group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-primary/20">
            @switch($step['icon'])
              @case('flag')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M5 3v18M19 5l-7 4 7 4V5z"></path>
                </svg>
                @break
              @case('pencil')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                </svg>
                @break
              @case('chart')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 3v18h18M7 16l4-4 3 3 6-6"></path>
                </svg>
                @break
            @endswitch
          </div>
          <h3 class="text-xl font-bold text-foreground">{{ $step['title'] }}</h3>
          <p class="text-sm leading-relaxed text-muted-foreground">{{ $step['description'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- Features Section --}}
  <section class="mx-auto max-w-6xl space-y-16 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 text-center">
      <span class="self-center rounded-full border border-border/60 bg-card/50 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground backdrop-blur-sm">{{ __('Key features') }}</span>
      <h2 class="text-3xl font-bold text-foreground sm:text-4xl lg:text-5xl">{{ __('A digital coach built for makers') }}</h2>
      <p class="mx-auto max-w-3xl text-lg text-muted-foreground">
        {{ __('Tools designed to prioritise action: focus on shipping while the app takes care of the rest.') }}
      </p>
    </div>
    
    <div class="grid gap-6 md:grid-cols-2">
      @foreach ($featureGrid as $feature)
        <div class="group flex flex-col gap-4 rounded-3xl border border-border/70 bg-card/80 p-8 backdrop-blur-sm transition-all hover:border-primary/50 hover:shadow-xl hover:shadow-primary/5">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary transition-all group-hover:scale-110 group-hover:bg-primary/20">
            @switch($feature['icon'])
              @case('dashboard')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="7" height="7"></rect>
                  <rect x="14" y="3" width="7" height="7"></rect>
                  <rect x="14" y="14" width="7" height="7"></rect>
                  <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                @break
              @case('tasks')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 11l3 3L22 4"></path>
                  <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                </svg>
                @break
              @case('users')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"></path>
                </svg>
                @break
              @case('ai')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                </svg>
                @break
            @endswitch
          </div>
          <h3 class="text-xl font-bold text-foreground">{{ $feature['title'] }}</h3>
          <p class="text-sm leading-relaxed text-muted-foreground">{{ $feature['description'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  {{-- Testimonials Section --}}
  <section class="mx-auto max-w-6xl space-y-16 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 text-center">
      <span class="self-center rounded-full border border-border/60 bg-card/50 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground backdrop-blur-sm">{{ __('Loved by makers') }}</span>
      <h2 class="text-3xl font-bold text-foreground sm:text-4xl lg:text-5xl">{{ __('They keep their streak with the app') }}</h2>
      <p class="mx-auto max-w-3xl text-lg text-muted-foreground">
        {{ __('Developers, makers, and mentors documenting every learning and sharing their insights.') }}
      </p>
    </div>
    
    <div class="grid gap-6 lg:grid-cols-3">
      @foreach ($testimonials as $testimonial)
        <figure class="group flex h-full flex-col justify-between rounded-3xl border border-border/70 bg-card/80 p-8 backdrop-blur-sm transition-all hover:border-primary/50 hover:shadow-xl hover:shadow-primary/5">
          <div class="mb-6">
            <svg class="h-8 w-8 text-primary/30" viewBox="0 0 24 24" fill="currentColor">
              <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"></path>
            </svg>
            <blockquote class="mt-4 text-base leading-relaxed text-muted-foreground">"{{ $testimonial['quote'] }}"</blockquote>
          </div>
          <figcaption class="flex items-center gap-4 border-t border-border/50 pt-6">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary/20 to-primary/10 text-base font-bold text-primary ring-4 ring-primary/10">{{ $testimonial['initials'] }}</span>
            <div>
              <span class="block text-sm font-semibold text-foreground">{{ $testimonial['name'] }}</span>
              <span class="block text-xs uppercase tracking-widest text-muted-foreground">{{ $testimonial['role'] }}</span>
            </div>
          </figcaption>
        </figure>
      @endforeach
    </div>
  </section>

  {{-- Support Section --}}
  <section id="support" class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
    <livewire:support.feedback-form />
  </section>

  {{-- Final CTA Section --}}
  <section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="relative overflow-hidden rounded-3xl border border-primary/30 bg-linear-to-br from-primary/10 via-primary/5 to-transparent p-12 shadow-2xl backdrop-blur-sm sm:p-16">
      {{-- Background decoration --}}
      <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-primary/10 blur-3xl"></div>
      <div class="absolute -bottom-16 -left-16 h-32 w-32 rounded-full bg-secondary/10 blur-3xl"></div>
      
      <div class="relative flex flex-col items-center gap-8 text-center">
        <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.28em] text-primary backdrop-blur-sm">
          <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
          </span>
          {{ __('Ready for day 01?') }}
        </span>
        
        <h2 class="text-3xl font-bold text-foreground sm:text-4xl lg:text-5xl">
          {{ __('We start together tonight') }}
        </h2>
        
        <p class="max-w-2xl text-lg leading-relaxed text-muted-foreground">
          {{ __('Sign up, set your focus, the app creates your first log and schedules a reminder. The next 100 shipments are yours.') }}
        </p>
        
        <div class="flex flex-wrap justify-center gap-4">
          <a
            wire:navigate
            href="{{ $heroCtaPrimary['route'] }}"
            class="group inline-flex items-center justify-center gap-2 rounded-full bg-primary px-8 py-4 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-primary/40"
          >
            {{ $heroCtaPrimary['label'] }}
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
            </svg>
          </a>
          <a
            wire:navigate
            href="{{ $heroCtaSecondary['route'] }}"
            class="group inline-flex items-center justify-center gap-2 rounded-full border-2 border-border/70 bg-background/50 px-8 py-4 text-sm font-semibold text-foreground backdrop-blur-sm transition-all hover:border-primary/50 hover:bg-primary/5"
          >
            {{ $heroCtaSecondary['label'] }}
          </a>
        </div>
        
        {{-- Trust indicators --}}
        <div class="mt-4 flex flex-wrap items-center justify-center gap-6 text-sm text-muted-foreground">
          <span class="flex items-center gap-2">
            <svg class="h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            {{ __('Free to start') }}
          </span>
          <span class="flex items-center gap-2">
            <svg class="h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            {{ __('No credit card') }}
          </span>
          <span class="flex items-center gap-2">
            <svg class="h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            {{ __('Cancel anytime') }}
          </span>
        </div>
      </div>
    </div>
  </section>
</div>