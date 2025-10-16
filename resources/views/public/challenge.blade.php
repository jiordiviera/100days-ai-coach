<x-layouts.app :title="$meta['title'] ?? null">
  <div class="mx-auto max-w-5xl space-y-10 px-4 py-12 sm:px-6 lg:px-0">
    <section class="rounded-3xl border border-border/60 bg-card/90 p-8 shadow-xl">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-4">
          <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">Challenge public</p>
          <h1 class="text-3xl font-semibold text-foreground">{{ $run->title }}</h1>
          @if ($run->description)
            <p class="max-w-2xl text-sm text-muted-foreground">{{ $run->description }}</p>
          @endif
          <div class="flex flex-wrap gap-3 text-xs text-muted-foreground">
            <span class="inline-flex items-center rounded-full border border-border/70 px-3 py-1">Lancé le {{ optional($stats['started_at'])->translatedFormat('d F Y') }}</span>
            <span class="inline-flex items-center rounded-full border border-border/70 px-3 py-1">Objectif {{ $stats['target_days'] }} jours</span>
            <span class="inline-flex items-center rounded-full border border-border/70 px-3 py-1">{{ $stats['total_members'] }} participant{{ $stats['total_members'] > 1 ? 's' : '' }}</span>
          </div>
        </div>
        <div class="w-full max-w-xs space-y-4 rounded-3xl border border-border/60 bg-background/80 p-5 text-sm sm:w-auto">
          <div>
            <p class="text-xs uppercase tracking-[0.28em] text-muted-foreground">Progression</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $stats['completion_percent'] }}%</p>
          </div>
          <div class="space-y-2 text-xs text-muted-foreground">
            <p>{{ $stats['total_logs'] }} logs enregistrés</p>
            <p>{{ $stats['public_logs'] }} logs publics</p>
            <p>{{ $stats['public_members'] }} profils publics</p>
          </div>
          @if ($cta['join_code'])
            <a
              href="{{ route('challenges.index') }}"
              class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow transition hover:shadow-lg"
            >
              Rejoindre avec le code {{ $cta['join_code'] }}
            </a>
          @else
            <a
              href="{{ route('challenges.index') }}"
              class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-border/60 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
              Participer au défi
            </a>
          @endif
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
      <h2 class="text-lg font-semibold text-foreground">Participants visibles</h2>
      @if ($publicParticipants->isEmpty())
        <p class="mt-3 text-sm text-muted-foreground">Les membres n'ont pas encore activé le profil public.</p>
      @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
          @foreach ($publicParticipants as $participant)
            <div class="flex items-center gap-3 rounded-2xl border border-border/70 bg-background/80 p-4">
              @if (optional($participant->profile)->avatar_url)
                <img src="{{ $participant->profile->avatar_url }}" alt="Avatar {{ $participant->name }}" class="h-12 w-12 rounded-2xl object-cover" />
              @else
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/15 text-sm font-semibold text-primary">
                  {{ mb_strtoupper(mb_substr($participant->name, 0, 1)) }}
                </span>
              @endif
              <div class="text-sm">
                <a
                  href="{{ route('public.profile', ['username' => $participant->profile->username]) }}"
                  class="font-semibold text-foreground hover:text-primary"
                >
                  {{ $participant->profile->username ?? $participant->name }}
                </a>
                @if ($participant->profile->bio)
                  <p class="text-xs text-muted-foreground">{{ $participant->profile->bio }}</p>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </section>

    <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="text-lg font-semibold text-foreground">Logs partagés récemment</h2>
          <p class="text-xs text-muted-foreground">Une sélection des dernières entrées publiques du challenge.</p>
        </div>
        <a
          href="{{ route('home') }}"
          class="hidden rounded-full border border-border/60 px-3 py-1 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary sm:inline-flex"
        >
          Lancer mon défi
        </a>
      </div>

      @if ($publicLogs->isEmpty())
        <p class="mt-3 text-sm text-muted-foreground">Aucun log public disponible pour le moment.</p>
      @else
        <div class="mt-6 space-y-4">
          @foreach ($publicLogs as $log)
            <article class="rounded-2xl border border-border/70 bg-background/80 p-5">
              <header class="flex flex-wrap items-center justify-between gap-3 text-xs text-muted-foreground">
                <span class="font-semibold text-foreground">Jour {{ $log->day_number }}</span>
                <span>{{ optional($log->date)->translatedFormat('d M Y') }}</span>
                <a
                  href="{{ optional($log->user->profile)->username ? route('public.profile', ['username' => $log->user->profile->username]) : '#' }}"
                  class="inline-flex items-center gap-2 rounded-full border border-border/60 px-3 py-0.5 text-[11px] uppercase tracking-widest text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                >
                  {{ optional($log->user->profile)->username ?? $log->user->name }}
                </a>
              </header>
              <div class="mt-3 space-y-3 text-sm text-muted-foreground">
                @if ($log->summary_md)
                  <div class="prose prose-sm max-w-none dark:prose-invert">
                    {!! \Illuminate\Support\Str::markdown($log->summary_md, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                  </div>
                @elseif ($log->notes)
                  <p>{{ $log->notes }}</p>
                @endif
                <a
                  href="{{ route('logs.share', ['token' => $log->public_token]) }}"
                  class="inline-flex items-center gap-2 text-xs font-semibold text-primary transition hover:underline"
                >
                  Voir l'entrée
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

    <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
      <h2 class="text-lg font-semibold text-foreground">Contact organisateur</h2>
      <div class="mt-3 flex flex-wrap gap-3 text-xs text-muted-foreground">
        <span>Challenge animé par {{ optional($run->owner)->name ?? 'un membre de la communauté' }}.</span>
        @if (! empty($ownerSocial))
          @foreach ($ownerSocial as $platform => $link)
            <a
              href="{{ $link }}"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 rounded-full border border-border/70 px-3 py-1 font-semibold transition hover:border-primary/50 hover:text-primary"
            >
              {{ $platform }}
            </a>
          @endforeach
        @endif
      </div>
    </section>
  </div>
</x-layouts.app>
