<x-layouts.app :title="$meta['title'] ?? null">
  <div class="mx-auto max-w-4xl space-y-10 px-4 py-12 sm:px-6 lg:px-0">
    <section class="rounded-3xl border border-border/60 bg-card/90 p-8 shadow-xl">
      <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-4">
          <div class="flex items-center gap-4">
            @if ($profile->avatar_url)
              <img src="{{ $profile->avatar_url }}" alt="Avatar {{ $user->name }}" class="h-20 w-20 rounded-3xl object-cover shadow-lg" />
            @else
              <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-primary/15 text-2xl font-semibold text-primary">
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
              </span>
            @endif
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">Profil public</p>
              <h1 class="text-3xl font-semibold text-foreground">{{ $profile->username ?? $user->name }}</h1>
              <p class="text-sm text-muted-foreground">Membre depuis {{ $user->created_at?->translatedFormat('F Y') }}</p>
            </div>
          </div>
          @if ($profile->bio)
            <p class="max-w-2xl text-sm leading-relaxed text-muted-foreground">{{ $profile->bio }}</p>
          @endif
          @if (! empty($socialLinks))
            <div class="flex flex-wrap gap-3">
              @foreach ($socialLinks as $platform => $link)
                <a
                  href="{{ $link }}"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-2 rounded-full border border-border/70 px-4 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                >
                  <span class="uppercase tracking-widest">{{ $platform }}</span>
                </a>
              @endforeach
            </div>
          @endif
        </div>
        <div class="grid w-full max-w-xs grid-cols-2 gap-3 text-center text-sm sm:w-auto">
          <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
            <p class="text-xs uppercase tracking-[0.28em] text-muted-foreground">Streak actuel</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $streaks['current'] }}</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
            <p class="text-xs uppercase tracking-[0.28em] text-muted-foreground">Meilleure streak</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $streaks['longest'] }}</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
            <p class="text-xs uppercase tracking-[0.28em] text-muted-foreground">Logs publics</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $stats['public_logs'] }}</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
            <p class="text-xs uppercase tracking-[0.28em] text-muted-foreground">Heures cumulées</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ number_format($stats['total_hours'], 1) }} h</p>
          </div>
        </div>
      </div>
    </section>

    @if (! empty($recentBadges))
      <section class="rounded-3xl border border-emerald-200/60 bg-emerald-500/10 p-6 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-700">Badges récents</h2>
        <div class="mt-4 flex flex-wrap gap-2">
          @foreach ($recentBadges as $badge)
            <span class="inline-flex items-center rounded-full bg-emerald-600/15 px-3 py-1 text-xs font-semibold text-emerald-800">
              {{ $badge->badge_key }}
            </span>
          @endforeach
        </div>
      </section>
    @endif

    <section class="space-y-6 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="text-lg font-semibold text-foreground">Derniers logs publics</h2>
          <p class="text-xs text-muted-foreground">Sélection des dernières entrées partagées par {{ $profile->username ?? $user->name }}.</p>
        </div>
        <a
          href="{{ route('home') }}"
          class="hidden rounded-full border border-border/60 px-3 py-1 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary sm:inline-flex"
        >
          Rejoindre le défi
        </a>
      </div>
      @if ($publicLogs->isEmpty())
        <p class="text-sm text-muted-foreground">Aucun log public pour le moment.</p>
      @else
        <div class="space-y-4">
          @foreach ($publicLogs as $log)
            <article class="rounded-2xl border border-border/70 bg-background/80 p-5">
              <header class="flex flex-wrap items-center justify-between gap-3 text-xs text-muted-foreground">
                <span class="font-semibold text-foreground">Jour {{ $log->day_number }}</span>
                <span>{{ optional($log->date)->translatedFormat('d M Y') }}</span>
                @if ($log->challengeRun)
                  <span class="rounded-full border border-border/60 px-3 py-0.5 text-[11px] uppercase tracking-widest">{{ $log->challengeRun->title }}</span>
                @endif
              </header>
              <div class="mt-3 space-y-3 text-sm text-muted-foreground">
                @if ($log->summary_md)
                  <div class="prose prose-sm max-w-none dark:prose-invert">
                    {!! \Illuminate\Support\Str::markdown($log->summary_md) !!}
                  </div>
                @elseif ($log->notes)
                  <p>{{ $log->notes }}</p>
                @endif
                @if ($log->tags)
                  <div class="flex flex-wrap gap-2">
                    @foreach ($log->tags as $tag)
                      <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $tag }}</span>
                    @endforeach
                  </div>
                @endif
                <a
                  href="{{ route('logs.share', ['token' => $log->public_token]) }}"
                  class="inline-flex items-center gap-2 text-xs font-semibold text-primary transition hover:underline"
                >
                  Voir le log complet
                  <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.293 2.293a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L13 5.414V15a1 1 0 11-2 0V5.414L8.707 7.707A1 1 0 017.293 6.293l4-4z" clip-rule="evenodd" />
                  </svg>
                </a>
              </div>
            </article>
          @endforeach
        </div>
      @endif
    </section>

    @if (! empty($stats['projects']))
      <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-foreground">Projets les plus travaillés</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($stats['projects'] as $project => $count)
                        <div class="rounded-2xl border border-border/70 bg-background/80 p-4">
                          <p class="text-sm font-semibold text-foreground">{{ $project }}</p>
                          <p class="text-xs text-muted-foreground">{{ $count }} {{ \Illuminate\Support\Str::plural('session', $count) }}</p>
            </div>
          @endforeach
        </div>
      </section>
    @endif
  </div>
</x-layouts.app>
