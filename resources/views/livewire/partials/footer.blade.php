@php
    $isAuthenticated = auth()->check();
    $user = auth()->user();
    $cta = $isAuthenticated
        ? [
            'eyebrow' => 'On garde le rythme',
            'title' => 'Rédige ton log du jour',
            'description' => 'Capture ton apprentissage et garde ta streak vivante.',
            'route' => route('daily-challenge'),
            'label' => 'Ouvrir le Daily Challenge',
        ]
        : [
            'eyebrow' => '#100DaysOfCode',
            'title' => 'Rejoins la communauté des expéditeurs',
            'description' => 'Fixe ton objectif, suis ta progression, partage tes victoires.',
            'route' => Route::has('register') ? route('register') : route('home'),
            'label' => Route::has('register') ? 'Commencer maintenant' : 'Explorer le défi',
        ];

    $ctaSecondary = $isAuthenticated
        ? ['label' => 'Voir mes projets', 'route' => route('projects.index')]
        : ['label' => 'Voir la démo', 'route' => '#how-it-works'];

    $ctaSecondarySupportsNavigate = ! str_starts_with($ctaSecondary['route'], '#');

    $ctaBullets = $isAuthenticated
        ? [
            'Sauvegarde ta streak quotidienne en moins d’une minute.',
            'Active le coach IA pour résumer et partager ton apprentissage.',
            'Débloque des badges à chaque étape clé du run.',
        ]
        : [
            'Planifie tes 100 prochains jours avec un journal guidé.',
            'Reçois rappels et tips IA pour tenir la cadence.',
            'Suis ta progression et décroche des badges visibles.',
        ];

    $navigationSections = [
        [
            'title' => 'Produit',
            'links' => [
                ['label' => 'Accueil', 'route' => route('home'), 'visible' => true],
                ['label' => 'Dashboard', 'route' => route('dashboard'), 'visible' => $isAuthenticated],
                ['label' => 'Daily Challenge', 'route' => route('daily-challenge'), 'visible' => $isAuthenticated],
            ],
        ],
        [
            'title' => 'Parcours',
            'links' => [
                ['label' => 'Challenges', 'route' => route('challenges.index'), 'visible' => $isAuthenticated],
                ['label' => 'Projets', 'route' => route('projects.index'), 'visible' => $isAuthenticated],
                ['label' => '#100DaysOfCode', 'route' => 'https://www.100daysofcode.com/', 'visible' => true, 'external' => true],
            ],
        ],
        [
            'title' => 'Support',
            'links' => [
                ['label' => 'Mentions légales', 'route' => '#', 'visible' => true],
                ['label' => 'Politique de confidentialité', 'route' => '#', 'visible' => true],
                ['label' => 'Contact', 'route' => 'mailto:hello@jiordiviera.me', 'visible' => true, 'external' => true],
            ],
        ],
        [
            'title' => 'Ressources',
            'links' => [
                ['label' => 'GitHub', 'route' => 'https://github.com/jiordiviera/100DaysOfCode', 'visible' => true, 'external' => true],
                ['label' => 'Community hashtag', 'route' => 'https://x.com/hashtag/100DaysOfCode', 'visible' => true, 'external' => true],
                ['label' => 'Docs fil rouge', 'route' => route('home') . '#how-it-works', 'visible' => true],
            ],
        ],
    ];

    $socials = [
        [
            'label' => 'GitHub',
            'url' => 'https://github.com/jiordiviera/100DaysOfCode',
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
          Un coach IA pour tenir la cadence, garder ta streak et célébrer chaque shipment.
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
                  <svg class="h-5 w-5" viewBox="0 0 16 16" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8a8 8 0 005.47 7.59c.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2 .37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82a7.6 7.6 0 012 0c1.53-1.03 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.28.82 2.15 0 3.07-1.87 3.74-3.65 3.94.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8 8 0 0016 8c0-4.42-3.58-8-8-8z" clip-rule="evenodd" />
                  </svg>
                  @break
                @case('x')
                  <svg class="h-5 w-5" viewBox="0 0 1200 1227" fill="currentColor">
                    <path d="M714.163 519.284 1160.89 0h-105.86L667.137 450.887 357.328 0H0l468.492 681.821L0 1226.37h105.866l409.625-476.152 327.181 476.152H1200L714.137 519.284h.026ZM569.165 687.828l-47.468-67.894-377.686-540.24h162.604l304.797 435.991 47.468 67.894 396.2 566.721H892.476L569.165 687.854v-.026Z" />
                  </svg>
                  @break
                @case('linkedin')
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4.98 3.5c0 1.38-1.11 2.5-2.48 2.5A2.49 2.49 0 010 3.5C0 2.12 1.12 1 2.5 1S5 2.12 5 3.5zM.22 23h4.52V7.98H.22V23zM7.55 7.98h4.33v2.05h.06c.6-1.14 2.06-2.35 4.24-2.35 4.53 0 5.36 2.98 5.36 6.86V23h-4.52v-6.55c0-1.56-.03-3.56-2.17-3.56-2.17 0-2.5 1.7-2.5 3.44V23H7.55V7.98z" />
                  </svg>
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
        <p>&copy; {{ now()->year }} {{ config('app.name') }}. Tous droits réservés.</p>
        @if ($isAuthenticated && $user)
          <p class="flex items-center gap-1">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/15 text-[0.7rem] font-semibold text-primary">{{ mb_substr($user->name, 0, 1) }}</span>
            <span>Connecté en tant que <span class="font-semibold text-foreground">{{ $user->name }}</span></span>
          </p>
        @else
          <p>Prêt à commencer ? <a wire:navigate href="{{ route('home') }}" class="font-semibold text-primary">Découvre le défi</a></p>
        @endif
      </div>
    </div>
  </div>
</footer>
