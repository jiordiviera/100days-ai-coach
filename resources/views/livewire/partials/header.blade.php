<header
  x-data="{ open: false }"
  class="sticky top-0 z-40 border-b border-border/80 bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/70"
>
  <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
    <!-- Brand -->
    <div class="flex items-center gap-3">
      <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
        <span class="text-xl font-semibold text-foreground">{{ config('app.name') }}</span>
      </a>
    </div>

    @if (Route::has('login'))
      <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
        <a wire:navigate href="{{ route('home') }}" class="text-muted-foreground hover:text-primary">Accueil</a>
        @auth
          <a wire:navigate href="{{ route('dashboard') }}" class="text-muted-foreground hover:text-primary">Dashboard</a>
          <a wire:navigate href="{{ route('challenges.index') }}" class="text-muted-foreground hover:text-primary">Challenges</a>
          <a wire:navigate href="{{ route('projects.index') }}" class="text-muted-foreground hover:text-primary">Projets</a>
        @else
          <a wire:navigate href="{{ route('login') }}" class="text-muted-foreground hover:text-primary">Connexion</a>
          @if (Route::has('register'))
            <a wire:navigate href="{{ route('register') }}" class="text-muted-foreground hover:text-primary">Inscription</a>
          @endif
        @endauth
      </nav>

      <div class="hidden items-center gap-3 md:flex">
        @auth
          <div class="flex items-center gap-3">
            <span class="text-sm text-muted-foreground">Salut, <span class="font-semibold text-foreground">{{ auth()->user()->name }}</span></span>
            <a
              href="{{ route('logout') }}"
              wire:navigate
              class="rounded-full bg-destructive px-4 py-2 text-xs font-semibold text-destructive-foreground transition hover:brightness-95"
            >
              Déconnexion
            </a>
          </div>
        @else
          <a
            href="{{ route('register') }}"
            wire:navigate
            class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-foreground transition hover:brightness-95"
          >
            Commencer
          </a>
        @endauth
      </div>
    @endif

    <!-- Mobile menu toggle -->
    <button class="md:hidden" @click="open = !open" aria-label="Menu">
      <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
        <path
          :class="{ 'hidden': open, 'inline-flex': !open }"
          class="inline-flex"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M4 6h16M4 12h16M4 18h16"
        />
        <path
          :class="{ 'hidden': !open, 'inline-flex': open }"
          class="hidden"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M6 18L18 6M6 6l12 12"
        />
      </svg>
    </button>
  </div>

  <!-- Mobile dropdown -->
  <div x-show="open" x-transition class="md:hidden border-t border-border/70 bg-background">
    <nav class="flex flex-col gap-1 p-4 text-sm font-medium">
      <a wire:navigate href="{{ route('home') }}" class="rounded px-3 py-2 hover:bg-muted">Accueil</a>
      @auth
        <a wire:navigate href="{{ route('dashboard') }}" class="rounded px-3 py-2 hover:bg-muted">Dashboard</a>
        <a wire:navigate href="{{ route('challenges.index') }}" class="rounded px-3 py-2 hover:bg-muted">Challenges</a>
        <a wire:navigate href="{{ route('projects.index') }}" class="rounded px-3 py-2 hover:bg-muted">Projets</a>
        <a wire:navigate href="{{ route('logout') }}" class="mt-2 rounded px-3 py-2 bg-destructive text-destructive-foreground">Déconnexion</a>
      @else
        <a wire:navigate href="{{ route('login') }}" class="rounded px-3 py-2 hover:bg-muted">Connexion</a>
        @if (Route::has('register'))
          <a wire:navigate href="{{ route('register') }}" class="rounded px-3 py-2 hover:bg-muted">Inscription</a>
        @endif
      @endauth
    </nav>
  </div>
</header>
