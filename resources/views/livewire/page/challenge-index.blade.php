@php
    $user = auth()->user();
    $ownedCount = $owned->count();
    $joinedCount = $joined->count();
    $activeRun = $activeRun ?? null;
    $activeRunOwned = $activeRun && $activeRun->owner_id === $user->id;
    $heroBadge = $activeRun
        ? ($activeRunOwned ? 'Run propriétaire' : 'Run en participation')
        : 'Prêt pour un nouveau run ?';
    $heroTitle = $activeRun
        ? ($activeRun->title ?: 'Challenge #100DaysOfCode')
        : 'Lance ton challenge 100DaysOfCode';
    $activeDayNumber = $activeRun
        ? (int) (\Illuminate\Support\Carbon::parse($activeRun->start_date)->startOfDay()->diffInDays(\Illuminate\Support\Carbon::now()->startOfDay()) + 1)
        : null;
    $heroDescription = $activeRun
        ? "Jour {$activeDayNumber} sur {$activeRun->target_days}. Documente chaque shipment pour tenir la streak."
        : "Planifie ton prochain run, invite ton crew et utilise le journal intelligent pour suivre tes shipments au quotidien.";
    $ctaPrimary = $activeRun
        ? ['label' => 'Ouvrir le challenge', 'route' => route('challenges.show', $activeRun)]
        : ['label' => 'Créer un challenge', 'route' => '#challenge-create'];
    $ctaSecondary = $activeRun
        ? ['label' => 'Journal du jour', 'route' => route('daily-challenge')]
        : ['label' => 'Voir les challenges rejoints', 'route' => '#joined-list'];
    $ctaSecondarySupportsNavigate = ! str_starts_with($ctaSecondary['route'], '#');
    $activeRestrictionMessage = $hasActiveChallenge
        ? ($activeRunOwned
            ? "Tu as déjà un challenge actif. Termine-le avant d'en lancer un nouveau."
            : "Tu participes déjà à un challenge actif. Termine-le avant d'en lancer un nouveau.")
        : null;
@endphp

