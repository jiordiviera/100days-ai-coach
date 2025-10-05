<div class="mx-auto max-w-6xl space-y-10 px-4 py-10 sm:px-6 lg:px-0">
  <section class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">Classement</p>
        <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">Leaderboard — Streak & jours actifs</h1>
        <p class="mt-2 text-sm text-muted-foreground">Compare ta constance avec la communauté #100DaysOfCode et garde la motivation.</p>
      </div>
      <div class="flex items-center gap-3">
        <a
          wire:navigate
          href="{{ route('daily-challenge') }}"
          class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
        >
          Retour au journal
        </a>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 rounded-3xl border border-border/60 bg-card/90 p-4">
      <label class="text-xs font-medium uppercase tracking-[0.24em] text-muted-foreground">Challenge</label>
      <select
        wire:model.live="challengeFilter"
        class="rounded-full border border-border/70 bg-background px-3 py-1 text-sm text-foreground focus:border-primary focus:outline-none"
      >
        <option value="">Tous les challenges</option>
        @foreach ($challengeOptions as $id => $title)
          <option value="{{ $id }}">{{ $title }}</option>
        @endforeach
      </select>
    </div>
  </section>

  <section class="space-y-4 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-border/70 text-sm">
        <thead class="text-xs uppercase tracking-[0.2em] text-muted-foreground">
          <tr>
            <th class="py-3 pr-4 text-left">Rang</th>
            <th class="py-3 pr-4 text-left">Participant</th>
            <th class="py-3 pr-4 text-right">Streak</th>
            <th class="py-3 pr-4 text-right">Jours actifs</th>
            <th class="py-3 pr-4 text-right">Dernier log</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border/60 text-foreground">
          @forelse ($leaderboard as $index => $entry)
            @php
              $rank = ($leaderboard->currentPage() - 1) * $leaderboard->perPage() + $loop->iteration;
              $user = $entry['user'];
              $username = $user?->profile?->username;
            @endphp
            <tr class="transition hover:bg-muted/40">
              <td class="py-3 pr-4 text-left text-muted-foreground">#{{ $rank }}</td>
              <td class="py-3 pr-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                    {{ mb_strtoupper(mb_substr($username ?? $user?->name ?? '—', 0, 1)) }}
                  </div>
                  <div class="space-y-0.5">
                    <p class="text-sm font-semibold text-foreground">{{ $username ?? $user?->name ?? 'Utilisateur' }}</p>
                    <p class="text-xs text-muted-foreground">{{ $user?->email }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 pr-4 text-right text-lg font-semibold">{{ $entry['streak'] }}</td>
              <td class="py-3 pr-4 text-right text-lg font-semibold">{{ $entry['days_active_total'] }}</td>
              <td class="py-3 pr-4 text-right text-sm text-muted-foreground">
                @if ($entry['last_log_date'])
                  {{ $entry['last_log_date']->diffForHumans() }}
                @else
                  —
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-6 text-center text-sm text-muted-foreground">
                Aucun participant pour le moment. Enregistre ton premier log pour apparaître ici !
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div>
      {{ $leaderboard->links() }}
    </div>
  </section>
</div>
