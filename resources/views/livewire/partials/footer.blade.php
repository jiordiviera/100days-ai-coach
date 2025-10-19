@php
    $isAuthenticated = auth()->check();
    $user = auth()->user();
    $cta = $isAuthenticated
        ? [
            'eyebrow' => __('Stay on pace'),
            'title' => __('Write today’s log'),
            'description' => __('Capture your learning and keep your streak alive.'),
            'route' => route('daily-challenge'),
            'label' => __('Open the Daily Challenge'),
        ]
        : [
            'eyebrow' => '#100DaysOfCode',
            'title' => __('Join the shipping community'),
            'description' => __('Set your focus, track your progress, and celebrate your wins.'),
            'route' => Route::has('register') ? route('register') : route('home'),
            'label' => Route::has('register') ? __('Start now') : __('Explore the challenge'),
        ];

    $ctaSecondary = $isAuthenticated
        ? ['label' => __('View my projects'), 'route' => route('projects.index')]
        : ['label' => __('See the demo'), 'route' => '#how-it-works'];

    $ctaSecondarySupportsNavigate = ! str_starts_with($ctaSecondary['route'], '#');

    $ctaBullets = $isAuthenticated
        ? [
            __('Log your streak in under a minute.'),
            __('Activate the AI coach to summarise and share your learning.'),
            __('Unlock badges at every milestone of your run.'),
        ]
        : [
            __('Plan your next 100 days with a guided journal.'),
            __('Receive reminders and AI tips to keep the pace.'),
            __('Track your progress and unlock visible badges.'),
        ];

    $navigationSections = [
        [
            'title' => __('Product'),
            'links' => [
                ['label' => __('Home'), 'route' => route('home'), 'visible' => true],
                ['label' => __('Dashboard'), 'route' => route('dashboard'), 'visible' => $isAuthenticated],
                ['label' => __('Daily Challenge'), 'route' => route('daily-challenge'), 'visible' => $isAuthenticated],
            ],
        ],
        [
            'title' => __('Journey'),
            'links' => [
                ['label' => __('Challenges'), 'route' => route('challenges.index'), 'visible' => $isAuthenticated],
                ['label' => __('Projects'), 'route' => route('projects.index'), 'visible' => $isAuthenticated],
                ['label' => '#100DaysOfCode', 'route' => config('app.url'), 'visible' => true, 'external' => true],
            ],
        ],
        [
            'title' => __('Support'),
            'links' => [
                ['label' => __('Legal Notice'), 'route' => route('legal.notice'), 'visible' => true],
                ['label' => __('Privacy Policy'), 'route' => route('privacy.policy'), 'visible' => true],
                ['label' => __('Contact'), 'route' => 'mailto:'.(config('legal.editor.email') ?? 'hello@jiordiviera.me'), 'visible' => true, 'external' => true],
            ],
        ],
        [
            'title' => __('Resources'),
            'links' => [
                ['label' => 'GitHub', 'route' => 'https://github.com/jiordiviera/100days-ai-coach', 'visible' => true, 'external' => true],
                ['label' => __('Community hashtag'), 'route' => 'https://x.com/hashtag/100DaysOfCode', 'visible' => true, 'external' => true],
                ['label' => __('Guided docs'), 'route' => route('home') . '#how-it-works', 'visible' => true],
            ],
        ],
    ];

    $socials = [
        [
            'label' => 'GitHub',
            'url' => 'https://github.com/jiordiviera/100days-ai-coach',
            'icon' => 'github',
        ],
        [
            'label' => 'X',
            'url' => 'https://x.com/hashtag/100DaysOfCode',
            'icon' => 'x',
        ],
        [
            'label' => 'LinkedIn',
            'url' => 'https://linkedin.com/in/jiordiviera',
            'icon' => 'linkedin',
        ],
    ];
@endphp

