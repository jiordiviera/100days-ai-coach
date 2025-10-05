@php
    $isAuthenticated = auth()->check();
    $user = auth()->user();
    $primaryLinks = $isAuthenticated
        ? [
            ['label' => 'Accueil', 'route' => route('home'), 'active' => ['home']],
            ['label' => 'Dashboard', 'route' => route('dashboard'), 'active' => ['dashboard']],
        ]
        : [
            ['label' => 'Accueil', 'route' => route('home'), 'active' => ['home']],
        ];
    $secondaryLinks = $isAuthenticated
        ? [
            ['label' => 'Challenges', 'route' => route('challenges.index'), 'active' => ['challenges.*']],
            ['label' => 'Projets', 'route' => route('projects.index'), 'active' => ['projects.*']],
            ['label' => 'Paramètres', 'route' => route('settings'), 'active' => ['settings']],
        ]
        : [];
    $ctaLink = $isAuthenticated
        ? ['label' => 'Daily Challenge', 'route' => route('daily-challenge')]
        : (Route::has('register') ? ['label' => 'Commencer', 'route' => route('register')] : null);
    $loginLink = Route::has('login') ? route('login') : null;
    $avatarUrl = $isAuthenticated ? optional(optional($user)->profile)->avatar_url : null;
    $initial = $isAuthenticated ? mb_strtoupper(mb_substr($user->name ?? 'Invité', 0, 1)) : null;
@endphp

<header
  x-data="{ mobileOpen: false }"
  class="sticky top-0 z-40 border-b border-border/70 bg-gradient-to-r from-background/95 via-background/90 to-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/90"
