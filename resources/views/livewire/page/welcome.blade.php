@php
    $isAuthenticated = auth()->check();

    $heroCtaPrimary = $isAuthenticated
        ? ['label' => 'Accéder à mon dashboard', 'route' => route('dashboard')]
        : (Route::has('register')
            ? ['label' => 'Commencer le défi', 'route' => route('register')]
            : ['label' => 'Explorer l’app', 'route' => route('home')]);

    $heroCtaSecondary = $isAuthenticated
        ? ['label' => 'Ouvrir le Daily Challenge', 'route' => route('daily-challenge')]
        : ['label' => 'Découvrir la méthode', 'route' => '#how-it-works'];

    $heroHighlights = [
        ['label' => 'Suivi quotidien', 'icon' => 'calendar'],
        ['label' => 'Streak & badges', 'icon' => 'sparkles'],
        ['label' => 'Coaching IA', 'icon' => 'bot'],
    ];

    $howItWorks = [
        [
            'title' => '01. Définis ton parcours',
            'description' => "Choisis ton focus des 100 prochains jours et crée tes premiers projets. L'app te fournit un canevas clair pour planifier tes shipments.",
            'icon' => 'flag',
        ],
        [
            'title' => '02. Loggue chaque journée',
            'description' => 'Saisie guidée, suggestions IA et rappel automatique. Une minute suffit pour documenter ce que tu as appris.',
            'icon' => 'pencil',
        ],
        [
            'title' => '03. Analyse & partage',
            'description' => 'Visualise ta progression, décroche des badges et exporte ton recap hebdo pour ta communauté.',
            'icon' => 'chart',
        ],
    ];

    $featureGrid = [
        [
            'title' => 'Dashboard de streak',
            'description' => 'Un cockpit pour visualiser ta constance, les jours à rattraper et les micro-objectifs accomplis.',
        ],
        [
            'title' => 'Gestion de projets & tâches',
            'description' => 'Structure tes défis en missions concrètes, attribue-les à un challenge et suis leur avancement.',
        ],
        [
            'title' => 'Invitations & runs privés',
            'description' => 'Rejoins des runs #100DaysOfCode en équipe, partage un code public et collabore sur vos shipments.',
        ],
        [
            'title' => 'Assistant IA intégré',
            'description' => 'Génère automatiquement punchlines, résumés et plans de progression selon ton ton préféré.',
        ],
    ];

    $testimonials = [
        [
            'initials' => 'JV',
            'name' => 'Jiordi Viera',
            'role' => 'Founder & Software Engineer',
            'quote' => "Je n'ai jamais tenu un journal de bord aussi longtemps. Les rappels et l'IA m'aident à shipper même les jours chargés.",
        ],
        [
            'initials' => 'CD',
            'name' => 'Claire Deborah',
            'role' => 'Web Developer',
            'quote' => 'Le combo log + projets m’aide à réellement mesurer mon progrès. Plus de « on verra demain ». On shippe.',
        ],
        [
            'initials' => 'DF',
            'name' => 'Darwin Fotso',
            'role' => 'Backend Engineer',
            'quote' => 'On onboard toute la promo sur la plateforme. Chacun garde son rythme et on partage nos insights en fin de semaine.',
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
          <span>Agent IA coach</span>
        </div>
        <h1 class="text-4xl font-bold leading-tight text-foreground sm:text-5xl lg:text-6xl">
          Code, consigne et partage tes 100 prochains shipments.
        </h1>
        <p class="max-w-xl text-lg text-muted-foreground">
          Un journal intelligent pour tenir ta streak, piloter tes projets et ne plus perdre le fil de tes apprentissages quotidiens.
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
        <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
          @foreach ($heroHighlights as $highlight)
            <span class="inline-flex items-center gap-2 rounded-full border border-border/60 px-3 py-1.5">
              @switch($highlight['icon'])
                @case('calendar')
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
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
            <span class="text-xs font-semibold uppercase tracking-widest text-primary">Aujourd’hui</span>
            <h3 class="mt-1 text-lg font-semibold text-foreground">Ton log #87 est prêt</h3>
            <p class="text-xs text-muted-foreground">Rédige ton insight du jour en moins de 60 secondes.</p>
          </div>
          <div class="space-y-6 px-6 py-8">
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-widest text-muted-foreground">Focus</span>
                <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">Shipping</span>
              </div>
              <p class="text-sm text-muted-foreground">Étendre l’API tasks pour l’automatisation des check-ins.</p>
            </div>
            <div class="space-y-3">
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">Streak</span>
                <span class="font-semibold text-primary">86 jours</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">Dernier shipment</span>
                <span class="font-semibold text-foreground">CI/CD auto sur main</span>
              </div>
            </div>
            <div class="flex items-center justify-between rounded-2xl border border-border/70 bg-background/70 px-4 py-3">
              <div>
                <p class="text-xs uppercase tracking-widest text-muted-foreground">IA Coach</p>
                <p class="text-sm text-foreground">“Besoin d’une punchline pour ton log d’aujourd’hui ?”</p>
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
              Prévisualiser un log
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="how-it-works" class="mx-auto max-w-6xl space-y-12 px-4 sm:px-6 lg:px-0">
    <div class="flex flex-col gap-3 text-center">
      <span class="self-center rounded-full border border-border/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">Comment ça marche</span>
      <h2 class="text-3xl font-semibold text-foreground sm:text-4xl">Une routine simple, un cadre complet</h2>
      <p class="mx-auto max-w-3xl text-base text-muted-foreground sm:text-lg">
        L’app structure ton challenge de A à Z : planifier, logguer, analyser. Pas besoin de documents éparpillés ou de feuilles de calcul improvisées.
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
      <span class="self-center rounded-full border border-border/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">Fonctionnalités clés</span>
      <h2 class="text-3xl font-semibold text-foreground sm:text-4xl">Un coach numérique taillé pour les makers</h2>
      <p class="mx-auto max-w-3xl text-base text-muted-foreground sm:text-lg">
        Des outils pensés pour prioriser l’action : focalise-toi sur ce que tu ships, l’app se charge de tracer le reste.
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
      <span class="self-center rounded-full border border-border/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">Retours</span>
      <h2 class="text-3xl font-semibold text-foreground sm:text-4xl">Ils tiennent leur streak avec l’app</h2>
      <p class="mx-auto max-w-3xl text-base text-muted-foreground sm:text-lg">
        Des développeurs, makers et mentors qui documentent chaque apprentissage et partagent leurs insights.
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
      <span class="rounded-full border border-primary/30 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">Prêt pour le jour 01 ?</span>
      <h2 class="text-3xl font-semibold text-foreground sm:text-4xl">On démarre ensemble dès ce soir</h2>
      <p class="max-w-2xl text-base text-muted-foreground sm:text-lg">
        Tu t’inscris, tu définis ton objectif, l’app crée ton premier log et programme un rappel. À toi les 100 prochains shipments.
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