<footer class="border-t border-border/80 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/90">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <section class="relative mt-5 mb-16 overflow-hidden rounded-3xl border border-primary/20 bg-gradient-to-br from-primary/10 via-primary/5 to-transparent p-8 shadow-lg  sm:p-10">
      <div class="max-w-3xl space-y-4">
        <span class="inline-flex items-center rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-primary">{{ $cta['eyebrow'] }}</span>
        <h2 class="text-2xl font-semibold text-foreground sm:text-3xl">{{ $cta['title'] }}</h2>
        <p class="text-sm text-muted-foreground sm:text-base">{{ $cta['description'] }}</p>
        <a
          wire:navigate
          href="{{ $cta['route'] }}"
          class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow transition hover:shadow-md"
        >
          {{ $cta['label'] }}
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
          </svg>
        </a>
      </div>
      <div class="pointer-events-none absolute -right-10 bottom-0 hidden h-36 w-36 rotate-12 rounded-full bg-primary/20 blur-3xl sm:block"></div>
    </section>

    <div class="grid gap-12 pb-12 sm:grid-cols-2 lg:grid-cols-4">
      <div class="space-y-5">
        <div class="flex items-center gap-3">
          <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/15 text-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="h-6 w-6">
              <path d="M4 12C4 7.582 7.582 4 12 4s8 3.582 8 8-3.582 8-8 8" stroke-linecap="round" />
              <path d="M8 12l2.25 2.25L16 8.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </span>
          <div class="flex flex-col">
            <span class="text-xs font-medium uppercase tracking-[0.28em] text-muted-foreground">#100Days</span>
            <span class="text-lg font-semibold text-foreground">{{ config('app.name') }}</span>
          </div>
        </div>
        <p class="text-sm leading-relaxed text-muted-foreground">
          {{ __('An AI coach to keep the pace, maintain your streak, and celebrate every shipment.') }}
        </p>
        <div class="flex items-center gap-3">
          @foreach ($socials as $social)
            <a
              href="{{ $social['url'] }}"
              target="_blank"
              rel="noopener"
              class="flex h-9 w-9 items-center justify-center rounded-full border border-border/70 text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
              <span class="sr-only">{{ $social['label'] }}</span>
              @switch($social['icon'])
                @case('github')
                  <svg class="h-5 w-5" viewBox="0 0 1024 1024" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8C0 11.54 2.29 14.53 5.47 15.59C5.87 15.66 6.02 15.42 6.02 15.21C6.02 15.02 6.01 14.39 6.01 13.72C4 14.09 3.48 13.23 3.32 12.78C3.23 12.55 2.84 11.84 2.5 11.65C2.22 11.5 1.82 11.13 2.49 11.12C3.12 11.11 3.57 11.7 3.72 11.94C4.44 13.15 5.59 12.81 6.05 12.6C6.12 12.08 6.33 11.73 6.56 11.53C4.78 11.33 2.92 10.64 2.92 7.58C2.92 6.71 3.23 5.99 3.74 5.43C3.66 5.23 3.38 4.41 3.82 3.31C3.82 3.31 4.49 3.1 6.02 4.13C6.66 3.95 7.34 3.86 8.02 3.86C8.7 3.86 9.38 3.95 10.02 4.13C11.55 3.09 12.22 3.31 12.22 3.31C12.66 4.41 12.38 5.23 12.3 5.43C12.81 5.99 13.12 6.7 13.12 7.58C13.12 10.65 11.25 11.33 9.47 11.53C9.76 11.78 10.01 12.26 10.01 13.01C10.01 14.08 10 14.94 10 15.21C10 15.42 10.15 15.67 10.55 15.59C13.71 14.53 16 11.53 16 8C16 3.58 12.42 0 8 0Z" transform="scale(64)" fill="#ffff"/></svg>
                  @break
                @case('x')
                  <svg class="h-5 w-5"  fill="none" viewBox="0 0 1200 1227"><path fill="#fff" d="M714.163 519.284 1160.89 0h-105.86L667.137 450.887 357.328 0H0l468.492 681.821L0 1226.37h105.866l409.625-476.152 327.181 476.152H1200L714.137 519.284h.026ZM569.165 687.828l-47.468-67.894-377.686-540.24h162.604l304.797 435.991 47.468 67.894 396.2 566.721H892.476L569.165 687.854v-.026Z"/></svg>
                  @break
                @case('linkedin')
                  <svg class="h-5 w-5" preserveAspectRatio="xMidYMid" viewBox="0 0 256 256"><path d="M218.123 218.127h-37.931v-59.403c0-14.165-.253-32.4-19.728-32.4-19.756 0-22.779 15.434-22.779 31.369v60.43h-37.93V95.967h36.413v16.694h.51a39.907 39.907 0 0 1 35.928-19.733c38.445 0 45.533 25.288 45.533 58.186l-.016 67.013ZM56.955 79.27c-12.157.002-22.014-9.852-22.016-22.009-.002-12.157 9.851-22.014 22.008-22.016 12.157-.003 22.014 9.851 22.016 22.008A22.013 22.013 0 0 1 56.955 79.27m18.966 138.858H37.95V95.967h37.97v122.16ZM237.033.018H18.89C8.58-.098.125 8.161-.001 18.471v219.053c.122 10.315 8.576 18.582 18.89 18.474h218.144c10.336.128 18.823-8.139 18.966-18.474V18.454c-.147-10.33-8.635-18.588-18.966-18.453" fill="#fff"/></svg>
                  @break
              @endswitch
            </a>
          @endforeach
        </div>
      </div>

      @foreach ($navigationSections as $section)
        @php
            $visibleLinks = collect($section['links'])->filter(fn ($link) => $link['visible'])->values();
        @endphp
        @if ($visibleLinks->isNotEmpty())
          <div class="space-y-4">
            <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">{{ $section['title'] }}</h3>
            <ul class="space-y-3 text-sm">
              @foreach ($visibleLinks as $link)
                <li>
                  @if (!empty($link['external']))
                    <a
                      href="{{ $link['route'] }}"
                      target="_blank"
                      rel="noopener"
                      class="text-muted-foreground transition hover:text-primary"
                    >
                      {{ $link['label'] }}
                    </a>
                  @else
                    <a
                      wire:navigate
                      href="{{ $link['route'] }}"
                      class="text-muted-foreground transition hover:text-primary"
                    >
                      {{ $link['label'] }}
                    </a>
                  @endif
                </li>
              @endforeach
            </ul>
          </div>
        @endif
      @endforeach
    </div>

    <div class="border-t border-border/70 py-6">
      <div class="flex flex-col items-center justify-between gap-3 text-xs text-muted-foreground sm:flex-row sm:text-sm">
        <p>&copy; {{ now()->year }} {{ config('app.name') }}. {{ __('All rights reserved.') }} <span class="opacity-75">· {{ __('Hosted on') }} <a href="https://www.hetzner.com" target="_blank" rel="noopener" class="hover:text-primary transition-colors">Hetzner</a></span></p>
        @if ($isAuthenticated && $user)
          <p class="flex items-center gap-1">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/15 text-[0.7rem] font-semibold text-primary">{{ mb_substr($user->name, 0, 1) }}</span>
            <span>{{ __('Signed in as') }} <span class="font-semibold text-foreground">{{ $user->name }}</span></span>
          </p>
        @else
          <p>{{ __('Ready to start?') }} <a wire:navigate href="{{ route('home') }}" class="font-semibold text-primary">{{ __('Discover the challenge') }}</a></p>
        @endif
      </div>
    </div>
  </div>
</footer>
