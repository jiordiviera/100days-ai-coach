@php
    $isAuthenticated = auth()->check();
    $user = auth()->user();
    $primaryLinks = $isAuthenticated
        ? collect([
            ['label' => __('Home'), 'route' => route('home'), 'active' => ['home']],
            ['label' => __('Dashboard'), 'route' => route('dashboard'), 'active' => ['dashboard']],
        ])->when(optional($user)->is_admin, function ($links) {
            $links[] = ['label' => __('Admin'), 'route' => url(config('app.admin_path', 'admin')), 'active' => []];

            return $links;
        })->all()
        : [
            ['label' => __('Home'), 'route' => route('home'), 'active' => ['home']],
        ];
    $secondaryLinks = $isAuthenticated
        ? [
            ['label' => __('Challenges'), 'route' => route('challenges.index'), 'active' => ['challenges.*']],
            ['label' => __('Projects'), 'route' => route('projects.index'), 'active' => ['projects.*']],
            ['label' => __('Leaderboard'), 'route' => route('leaderboard'), 'active' => ['leaderboard']],
            ['label' => __('Settings'), 'route' => route('settings'), 'active' => ['settings']],
        ]
        : [];
    $ctaLink = $isAuthenticated
        ? ['label' => 'Daily Challenge', 'route' => route('daily-challenge')]
        : (Route::has('register') ? ['label' => __('Get started'), 'route' => route('register')] : null);
    $loginLink = Route::has('login') ? route('login') : null;
    $avatarUrl = $isAuthenticated ? optional(optional($user)->profile)->avatar_url : null;
    $initial = $isAuthenticated ? mb_strtoupper(mb_substr($user->name ?? __('Guest'), 0, 1)) : null;
    $availableLocales = config('app.available_locales', ['en', 'fr']);
    $currentLocale = app()->getLocale();
@endphp

<header
  x-data="{ mobileOpen: false, scrolled: false }"
  x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
  class="sticky top-0 z-50 border-b transition-all duration-300"
  :class="scrolled ? 'border-border/80 bg-background/95 shadow-lg backdrop-blur-md supports-[backdrop-filter]:bg-background/95' : 'border-border/70 bg-gradient-to-r from-background/95 via-background/90 to-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/90'"