>
  <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3">
      <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/15 text-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
            <path d="M4 12C4 7.582 7.582 4 12 4s8 3.582 8 8-3.582 8-8 8" stroke-linecap="round" />
            <path d="M8 12l2.25 2.25L16 8.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <div class="flex flex-col">
          <span class="text-xs font-medium uppercase tracking-[0.28em] text-muted-foreground">#100Days</span>
          <span class="text-lg font-semibold text-foreground leading-tight">{{ config('app.name') }}</span>
        </div>
      </a>
    </div>

    <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
      @foreach ($primaryLinks as $link)
        @php($isActive = request()->routeIs($link['active']))
        <a
          wire:navigate
          href="{{ $link['route'] }}"
          class="transition {{ $isActive ? 'text-foreground' : 'text-muted-foreground hover:text-primary' }}"
        >
          {{ $link['label'] }}
        </a>
      @endforeach

      @if (! empty($secondaryLinks))
        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
          <button
            type="button"
            class="flex items-center gap-1 rounded-full px-3 py-1.5 text-muted-foreground transition hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/60"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="header-more-menu"
          >
            <span>Parcours</span>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </button>
          <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="open = false"
            id="header-more-menu"
            class="absolute right-0 mt-3 w-56 rounded-xl border border-border/80 bg-popover p-2 shadow-xl"
          >
            <div class="flex flex-col">
              @foreach ($secondaryLinks as $link)
                @php($isActive = request()->routeIs($link['active']))
                <a
                  wire:navigate
                  href="{{ $link['route'] }}"
                  class="flex items-center justify-between rounded-lg px-3 py-2 text-sm transition {{ $isActive ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground' }}"
                  @click="open = false"
                >
                  <span>{{ $link['label'] }}</span>
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                  </svg>
                </a>
              @endforeach
            </div>
          </div>
        </div>
      @endif
    </nav>

    <div class="hidden items-center gap-3 md:flex">
      @if ($ctaLink)
        <a
          wire:navigate
          href="{{ $ctaLink['route'] }}"
          class="rounded-full border border-primary/30 bg-primary/10 px-4 py-2 text-xs font-semibold text-primary transition hover:border-primary hover:bg-primary/20"
        >
          {{ $ctaLink['label'] }}
        </a>
      @endif

      @auth
        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
          <button
            type="button"
            class="flex items-center gap-2 rounded-full border border-border/70 bg-card px-2.5 py-1.5 text-sm font-medium text-foreground shadow-sm transition hover:border-primary/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/50"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="header-user-menu"
          >
            @if ($avatarUrl)
              <img src="{{ $avatarUrl }}" alt="Avatar utilisateur" class="h-8 w-8 rounded-full object-cover" />
            @else
              <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/15 text-sm font-semibold text-primary">
                {{ $initial }}
              </span>
            @endif
            <span class="hidden text-left sm:block">
              <span class="block text-xs text-muted-foreground">Connecté</span>
              <span class="block leading-tight">{{ $user->name }}</span>
            </span>
            <svg class="h-4 w-4 text-muted-foreground" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </button>
          <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            @click.outside="open = false"
            id="header-user-menu"
            class="absolute right-0 mt-3 w-60 rounded-xl border border-border/80 bg-popover p-2 shadow-2xl"
          >
            <div class="flex items-center gap-2 rounded-lg bg-muted/60 px-3 py-2">
              @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="Avatar utilisateur" class="h-9 w-9 rounded-full object-cover" />
              @else
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/15 text-sm font-semibold text-primary">
                  {{ $initial }}
                </span>
              @endif
              <div class="text-xs text-muted-foreground">
                <p class="font-semibold text-foreground">{{ $user->name }}</p>
                <p>{{ $user->email }}</p>
              </div>
            </div>
            <div class="mt-2 flex flex-col text-sm">
              <a wire:navigate href="{{ route('settings') }}" class="rounded-lg px-3 py-2 text-muted-foreground transition hover:bg-muted hover:text-foreground" @click="open = false">Paramètres</a>
              <a wire:navigate href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-muted-foreground transition hover:bg-muted hover:text-foreground" @click="open = false">Mon tableau de bord</a>
            </div>
            <div class="mt-2 border-t border-border/70 pt-2">
              <a
                wire:navigate
                href="{{ route('logout') }}"
                class="flex items-center justify-center rounded-lg bg-destructive px-3 py-2 text-sm font-semibold text-destructive-foreground transition hover:brightness-95"
              >
                Déconnexion
              </a>
            </div>
          </div>
        </div>
      @else
        @if ($loginLink)
          <a
            wire:navigate
            href="{{ $loginLink }}"
            class="rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
          >
            Connexion
          </a>
        @endif
      @endauth
    </div>

    <button
      class="flex h-10 w-10 items-center justify-center rounded-full border border-border/60 text-foreground transition hover:border-primary/50 md:hidden"
      @click="mobileOpen = ! mobileOpen"
      :aria-expanded="mobileOpen"
      aria-controls="mobile-nav"
      aria-label="Basculer le menu"
    >
      <svg x-show="!mobileOpen" class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
      <svg x-show="mobileOpen" class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>

  <div
    x-cloak
    x-show="mobileOpen"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    id="mobile-nav"
    class="md:hidden border-t border-border/70 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/90"
  >
    <div class="space-y-6 px-4 py-6">
      @auth
        <div class="flex items-center gap-3">
          @if ($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="Avatar utilisateur" class="h-10 w-10 rounded-full object-cover" />
          @else
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/15 text-sm font-semibold text-primary">
              {{ $initial }}
            </span>
          @endif
          <div class="text-sm">
            <p class="font-semibold text-foreground">{{ $user->name }}</p>
            <p class="text-muted-foreground">{{ $user->email }}</p>
          </div>
        </div>
      @endauth

      <nav class="flex flex-col gap-2 text-sm font-medium">
        @foreach ($primaryLinks as $link)
          @php($isActive = request()->routeIs($link['active']))
          <a
            wire:navigate
            href="{{ $link['route'] }}"
            class="rounded-lg px-3 py-2 transition {{ $isActive ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
            @click="mobileOpen = false"
          >
            {{ $link['label'] }}
          </a>
        @endforeach
      </nav>

      @if (! empty($secondaryLinks))
        <div>
          <span class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Espace membre</span>
          <div class="mt-3 flex flex-col gap-2 text-sm">
            @foreach ($secondaryLinks as $link)
              @php($isActive = request()->routeIs($link['active']))
              <a
                wire:navigate
                href="{{ $link['route'] }}"
                class="rounded-lg px-3 py-2 transition {{ $isActive ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
                @click="mobileOpen = false"
              >
                {{ $link['label'] }}
              </a>
            @endforeach
          </div>
        </div>
      @endif

      <div class="flex flex-col gap-3">
        @if ($ctaLink)
          <a
            wire:navigate
            href="{{ $ctaLink['route'] }}"
            class="w-full rounded-full border border-primary/40 bg-primary/10 px-3 py-2 text-center text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/20"
            @click="mobileOpen = false"
          >
            {{ $ctaLink['label'] }}
          </a>
        @endif

        @auth
          <a
            wire:navigate
            href="{{ route('logout') }}"
            class="w-full rounded-full bg-destructive px-3 py-2 text-center text-sm font-semibold text-destructive-foreground transition hover:brightness-95"
            @click="mobileOpen = false"
          >
            Déconnexion
          </a>
        @else
          @if ($loginLink)
            <a
              wire:navigate
              href="{{ $loginLink }}"
              class="w-full rounded-full border border-border/70 px-3 py-2 text-center text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
              @click="mobileOpen = false"
            >
              Connexion
            </a>
          @endif
        @endauth
      </div>
    </div>
  </div>
</header>
