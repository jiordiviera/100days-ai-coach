@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Carbon;

    $formatHours = static fn ($value) => rtrim(rtrim(number_format((float) $value, 1, '.', ' '), '0'), '.');
    $formatNumber = static fn ($value) => number_format((int) $value, 0, '.', ' ');
@endphp

<div class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6 lg:px-0">
  @if (! $run)
    <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
      <div class="absolute inset-0">
        <div class="absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
        <div class="absolute -right-12 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
      </div>

      <div class="relative grid gap-10 p-8 lg:grid-cols-[1.2fr_0.8fr] lg:p-10">
        <div class="space-y-6">
          <div class="space-y-2">
            <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
              Journal quotidien
            </span>
            <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">Aucun challenge actif pour l'instant</h1>
            <p class="max-w-xl text-sm text-muted-foreground sm:text-base">
              Rejoins un run #100DaysOfCode ou crée ton propre challenge pour commencer à consigner tes shipments et débloquer des badges.
            </p>
          </div>

          <div class="flex flex-wrap gap-3">
            <a
              wire:navigate
              href="{{ route('challenges.index') }}"
              class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:shadow-xl hover:shadow-primary/30"
            >
              Explorer les challenges
            </a>
            <a
              wire:navigate
              href="{{ route('dashboard') }}"
              class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-5 py-2.5 text-sm font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
              Retour au tableau de bord
            </a>
          </div>
        </div>

        <div class="space-y-5 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl">
          <div>
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Rejoindre via un code</p>
            <p class="text-sm text-muted-foreground">Colle ici un code public ou une invitation privée pour démarrer immédiatement.</p>
          </div>
          <form wire:submit.prevent="joinWithCode" class="space-y-3">
            <div class="flex flex-col gap-3 sm:flex-row">
              <input
                type="text"
                wire:model.defer="inviteCode"
                placeholder="Code d'invitation ou challenge public"
                class="flex-1 rounded-2xl border border-border/70 bg-background px-4 py-2 text-sm text-foreground focus:border-primary focus:outline-none"
              >
              <button
                type="submit"
                wire:loading.attr="disabled"
                class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground shadow transition hover:shadow-lg"
              >
                Rejoindre
              </button>
            </div>
          </form>

          @if ($pendingInvitations->isNotEmpty())
            <div class="space-y-3 rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
              <h3 class="text-sm font-semibold text-foreground">Invitations en attente</h3>
              <ul class="space-y-2 text-sm">
                @foreach ($pendingInvitations as $invitation)
                  <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border/60 bg-background px-3 py-2">
                    <div>
                      <p class="font-medium text-foreground">{{ $invitation->run->title }}</p>
                      <p class="text-xs text-muted-foreground">Invité par {{ $invitation->run->owner?->name ?? 'un membre' }}</p>
                    </div>
                    <button
                      type="button"
                      wire:click="acceptInvitation('{{ $invitation->id }}')"
                      wire:loading.attr="disabled"
                      class="rounded-full bg-primary px-3 py-1.5 text-xs font-semibold text-primary-foreground shadow hover:shadow-lg"
                    >
                      Accepter
                    </button>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      </div>
    </section>
  @else
    @php
      $challengeDateParsed = Carbon::parse($challengeDate);
      $formattedDate = $challengeDateParsed->translatedFormat('d F Y');
      $dayLabel = "Jour {$currentDayNumber} sur {$run->target_days}";
      $progressPercent = $summary['completion'] ?? 0;
    @endphp
    <section class="relative overflow-hidden rounded-3xl border border-border/60 bg-gradient-to-br from-primary/5 via-background to-background shadow-lg">
      <div class="absolute inset-0">
        <div class="absolute -left-16 bottom-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"></div>
        <div class="absolute -right-12 top-0 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>
      </div>

      <div class="relative grid gap-10 p-8 lg:grid-cols-[1.25fr_0.75fr] lg:p-10">
        <div class="space-y-6">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-2">
              <span class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
                {{ $dayLabel }}
              </span>
              <h1 class="text-3xl font-semibold text-foreground sm:text-4xl">{{ $formattedDate }}</h1>
              <p class="max-w-xl text-sm text-muted-foreground sm:text-base">
                Challenge « {{ $run->title ?? '100DaysOfCode' }} » animé par {{ $run->owner->name }}. Consigne ton shipment pour garder ta streak.
              </p>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                wire:click="goToDay('previous')"
                @class([
                    'rounded-full border px-4 py-2 text-xs font-semibold transition',
                    'border-border/70 text-muted-foreground hover:border-primary hover:text-primary' => $canGoPrevious,
                    'border-border/40 text-muted-foreground/70 cursor-not-allowed' => ! $canGoPrevious,
                ])
                @disabled(! $canGoPrevious)
              >
                Jour précédent
              </button>
              <button
                type="button"
                wire:click="goToDay('next')"
                @class([
                    'rounded-full border px-4 py-2 text-xs font-semibold transition',
                    'border-border/70 text-muted-foreground hover:border-primary hover:text-primary' => $canGoNext,
                    'border-border/40 text-muted-foreground/70 cursor-not-allowed' => ! $canGoNext,
                ])
                @disabled(! $canGoNext)
              >
                Jour suivant
              </button>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
              <p class="text-xs uppercase tracking-widest text-muted-foreground">Streak actuel</p>
              <p class="mt-2 text-2xl font-semibold text-foreground">{{ $summary['streak'] ?? 0 }} {{ Str::plural('jour', $summary['streak'] ?? 0) }}</p>
              <p class="text-xs text-muted-foreground">Ne casse pas la série</p>
            </div>
            <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
              <p class="text-xs uppercase tracking-widest text-muted-foreground">Logs enregistrés</p>
              <p class="mt-2 text-2xl font-semibold text-foreground">{{ $summary['totalLogs'] ?? 0 }}</p>
              <p class="text-xs text-muted-foreground">Depuis le début du run</p>
            </div>
            <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
              <p class="text-xs uppercase tracking-widest text-muted-foreground">Heures cumulées</p>
              <p class="mt-2 text-2xl font-semibold text-foreground">{{ $formatHours($summary['totalHours'] ?? 0) }} h</p>
              <p class="text-xs text-muted-foreground">{{ $formatHours($summary['hoursThisWeek'] ?? 0) }} h cette semaine</p>
            </div>
            <div class="rounded-2xl border border-border/70 bg-card/90 p-4">
              <p class="text-xs uppercase tracking-widest text-muted-foreground">Progression</p>
              <p class="mt-2 text-2xl font-semibold text-foreground">{{ $progressPercent }}%</p>
              <p class="text-xs text-muted-foreground">Objectif : {{ $run->target_days }} jours</p>
            </div>
          </div>
        </div>

        <div class="relative space-y-4 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-xl">
          <div>
            <p class="text-xs uppercase tracking-widest text-muted-foreground">Raccourcis</p>
            <h2 class="mt-1 text-lg font-semibold text-foreground">Actions rapides</h2>
          </div>
          <dl class="space-y-3 text-sm text-muted-foreground">
            <div class="flex items-center justify-between">
              <dt>Dernier log</dt>
              <dd>
                @if ($summary['lastLogAt'] ?? false)
                  {{ $summary['lastLogAt']->translatedFormat('d/m/Y') }}
                @else
                  —
                @endif
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Projets actifs</dt>
              <dd>{{ count($projectBreakdown) }}</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Entrées restantes</dt>
              <dd>{{ max(0, $run->target_days - ($summary['totalLogs'] ?? 0)) }}</dd>
            </div>
          </dl>
          @if (auth()->id() !== $run->owner_id)
            <button
              type="button"
              wire:confirm="Quitter le challenge ?"
              wire:click="leave"
              class="w-full rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
            >
              Quitter le challenge
            </button>
          @endif
        </div>
      </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
      <article class="space-y-6 rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
        @if ($showReminder)
          <div class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Pas encore de log pour aujourd'hui. Renseigne ta journée pour conserver ta streak !
          </div>
        @endif
        @if (session()->has('message'))
          <div class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ session('message') }}
          </div>
        @endif

        @if ($todayEntry && ! $isEditing)
          <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">
                Entrée complétée pour ce jour
              </span>
              <button
                type="button"
                wire:click="startEditing"
                class="rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
              >
                Modifier mon entrée
              </button>
            </div>

            <div class="space-y-4">
              <div>
                <p class="text-xs uppercase tracking-widest text-muted-foreground">Description</p>
                <p class="mt-1 whitespace-pre-line rounded-2xl border border-border/70 bg-background/80 px-4 py-3 text-sm">
                  {{ $todayEntry->notes ?: '—' }}
                </p>
              </div>

              <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
                  <p class="text-xs uppercase tracking-widest text-muted-foreground">Heures codées</p>
                  <p class="mt-1 text-base font-semibold text-foreground">{{ $formatHours($todayEntry->hours_coded) }}</p>
                </div>
                <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
                  <p class="text-xs uppercase tracking-widest text-muted-foreground">Apprentissages</p>
                  <p class="mt-1 text-sm text-foreground">{{ $todayEntry->learnings ?: '—' }}</p>
                </div>
                <div class="rounded-2xl border border-border/70 bg-background/80 px-4 py-3">
                  <p class="text-xs uppercase tracking-widest text-muted-foreground">Difficultés</p>
                  <p class="mt-1 text-sm text-foreground">{{ $todayEntry->challenges_faced ?: '—' }}</p>
                </div>
              </div>

              <div>
                <p class="text-xs uppercase tracking-widest text-muted-foreground">Projets travaillés</p>
                <div class="mt-2 flex flex-wrap gap-2">
                  @php($projects = collect($todayEntry->projects_worked_on ?? []))
                  @forelse ($projects as $pid)
                    @php($project = $allProjects->firstWhere('id', $pid))
                    <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $project?->name ?? 'Projet supprimé' }}</span>
                  @empty
                    <span class="text-sm text-muted-foreground">Aucun projet associé.</span>
                  @endforelse
                </div>
              </div>
            </div>
          </div>
        @else
          <form wire:submit.prevent="saveEntry" class="space-y-5">
            {{ $this->form }}

            <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
              <span>Raccourcis :</span>
              @foreach ([0.5, 1, 2, 3, 4] as $preset)
                @php($label = rtrim(rtrim(number_format($preset, 1, '.', ' '), '0'), '.'))
                <button
                  type="button"
                  wire:click="$set('dailyForm.hours_coded', {{ $preset }})"
                  class="rounded-full border border-border/70 px-3 py-1 text-foreground transition hover:border-primary hover:text-primary"
                >
                  {{ $label }} h
                </button>
              @endforeach
            </div>

            <div class="flex flex-wrap gap-2">
              <button
                type="submit"
                class="rounded-full bg-primary px-6 py-2 text-sm font-semibold text-primary-foreground shadow transition hover:shadow-lg"
              >
                Sauvegarder ma progression
              </button>
              @if ($todayEntry)
                <button
                  type="button"
                  wire:click="cancelEditing"
                  class="rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                >
                  Annuler
                </button>
              @endif
            </div>
          </form>
        @endif

        @if ($todayEntry)
          <div class="rounded-3xl border border-border/60 bg-background/90 p-6 shadow-sm" @if($shouldPollAi) wire:poll.7s="pollAiPanel" @endif>
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h2 class="text-lg font-semibold text-foreground">Insights IA</h2>
                <p class="text-xs text-muted-foreground">
                  @if ($aiPanel['status'] === 'pending')
                    Génération en cours...
                  @elseif ($aiPanel['updated_at'])
                    Mis à jour le {{ optional($aiPanel['updated_at'])->translatedFormat('d/m/Y à H\hi') }}
                  @else
                    En attente d'une première génération.
                  @endif
                </p>
              </div>
              <div
                class="flex flex-wrap gap-2"
                x-data="{
                    copied: false,
                    draft: {{ Js::from($aiPanel['share_draft'] ?? '') }},
                    copyDraft() {
                      if (! this.draft) {
                        return;
                      }

                      navigator.clipboard.writeText(this.draft);
                      this.copied = true;
                      setTimeout(() => this.copied = false, 2000);
                    }
                }"
              >
                <button
                  type="button"
                  wire:click="regenerateAi"
                  @disabled($aiPanel['status'] === 'pending')
                  class="rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                >
                  Relancer l'IA
                </button>
                <button
                  type="button"
                  @click.prevent="copyDraft()"
                  x-bind:disabled="! draft"
                  class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow transition hover:shadow-lg"
                >
                  <span x-show="!copied">Copier le brouillon</span>
                  <span x-show="copied" x-cloak>Copié !</span>
                </button>
              </div>
            </div>

            <div class="mt-4 space-y-4">
              <div>
                <p class="text-xs uppercase tracking-widest text-muted-foreground">Résumé</p>
                @if ($aiPanel['status'] === 'ready' && $aiPanel['summary'])
                  <div class="prose prose-sm max-w-none dark:prose-invert">
                    {!! Str::markdown($aiPanel['summary']) !!}
                  </div>
                @else
                  <div class="h-20 rounded-2xl bg-muted/70 animate-pulse" aria-hidden="true"></div>
                @endif
              </div>

              <div>
                <p class="text-xs uppercase tracking-widest text-muted-foreground">Tags</p>
                @if ($aiPanel['status'] === 'ready' && filled($aiPanel['tags']))
                  <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($aiPanel['tags'] as $tag)
                      <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ $tag }}</span>
                    @endforeach
                  </div>
                @else
                  <div class="flex gap-2">
                    <div class="h-6 w-20 rounded-full bg-muted/70 animate-pulse"></div>
                    <div class="h-6 w-14 rounded-full bg-muted/60 animate-pulse"></div>
                    <div class="h-6 w-16 rounded-full bg-muted/40 animate-pulse"></div>
                  </div>
                @endif
              </div>

              <div class="grid gap-4 md:grid-cols-2">
                <div>
                  <p class="text-xs uppercase tracking-widest text-muted-foreground">Conseil du coach</p>
                  @if ($aiPanel['status'] === 'ready' && $aiPanel['coach_tip'])
                    <p class="mt-1 rounded-2xl border border-border/70 bg-background/80 px-4 py-2 text-sm">{{ $aiPanel['coach_tip'] }}</p>
                  @else
                    <div class="h-16 rounded-2xl bg-muted/60 animate-pulse"></div>
                  @endif
                </div>
                <div>
                  <p class="text-xs uppercase tracking-widest text-muted-foreground">Brouillon de partage</p>
                  @if ($aiPanel['status'] === 'ready' && $aiPanel['share_draft'])
                    <pre class="mt-1 max-h-48 overflow-auto rounded-2xl border border-border/70 bg-background/80 px-4 py-2 text-xs">{{ $aiPanel['share_draft'] }}</pre>
                  @else
                    <div class="h-20 rounded-2xl bg-muted/60 animate-pulse"></div>
                  @endif
                </div>
              </div>

              @if ($aiPanel['status'] === 'ready' && $aiPanel['model'])
                <p class="text-xs text-muted-foreground">
                  Généré avec {{ $aiPanel['model'] }} · {{ $aiPanel['latency_ms'] ?? '—' }} ms · ${{ number_format((float) ($aiPanel['cost_usd'] ?? 0), 3) }}
                </p>
              @endif
            </div>
          </div>
        @endif
      </article>

      <aside class="space-y-6">
        @if ($githubRepository)
          <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h2 class="text-lg font-semibold text-foreground">Repository GitHub</h2>
                <p class="text-xs text-muted-foreground">Consigne tes logs dans ton repo dédié.</p>
              </div>
              <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                {{ \Illuminate\Support\Str::ucfirst($githubRepository['visibility'] ?? 'private') }}
              </span>
            </div>
            <div class="mt-4 flex items-center justify-between text-sm">
              <span class="font-medium text-foreground">{{ $githubRepository['label'] }}</span>
              <a
                href="{{ $githubRepository['url'] }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 rounded-full border border-border/70 px-3 py-1 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
              >
                Ouvrir
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.293 2.293a1 1 0 011.414 0l4 4a1 1 0 01-.707 1.707H15a1 1 0 110-2h.586L11 3.414V5a1 1 0 11-2 0V2a1 1 0 011-1h3a1 1 0 01.707.293zM5 5a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-2a1 1 0 112 0v2a5 5 0 01-5 5H5a5 5 0 01-5-5V8a5 5 0 015-5h2a1 1 0 110 2H5z" clip-rule="evenodd" />
                </svg>
              </a>
            </div>
          </section>
        @endif

        <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold text-foreground">Partage public</h2>
              <p class="text-xs text-muted-foreground">Génère un lien public en lecture seule pour ton journal.</p>
            </div>
            @if ($publicShare)
              <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">Actif</span>
            @else
              <span class="inline-flex items-center rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-600">Inactif</span>
            @endif
          </div>

          @if ($publicShare)
            <div class="mt-4 space-y-3">
              <div class="rounded-2xl border border-border/70 bg-background/80 p-3 text-xs">
                <div class="flex items-center justify-between gap-2">
                  <span class="truncate">{{ $publicShare['url'] }}</span>
                  <button
                    type="button"
                    onclick="navigator.clipboard.writeText('{{ $publicShare['url'] }}'); this.innerText='Copié !'; setTimeout(() => this.innerText='Copier', 2000);"
                    class="inline-flex items-center gap-2 rounded-full border border-border/70 px-3 py-1 font-semibold text-xs text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                  >
                    Copier
                  </button>
                </div>
              </div>
              <button
                type="button"
                wire:click="disablePublicShare"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-full border border-border/70 px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-destructive/50 hover:text-destructive"
              >
                Désactiver le partage
              </button>
            </div>
          @else
            <div class="mt-4 space-y-3">
              <p class="text-xs text-muted-foreground">
                Enregistre ton entrée du jour puis génère un lien public pour la partager sur les réseaux.
              </p>
              <button
                type="button"
                wire:click="enablePublicShare"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow transition hover:shadow-lg"
              >
                Générer le lien public
              </button>
            </div>
          @endif
        </section>

        <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-foreground">Mes statistiques</h2>
          <dl class="mt-4 space-y-3 text-sm">
            <div class="flex items-center justify-between">
              <dt>Streak actuel</dt>
              <dd>{{ $summary['streak'] ?? 0 }} {{ Str::plural('jour', $summary['streak'] ?? 0) }}</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Entrées totales</dt>
              <dd>{{ $summary['totalLogs'] ?? 0 }}</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Heures cumulées</dt>
              <dd>{{ $formatHours($summary['totalHours'] ?? 0) }} h</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Heures moyennes / log</dt>
              <dd>{{ $formatHours($summary['averageHours'] ?? 0) }} h</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Heures cette semaine</dt>
              <dd>{{ $formatHours($summary['hoursThisWeek'] ?? 0) }} h</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Progression</dt>
              <dd>{{ $summary['completion'] ?? 0 }}%</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Dernier log</dt>
              <dd>
                @if ($summary['lastLogAt'] ?? false)
                  {{ $summary['lastLogAt']->translatedFormat('d/m/Y') }}
                @else
                  —
                @endif
              </dd>
            </div>
          </dl>
        </section>

        <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-foreground">Historique récent</h2>
          <div class="mt-3 space-y-2 text-sm">
            @forelse ($history as $entry)
              <button
                type="button"
                wire:click="setDate('{{ $entry['date'] ?? $challengeDate }}')"
                class="w-full rounded-2xl border border-border/60 bg-background/80 px-4 py-2 text-left transition hover:border-primary/50 hover:text-primary"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span>Jour {{ $entry['day_number'] }}</span>
                    @if ($entry['retro'] ?? false)
                      <span class="inline-flex items-center rounded-full bg-amber-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-400">
                        Rétro
                      </span>
                    @endif
                  </div>
                  <span class="text-xs text-muted-foreground">
                    {{ $entry['date'] ? Carbon::parse($entry['date'])->translatedFormat('d/m') : '—' }}
                  </span>
                </div>
                <div class="mt-1 flex items-center justify-between text-xs text-muted-foreground">
                  <span>{{ $formatHours($entry['hours']) }} h</span>
                  <span>{{ count($entry['projects']) }} {{ Str::plural('projet', count($entry['projects'])) }}</span>
                </div>
              </button>
            @empty
              <p class="text-xs text-muted-foreground">Pas encore d'historique à afficher.</p>
            @endforelse
          </div>
        </section>

        <section class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-foreground">Projets les plus actifs</h2>
          <div class="mt-3 space-y-2 text-sm">
            @forelse ($projectBreakdown as $project)
              <div class="flex items-center justify-between rounded-2xl border border-border/60 bg-background/80 px-3 py-2">
                <span>{{ $project['name'] }}</span>
                <span class="text-xs text-muted-foreground">{{ $project['count'] }} {{ Str::plural('jour', $project['count']) }}</span>
              </div>
            @empty
              <p class="text-xs text-muted-foreground">Associe ton journal à un projet pour voir ici la répartition.</p>
            @endforelse
          </div>
        </section>
      </aside>
    </section>
  @endif
</div>
