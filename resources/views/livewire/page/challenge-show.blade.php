@php
    use Illuminate\Support\Str;

    $isOwner = auth()->id() === $run->owner_id;
    $startDateFormatted = optional($run->start_date)->format('d/m/Y');
    $targetDays = max(1, (int) $run->target_days);
    $statusLabel = match ($run->status) {
        'completed' => 'Terminé',
        'paused' => 'En pause',
        default => 'Actif',
    };
    $publicJoinCode = $run->is_public && $run->public_join_code;
    $calendarColumns = 10;
    $calendarChunks = array_chunk(range(1, $targetDays), $calendarColumns);
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
      <div class="absolute -right-12 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
    </div>

    <div class="relative grid gap-10 p-8 lg:grid-cols-[1.25fr_0.75fr] lg:p-10">
      <div class="space-y-6">
        <div class="space-y-2">
          <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
            {{ $statusLabel }}
          </span>
          <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ $run->title ?? 'Challenge 100DaysOfCode' }}</h1>
          <p class="max-w-xl text-sm text-muted-foreground sm:text-base">
            @if ($activeDayNumber)
              Jour {{ $activeDayNumber }} sur {{ $targetDays }}.
            @endif
            Tenu par {{ $run->owner->name }} – consigne chaque shipment pour garder ta streak vivante.
          </p>
        </div>

        <div class="flex flex-wrap gap-3">
          <a
            wire:navigate
            href="{{ route('daily-challenge') }}"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
          >
            Journal du jour
          </a>
          <a
            wire:navigate
            href="{{ route('challenges.insights', $run->id) }}"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
          >
            Insights
          </a>
          <a
            wire:navigate
            href="{{ route('challenges.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
          >
            Retour aux challenges
          </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Participants</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $participantsCount }}</p>
            <p class="text-xs text-muted-foreground">dont toi et {{ $run->owner->name }}</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Progression globale</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $globalPercent }}%</p>
            <p class="text-xs text-muted-foreground">Total des logs sur l'objectif collectif</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Ta streak</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $myStreak }} jours</p>
            <p class="text-xs text-muted-foreground">Consigne ton log du jour pour la conserver</p>
          </div>
          <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Jours restants</p>
            <p class="mt-2 text-2xl font-semibold text-foreground">{{ $daysRemaining ?? $targetDays }}</p>
            <p class="text-xs text-muted-foreground">Objectif total : {{ $targetDays }} jours</p>
          </div>
        </div>

        @if ($publicJoinCode)
          <div
            class="inline-flex flex-wrap items-center gap-3 rounded-2xl border border-primary/30 bg-primary/10 px-4 py-3 text-xs text-primary"
            x-data="{ copied: false, copy(text) { navigator.clipboard.writeText(text); this.copied = true; setTimeout(() => this.copied = false, 2000); } }"
          >
            <span class="font-semibold uppercase tracking-[0.24em]">Code public</span>
            <span class="font-mono text-sm">{{ $run->public_join_code }}</span>
            <button
              type="button"
              class="rounded-full border border-primary px-3 py-1 font-semibold transition hover:bg-primary hover:text-primary-foreground"
              @click="copy('{{ $run->public_join_code }}')"
            >
              <span x-show="! copied">Copier</span>
              <span x-show="copied">Copié !</span>
            </button>
          </div>
        @endif
      </div>

      <div class="relative space-y-4 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl">
        <div>
          <p class="text-xs uppercase tracking-widest text-muted-foreground">Détails</p>
          <h2 class="mt-1 text-lg font-semibold text-foreground">Informations du challenge</h2>
        </div>
        <dl class="space-y-3 text-sm text-muted-foreground">
          <div class="flex items-center justify-between">
            <dt>Owner</dt>
            <dd class="font-medium text-foreground">{{ $run->owner->name }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>Date de début</dt>
            <dd class="font-medium text-foreground">{{ $startDateFormatted ?? '—' }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>Objectif</dt>
            <dd class="font-medium text-foreground">{{ $targetDays }} jours</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>Visibilité</dt>
            <dd class="font-medium text-foreground">{{ $run->is_public ? 'Public' : 'Privé' }}</dd>
          </div>
        </dl>

        @if (! $isOwner)
          <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground">
            Tu es participant. Suis tes logs via le Daily Challenge.
          </div>
        @endif
      </div>
    </div>
  </section>

  <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
    <article class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-foreground">Participants</h2>
          <p class="text-xs text-muted-foreground">Progression individuelle et streaks.</p>
        </div>
      </div>

      <div class="mt-4 space-y-4">
        @foreach ($progress as $item)
          @php($participantLink = $run->participantLinks->firstWhere('user_id', $item['user']->id))
          <div class="space-y-2 rounded-2xl border border-border/70 bg-background/80 p-4 text-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-foreground">{{ $item['user']->name }}</span>
                @if ($item['user']->id === $run->owner_id)
                  <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">Owner</span>
                @endif
              </div>
              <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <span class="rounded-full bg-emerald-500/10 px-3 py-1 font-semibold text-emerald-600">Streak {{ $item['streak'] }}</span>
                <span>{{ $item['done'] }} / {{ $targetDays }} ({{ $item['percent'] }}%)</span>
              </div>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-muted">
              <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $item['percent'] }}%"></div>
            </div>
            @if ($isOwner && $participantLink && $item['user']->id !== $run->owner_id)
              <div class="flex justify-end">
                <x-filament::button
                  color="danger"
                  size="sm"
                  wire:confirm="Retirer ce participant du challenge ?"
                  wire:click="removeParticipant('{{ $participantLink->getKey() }}')"
                >
                  Retirer
                </x-filament::button>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </article>

    <article class="space-y-6">
      @if ($isOwner)
        <div class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-foreground">Inviter des participants</h2>
          <p class="text-xs text-muted-foreground">Une personne par run actif. Les invités existants doivent terminer leur challenge avant d'accepter.</p>

          <form wire:submit.prevent="sendInvite" class="mt-4 flex flex-wrap items-center gap-3">
            <div class="min-w-60 grow">
              {{ $this->form }}
            </div>
            <x-filament::button type="submit">Envoyer l'invitation</x-filament::button>
          </form>

          @if ($lastInviteLink)
            <p class="mt-3 text-xs text-muted-foreground">
              Lien généré :
              <x-filament::link class="font-mono text-primary" href="{{ $lastInviteLink }}">{{ Str::limit($lastInviteLink, 40) }}</x-filament::link>
            </p>
          @endif

          <div class="mt-4">
            <h3 class="text-sm font-semibold text-foreground">Invitations en attente</h3>
            <ul class="mt-2 space-y-2 text-sm">
              @forelse ($pendingInvites as $inv)
                <li class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-border/60 bg-background/80 px-4 py-2">
                  <span>{{ $inv->email }}</span>
                  <span class="flex items-center gap-2">
                    <x-filament::button size="sm" wire:click="copyLink('{{ route('challenges.accept', $inv->token) }}')">
                      Copier
                    </x-filament::button>
                    <x-filament::button
                      size="sm"
                      color="danger"
                      wire:confirm="Révoquer cette invitation ?"
                      wire:click="revokeInvite('{{ $inv->getKey() }}')"
                    >
                      Révoquer
                    </x-filament::button>
                  </span>
                </li>
              @empty
                <li class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground">
                  Aucune invitation en attente.
                </li>
              @endforelse
            </ul>
          </div>
        </div>
      @else
        <div class="rounded-3xl border border-border/60 bg-card/90 p-6 text-sm text-muted-foreground shadow-sm">
          <p class="font-semibold text-foreground">Rappel</p>
          <p class="mt-2">Tu participes à ce challenge. Utilise le journal quotidien pour documenter tes shipments et surveille ton badge streak.</p>
        </div>
      @endif

      <div class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">Mes derniers logs</h2>
          @if (! $isOwner)
            <x-filament::button color="gray" size="sm" wire:click="leave" wire:confirm="Quitter ce challenge ?">
              Quitter le challenge
            </x-filament::button>
          @endif
        </div>
        <ul class="mt-4 space-y-2 text-sm">
          @forelse ($myRecentLogs as $log)
            <li class="flex items-center justify-between rounded-2xl border border-border/70 bg-background/80 px-4 py-2">
              <span>
                Jour {{ $log->day_number }}
                @if ($log->date)
                  · {{ $log->date->format('d/m/Y') }}
                @endif
              </span>
              <span class="text-muted-foreground">{{ $log->hours_coded }} h</span>
            </li>
          @empty
            <li class="rounded-2xl border border-dashed border-border/70 bg-background/80 px-4 py-3 text-xs text-muted-foreground">
              Aucun log pour le moment. Renseigne ton journal du jour.
            </li>
          @endforelse
        </ul>
      </div>
    </article>
  </section>

  <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-foreground">Calendrier du challenge</h2>
    <p class="text-xs text-muted-foreground">Visualise ta progression sur {{ $targetDays }} jours. Les cases pleines représentent tes entrées complétées.</p>

    <div class="mt-4 space-y-2">
      @foreach ($calendarChunks as $chunk)
        <div class="grid grid-cols-10 gap-1">
          @foreach ($chunk as $day)
            @php($done = in_array($day, $myDoneDays, true))
            <div
              class="flex h-7 items-center justify-center rounded text-[10px] {{ $done ? 'bg-primary text-primary-foreground' : 'bg-muted text-foreground' }}"
              title="Jour {{ $day }}"
            >
              {{ $day }}
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  </section>
</div>