>
  <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
    {{-- Logo --}}
    <div class="flex items-center gap-3">
      <a href="{{ route('home') }}" wire:navigate class="group flex items-center gap-3 transition-all hover:scale-105">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary/20 to-primary/10 text-primary ring-2 ring-primary/20 transition-all group-hover:ring-4 group-hover:ring-primary/30 group-hover:shadow-lg group-hover:shadow-primary/20">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
            <path d="M4 12C4 7.582 7.582 4 12 4s8 3.582 8 8-3.582 8-8 8" stroke-linecap="round" />
            <path d="M8 12l2.25 2.25L16 8.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </span>
        <div class="flex flex-col">
          <span class="text-[10px] font-bold uppercase tracking-[0.28em] text-primary">#100Days</span>
          <span class="text-lg font-bold leading-tight text-foreground">{{ config('app.name') }}</span>
        </div>
      </a>
    </div>

    {{-- Desktop Navigation --}}
    <nav class="hidden items-center gap-6 text-sm font-semibold md:flex">
      @foreach ($primaryLinks as $link)
        @php($isActive = request()->routeIs($link['active']))
        <a
          wire:navigate
          href="{{ $link['route'] }}"
          class="relative rounded-lg px-3 py-2 transition-all {{ $isActive ? 'text-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50' }}"
        >
          {{ $link['label'] }}
          @if ($isActive)
            <span class="absolute bottom-0 left-1/2 h-0.5 w-1/2 -translate-x-1/2 rounded-full bg-primary"></span>
          @endif
        </a>
      @endforeach

      @if (! empty($secondaryLinks))
        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
          <button
            type="button"
            class="group flex items-center gap-1.5 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:bg-muted/50 hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/60"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="header-more-menu"
          >
            <span>{{ __('Explore') }}</span>
            <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </button>
          <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
            @click.outside="open = false"
            id="header-more-menu"
            class="absolute right-0 mt-3 w-64 origin-top-right rounded-2xl border border-border/80 bg-popover p-2 shadow-2xl backdrop-blur-sm"
          >
            <div class="space-y-1">
              @foreach ($secondaryLinks as $link)
                @php($isActive = request()->routeIs($link['active']))
                <a
                  wire:navigate
                  href="{{ $link['route'] }}"
                  class="group flex items-center justify-between rounded-xl px-4 py-3 text-sm transition-all {{ $isActive ? 'bg-primary/10 text-primary font-semibold' : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground' }}"
                  @click="open = false"
                >
                  <span>{{ $link['label'] }}</span>
                  <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h9.69l-2.47-2.47a.75.75 0 111.06-1.06l3.75 3.75a.75.75 0 010 1.06l-3.75 3.75a.75.75 0 01-1.06-1.06l2.47-2.47H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                  </svg>
                </a>
              @endforeach
            </div>
          </div>
        </div>
      @endif
    </nav>

    {{-- Desktop Actions --}}
    <div class="hidden items-center gap-3 md:flex">
      {{-- Language Selector --}}
      <form method="POST" action="{{ route('locale.update') }}" class="relative">
        @csrf
        <label class="sr-only" for="desktop-locale">{{ __('Language') }}</label>
        <div class="relative">
          <select
            id="desktop-locale"
            name="locale"
            class="appearance-none rounded-lg border border-border/70 bg-background/50 pl-3 pr-8 py-2 text-xs font-bold uppercase tracking-wider text-muted-foreground backdrop-blur-sm transition-all hover:border-primary/50 hover:bg-background focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
            onchange="this.form.submit()"
          >
            @foreach ($availableLocales as $locale)
              <option value="{{ $locale }}" @selected($locale === $currentLocale)>
                {{ strtoupper($locale) }}
              </option>
            @endforeach
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
            <svg class="h-4 w-4 text-muted-foreground" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </form>

      {{-- CTA Button --}}
      @if ($ctaLink)
        <a
          wire:navigate
          href="{{ $ctaLink['route'] }}"
          class="group relative overflow-hidden rounded-full border-2 border-primary/30 bg-primary/10 px-5 py-2 text-xs font-bold text-primary transition-all hover:border-primary/50 hover:bg-primary/20 hover:shadow-lg hover:shadow-primary/25 hover:scale-105"
        >
          <span class="relative z-10">{{ $ctaLink['label'] }}</span>
          <div class="absolute inset-0 -z-0 bg-gradient-to-r from-primary/0 via-primary/10 to-primary/0 opacity-0 transition-opacity group-hover:opacity-100"></div>
        </a>
      @endif

      {{-- User Menu --}}
      @auth
        <form method="POST" action="{{ route('logout') }}" id="header-logout-form" class="hidden">
          @csrf
        </form>
        <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
          <button
            type="button"
            class="group flex items-center gap-3 rounded-full border-2 border-border/70 bg-card/80 pl-1.5 pr-4 py-1.5 text-sm font-medium text-foreground shadow-sm backdrop-blur-sm transition-all hover:border-primary/50 hover:shadow-md hover:shadow-primary/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/50"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="header-user-menu"
          >
            @if ($avatarUrl)
              <img src="{{ $avatarUrl }}" alt="{{ __('User avatar') }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-primary/20 transition-all group-hover:ring-4 group-hover:ring-primary/30" />
            @else
              <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-primary/10 text-sm font-bold text-primary ring-2 ring-primary/20 transition-all group-hover:ring-4 group-hover:ring-primary/30">
                {{ $initial }}
              </span>
            @endif
            <span class="hidden text-left sm:block">
              <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">{{ __('Signed in') }}</span>
              <span class="block leading-tight font-semibold">{{ $user->name }}</span>
            </span>
            <svg class="h-4 w-4 text-muted-foreground transition-transform" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </button>
          <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
            @click.outside="open = false"
            id="header-user-menu"
            class="absolute right-0 mt-3 w-72 origin-top-right rounded-2xl border border-border/80 bg-popover p-3 shadow-2xl backdrop-blur-sm"
          >
            {{-- User Info Card --}}
            <div class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-primary/10 to-primary/5 p-4 ring-1 ring-primary/20">
              @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ __('User avatar') }}" class="h-12 w-12 rounded-full object-cover ring-2 ring-primary/30" />
              @else
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-primary/30 to-primary/20 text-base font-bold text-primary ring-2 ring-primary/30">
                  {{ $initial }}
                </span>
              @endif
              <div class="min-w-0 flex-1 text-sm">
                <p class="truncate font-bold text-foreground">{{ $user->name }}</p>
                <p class="truncate text-xs text-muted-foreground">{{ $user->email }}</p>
              </div>
            </div>

            {{-- Menu Links --}}
            <div class="mt-3 space-y-1">
              <a wire:navigate href="{{ route('settings') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:bg-muted hover:text-foreground" @click="open = false">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="3"></circle>
                  <path d="M12 1v6m0 6v6m-9-9h6m6 0h6"></path>
                </svg>
                {{ __('Settings') }}
              </a>
              <a wire:navigate href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:bg-muted hover:text-foreground" @click="open = false">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="7" height="7"></rect>
                  <rect x="14" y="3" width="7" height="7"></rect>
                  <rect x="14" y="14" width="7" height="7"></rect>
                  <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                {{ __('My dashboard') }}
              </a>
            </div>

            {{-- Logout --}}
            <div class="mt-3 border-t border-border/70 pt-3">
              <button
                type="submit"
                form="header-logout-form"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-destructive px-4 py-2.5 text-sm font-bold text-destructive-foreground transition-all hover:brightness-95 hover:shadow-lg"
                @click="open = false"
              >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4m7 14l5-5-5-5m5 5H9"></path>
                </svg>
                {{ __('Log out') }}
              </button>
            </div>
          </div>
        </div>
      @else
        @if ($loginLink)
          <a
            wire:navigate
            href="{{ $loginLink }}"
            class="rounded-full border-2 border-border/70 bg-background/50 px-5 py-2 text-xs font-bold text-foreground backdrop-blur-sm transition-all hover:border-primary/50 hover:bg-background hover:text-primary hover:shadow-md"
          >
            {{ __('Sign in') }}
          </a>
        @endif
      @endauth
    </div>

    {{-- Mobile Menu Button --}}
    <button
      class="group flex h-10 w-10 items-center justify-center rounded-xl border-2 border-border/60 text-foreground transition-all hover:border-primary/50 hover:bg-primary/5 md:hidden"
      @click="mobileOpen = ! mobileOpen"
      :aria-expanded="mobileOpen"
      aria-controls="mobile-nav"
      aria-label="{{ __('Toggle menu') }}"
    >
      <svg x-show="!mobileOpen" class="h-5 w-5 transition-transform group-hover:scale-110" stroke="currentColor" fill="none" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
      <svg x-show="mobileOpen" class="h-5 w-5 transition-transform group-hover:rotate-90" stroke="currentColor" fill="none" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>

  {{-- Mobile Navigation --}}
  <div
    x-cloak
    x-show="mobileOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-4"
    id="mobile-nav"
    class="border-t border-border/70 bg-background/98 backdrop-blur-md supports-[backdrop-filter]:bg-background/95 md:hidden"
  >
    <div class="space-y-6 px-4 py-6">
      {{-- User Info Mobile --}}
      @auth
        <div class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-primary/10 to-primary/5 p-4 ring-1 ring-primary/20">
          @if ($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="{{ __('User avatar') }}" class="h-12 w-12 rounded-full object-cover ring-2 ring-primary/30" />
          @else
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-primary/30 to-primary/20 text-base font-bold text-primary ring-2 ring-primary/30">
              {{ $initial }}
            </span>
          @endif
          <div class="min-w-0 flex-1 text-sm">
            <p class="truncate font-bold text-foreground">{{ $user->name }}</p>
            <p class="truncate text-xs text-muted-foreground">{{ $user->email }}</p>
          </div>
        </div>
      @endauth

      {{-- Language Selector Mobile --}}
      <form method="POST" action="{{ route('locale.update') }}">
        @csrf
        <label class="sr-only" for="mobile-locale">{{ __('Language') }}</label>
        <div class="relative">
          <select
            id="mobile-locale"
            name="locale"
            class="w-full appearance-none rounded-xl border-2 border-border/70 bg-background/50 px-4 py-3 pr-10 text-sm font-bold uppercase tracking-wider text-foreground backdrop-blur-sm transition-all hover:border-primary/50 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/40"
            onchange="this.form.submit()"
          >
            @foreach ($availableLocales as $locale)
              <option value="{{ $locale }}" @selected($locale === $currentLocale)>{{ strtoupper($locale) }}</option>
            @endforeach
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
            <svg class="h-5 w-5 text-muted-foreground" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </form>

      {{-- Primary Links Mobile --}}
      <nav class="space-y-2">
        @foreach ($primaryLinks as $link)
          @php($isActive = request()->routeIs($link['active']))
          <a
            wire:navigate
            href="{{ $link['route'] }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition-all {{ $isActive ? 'bg-primary/10 text-primary ring-2 ring-primary/20' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
            @click="mobileOpen = false"
          >
            {{ $link['label'] }}
          </a>
        @endforeach
      </nav>

      {{-- Secondary Links Mobile --}}
      @if (! empty($secondaryLinks))
        <div class="space-y-3">
          <span class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.28em] text-muted-foreground">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
            </svg>
            {{ __('Member area') }}
          </span>
          <div class="space-y-2">
            @foreach ($secondaryLinks as $link)
              @php($isActive = request()->routeIs($link['active']))
              <a
                wire:navigate
                href="{{ $link['route'] }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition-all {{ $isActive ? 'bg-primary/10 text-primary ring-2 ring-primary/20' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
                @click="mobileOpen = false"
              >
                {{ $link['label'] }}
              </a>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Action Buttons Mobile --}}
      <div class="space-y-3 border-t border-border/70 pt-6">
        @if ($ctaLink)
          <a
            wire:navigate
            href="{{ $ctaLink['route'] }}"
            class="flex w-full items-center justify-center gap-2 rounded-full border-2 border-primary/40 bg-primary/10 px-4 py-3 text-sm font-bold text-primary transition-all hover:border-primary hover:bg-primary/20 hover:shadow-lg hover:shadow-primary/20"
            @click="mobileOpen = false"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 12l8-4-8-4-8 4 8 4zM4 12l8 4 8-4M4 16l8 4 8-4"></path>
            </svg>
            {{ $ctaLink['label'] }}
          </a>
        @endif

        @auth
          <button
            type="submit"
            form="header-logout-form"
            class="flex w-full items-center justify-center gap-2 rounded-full bg-destructive px-4 py-3 text-sm font-bold text-destructive-foreground transition-all hover:brightness-95 hover:shadow-lg"
            @click="mobileOpen = false"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4m7 14l5-5-5-5m5 5H9"></path>
            </svg>
            {{ __('Log out') }}
          </button>
        @else
          @if ($loginLink)
            <a
              wire:navigate
              href="{{ $loginLink }}"
              class="flex w-full items-center justify-center gap-2 rounded-full border-2 border-border/70 bg-background px-4 py-3 text-sm font-bold text-foreground transition-all hover:border-primary/50 hover:bg-background hover:text-primary"
              @click="mobileOpen = false"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4m-5-4l5-5-5-5m5 5H3"></path>
              </svg>
              {{ __('Sign in') }}
            </a>
          @endif
        @endauth
      </div>
    </div>
  </div>
</header>