<div class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6 lg:px-0">
  @if (session()->has('message'))
    <div class="rounded-2xl border border-primary/30 bg-primary/10 px-4 py-3 text-sm text-primary shadow-sm">
      {{ session('message') }}
    </div>
  @endif

  <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
    <div class="absolute inset-0">
      <div class="absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
      <div class="absolute -right-10 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
    </div>

    <div class="relative grid gap-10 p-8 lg:grid-cols-[1.2fr_0.8fr] lg:p-10">
      <div class="space-y-6">
        <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
          {{ $heroBadge }}
        </span>

        <div class="space-y-3">
          <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ $heroTitle }}</h1>
          <p class="max-w-xl text-sm text-muted-foreground sm:text-base">{{ $heroDescription }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
          <a
            wire:navigate
            href="{{ $ctaPrimary['route'] }}"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
          >
            {{ $ctaPrimary['label'] }}
          </a>

          @if ($ctaSecondarySupportsNavigate)
            <a
              wire:navigate
              href="{{ $ctaSecondary['route'] }}"
              class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
              {{ $ctaSecondary['label'] }}
            </a>
          @else
            <a
              href="{{ $ctaSecondary['route'] }}"
              class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
              {{ $ctaSecondary['label'] }}
            </a>
          @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Challenges créés</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $ownedCount }}</p>
            <p class="text-xs text-muted-foreground">{{ \Illuminate\Support\Str::plural('challenge', $ownedCount) }} propriétaire</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Challenges rejoints</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $joinedCount }}</p>
            <p class="text-xs text-muted-foreground">Runs où tu es participant</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Streak mode</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $activeRun ? 'Actif' : 'À lancer' }}</p>
            <p class="text-xs text-muted-foreground">Planifie ton prochain run dès maintenant</p>
          </div>
        </div>
      </div>

      <div class="relative">
        <div class="absolute -right-6 top-6 h-16 w-16 rounded-full bg-primary/20 blur-3xl"></div>
        <div class="relative h-full rounded-3xl border border-border/60 bg-card/85 p-6 shadow-xl" id="challenge-create">
          <div class="space-y-3">
            <h2 class="text-lg font-semibold text-foreground">Créer un challenge</h2>
            <p class="text-xs text-muted-foreground">
              Définis une date de départ, la durée et choisis si ton run est public. Tu pourras inviter des partenaires ensuite.
            </p>
          </div>

          <form wire:submit.prevent="create" class="mt-4 space-y-4">
            {{ $this->form }}

            @if ($activeRestrictionMessage)
              <p class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-900">
                {{ $activeRestrictionMessage }}
              </p>
            @endif

            <div class="flex justify-end">
              <x-filament::button type="submit" :disabled="$hasActiveChallenge">
                Lancer le challenge
              </x-filament::button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <section class="grid gap-6 lg:grid-cols-2">
    <article class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-foreground">Mes challenges</h2>
          <p class="text-xs text-muted-foreground">Tous les runs dont tu es propriétaire.</p>
        </div>
        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $ownedCount }}</span>
      </div>

      <div class="mt-4 space-y-3">
        @forelse ($owned as $run)
          @php($isActive = $activeRun && $activeRun->id === $run->id)
          <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border {{ $isActive ? 'border-primary/60' : 'border-border/70' }} bg-background/80 px-4 py-3 text-sm">
            <div>
              <p class="font-semibold text-foreground">{{ $run->title ?? 'Challenge 100DaysOfCode' }}</p>
              <p class="text-xs text-muted-foreground">
                Démarré le {{ $run->start_date->format('d/m/Y') }} · {{ $run->target_days }} jours · {{ strtoupper($run->status) }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              @if ($isActive)
                <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">Run en cours</span>
              @elseif ($run->status === 'active')
                <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">Actif</span>
              @endif
              <a
                wire:navigate
                href="{{ route('challenges.show', $run) }}"
                class="inline-flex items-center gap-2 rounded-full border {{ $isActive ? 'border-primary/50 text-primary' : 'border-border/70 text-muted-foreground' }} px-3 py-1.5 text-xs font-semibold transition hover:border-primary/50 hover:text-primary"
              >
                {{ $isActive ? 'Continuer' : 'Ouvrir' }}
              </a>
            </div>
          </div>
        @empty
          <div class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-6 text-center text-sm text-muted-foreground">
            Aucun challenge créé pour l'instant. Lance ton premier run pour débloquer le journal quotidien.
          </div>
        @endforelse
      </div>
    </article>

    <article class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm" id="joined-list">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-foreground">Challenges rejoints</h2>
          <p class="text-xs text-muted-foreground">Runs auxquels tu participes grâce à une invitation.</p>
        </div>
        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $joinedCount }}</span>
      </div>

      <div class="mt-4 space-y-3">
        @forelse ($joined as $run)
          @php($isActive = $activeRun && $activeRun->id === $run->id)
          <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border {{ $isActive ? 'border-primary/60' : 'border-border/70' }} bg-background/80 px-4 py-3 text-sm">
            <div>
              <p class="font-semibold text-foreground">{{ $run->title ?? 'Challenge 100DaysOfCode' }}</p>
              <p class="text-xs text-muted-foreground">
                Host: {{ $run->owner?->name ?? 'Inconnu' }} · Démarré le {{ $run->start_date->format('d/m/Y') }} · {{ $run->target_days }} jours
              </p>
            </div>
            <a
              wire:navigate
              href="{{ route('challenges.show', $run) }}"
              class="inline-flex items-center gap-2 rounded-full border {{ $isActive ? 'border-primary/50 text-primary' : 'border-border/70 text-muted-foreground' }} px-3 py-1.5 text-xs font-semibold transition hover:border-primary/50 hover:text-primary"
            >
              {{ $isActive ? 'Continuer' : 'Rejoindre le run' }}
            </a>
          </div>
        @empty
          <div class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-6 text-center text-sm text-muted-foreground">
            Aucun challenge rejoint pour le moment. Lorsque tu accepteras une invitation, il apparaîtra ici.
          </div>
        @endforelse
      </div>
    </article>
  </section>
</div>
