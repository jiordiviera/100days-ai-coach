<div class="mx-auto max-w-5xl space-y-6 py-6">
  @if (! $run)
    <x-filament::card>
      <x-slot name="heading">Aucun challenge actif</x-slot>
      <p class="text-sm text-muted-foreground">
        Rejoignez ou créez un challenge pour consigner votre progression dans le journal quotidien.
      </p>
      <div class="mt-4 flex flex-wrap gap-2">
        <x-filament::button tag="a" href="{{ route('challenges.index') }}">
          Voir les challenges
        </x-filament::button>
        <x-filament::button tag="a" href="{{ route('dashboard') }}" color="gray">
          Retour au tableau de bord
        </x-filament::button>
      </div>
    </x-filament::card>
  @else
    <div class="grid gap-6 lg:grid-cols-3">
      <div class="space-y-4 lg:col-span-2">
        <x-filament::card>
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-xs uppercase text-muted-foreground">
                Jour {{ $currentDayNumber }}/{{ $run->target_days }}
              </p>
              <h2 class="text-xl font-semibold">
                {{ \Illuminate\Support\Carbon::parse($challengeDate)->translatedFormat('d F Y') }}
              </h2>
            </div>
            <div class="flex flex-wrap gap-2">
              <x-filament::button color="gray" size="sm" wire:click="goToDay('previous')" :disabled="! $canGoPrevious">
                Jour précédent
              </x-filament::button>
              <x-filament::button color="gray" size="sm" wire:click="goToDay('next')" :disabled="! $canGoNext">
                Jour suivant
              </x-filament::button>
            </div>
          </div>

          <div class="mt-4 text-xs text-muted-foreground">
            Défi « {{ $run->title ?? '100 Days of Code' }} » · démarré le {{ $run->start_date->translatedFormat('d/m/Y') }}
          </div>

          <div class="mt-6">
            @if ($showReminder)
              <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Pas encore de log pour aujourd’hui. Renseignez votre journée pour conserver votre streak !
              </div>
            @endif
            @if (session()->has('message'))
              <div class="mb-4 rounded-md bg-green-100 px-3 py-2 text-sm text-green-800 dark:bg-green-900/60 dark:text-green-200">
                {{ session('message') }}
              </div>
            @endif

            @if ($todayEntry && ! $isEditing)
              <div class="space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                  <x-filament::badge color="success">
                    Entrée complétée pour ce jour
                  </x-filament::badge>
                  <x-filament::button size="sm" wire:click="startEditing">
                    Modifier mon entrée
                  </x-filament::button>
                </div>

                <dl class="space-y-3 text-sm">
                  <div>
                    <dt class="text-xs uppercase text-muted-foreground">Description</dt>
                    <dd class="mt-1 whitespace-pre-line rounded-md bg-muted px-3 py-2 text-sm">
                      {{ $todayEntry->notes }}
                    </dd>
                  </div>

                  <div>
                    <dt class="text-xs uppercase text-muted-foreground">Projets travaillés</dt>
                    <dd class="mt-1 flex flex-wrap gap-2">
                      @php($projects = collect($todayEntry->projects_worked_on ?? []))

                      @if ($projects->isNotEmpty())
                        @foreach ($projects as $pid)
                          @php($project = $allProjects->firstWhere('id', $pid))
                          <x-filament::badge color="gray">
                            {{ $project?->name ?? 'Projet supprimé' }}
                          </x-filament::badge>
                        @endforeach
                      @else
                        <span class="text-muted-foreground">Aucun</span>
                      @endif
                    </dd>
                  </div>

                  <div class="grid gap-3 md:grid-cols-3">
                    <div class="rounded-md bg-muted px-3 py-2">
                      <p class="text-xs uppercase text-muted-foreground">Heures codées</p>
                      <p class="text-base font-semibold">{{ $todayEntry->hours_coded }}</p>
                    </div>
                    <div class="rounded-md bg-muted px-3 py-2">
                      <p class="text-xs uppercase text-muted-foreground">Apprentissages</p>
                      <p class="text-sm">{{ $todayEntry->learnings ?: '—' }}</p>
                    </div>
                    <div class="rounded-md bg-muted px-3 py-2">
                      <p class="text-xs uppercase text-muted-foreground">Difficultés</p>
                      <p class="text-sm">{{ $todayEntry->challenges_faced ?: '—' }}</p>
                    </div>
                  </div>
                </dl>
              </div>
            @else
              <form wire:submit.prevent="saveEntry" class="space-y-4">
                {{ $this->form }}

                <div class="flex flex-wrap gap-2 text-xs text-muted-foreground">
                  <span>Raccourcis d'heures :</span>
                  @foreach ([0.5, 1, 2, 3, 4] as $preset)
                    <button
                      type="button"
                      wire:click="$set('dailyForm.hours_coded', {{ $preset }})"
                      class="rounded-full border border-border px-3 py-1 text-foreground hover:border-primary hover:text-primary"
                    >
                      {{ rtrim(rtrim(number_format($preset, 2, ',', ' '), '0'), ',') }} h
                    </button>
                  @endforeach
                </div>

                <x-filament::button type="submit" color="primary">
                  Sauvegarder ma progression
                </x-filament::button>
                @if ($todayEntry)
                  <x-filament::button type="button" color="gray" wire:click="cancelEditing">
                    Annuler
                  </x-filament::button>
                @endif
              </form>
            @endif
          </div>

          @if ($todayEntry)
            <div class="mt-6 space-y-4" @if($shouldPollAi) wire:poll.7s="pollAiPanel" @endif>
              <div class="rounded-xl border border-border/70 bg-card px-4 py-4 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <h3 class="text-lg font-semibold">Insights IA</h3>
                    <p class="text-xs text-muted-foreground">
                      @if ($aiPanel['status'] === 'pending')
                        Génération en cours…
                      @elseif ($aiPanel['updated_at'])
                        Mis à jour le {{ optional($aiPanel['updated_at'])->translatedFormat('d/m/Y à H\hi') }}
                      @else
                        En attente d'une première génération IA.
                      @endif
                    </p>
                  </div>
                  <div class="flex flex-wrap gap-2" x-data="{
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
                    }">
                    <x-filament::button size="sm" color="gray" wire:click="regenerateAi" :disabled="$aiPanel['status'] === 'pending'">
                      Relancer l'IA
                    </x-filament::button>
                    <x-filament::button
                      size="sm"
                      color="primary"
                      x-on:click.prevent="copyDraft()"
                      x-bind:disabled="! draft"
                    >
                      <span x-show="!copied">Copier le brouillon</span>
                      <span x-show="copied" x-cloak>Copié !</span>
                    </x-filament::button>
                  </div>
                </div>

                <div class="mt-4 space-y-4">
                  <div>
                    <p class="text-xs uppercase text-muted-foreground">Résumé</p>
                    @if ($aiPanel['status'] === 'ready' && $aiPanel['summary'])
                      <div class="prose prose-sm max-w-none dark:prose-invert">
                        {!! \Illuminate\Support\Str::markdown($aiPanel['summary']) !!}
                      </div>
                    @else
                      <div class="h-20 rounded-md bg-muted/70 animate-pulse" aria-hidden="true"></div>
                    @endif
                  </div>

                  <div>
                    <p class="text-xs uppercase text-muted-foreground">Tags</p>
                    @if ($aiPanel['status'] === 'ready' && filled($aiPanel['tags']))
                      <div class="mt-1 flex flex-wrap gap-2">
                        @foreach ($aiPanel['tags'] as $tag)
                          <x-filament::badge color="primary">{{ $tag }}</x-filament::badge>
                        @endforeach
                      </div>
                    @else
                      <div class="flex gap-2">
                        <div class="h-6 w-20 rounded-full bg-muted/70 animate-pulse"></div>
                        <div class="h-6 w-12 rounded-full bg-muted/50 animate-pulse"></div>
                        <div class="h-6 w-16 rounded-full bg-muted/40 animate-pulse"></div>
                      </div>
                    @endif
                  </div>

                  <div class="grid gap-4 md:grid-cols-2">
                    <div>
                      <p class="text-xs uppercase text-muted-foreground">Conseil du coach</p>
                      @if ($aiPanel['status'] === 'ready' && $aiPanel['coach_tip'])
                        <p class="mt-1 rounded-md bg-muted px-3 py-2 text-sm">{{ $aiPanel['coach_tip'] }}</p>
                      @else
                        <div class="h-14 rounded-md bg-muted/60 animate-pulse"></div>
                      @endif
                    </div>
                    <div>
                      <p class="text-xs uppercase text-muted-foreground">Brouillon de partage</p>
                      @if ($aiPanel['status'] === 'ready' && $aiPanel['share_draft'])
                        <pre class="mt-1 max-h-48 overflow-auto rounded-md bg-muted px-3 py-2 text-xs">{{ $aiPanel['share_draft'] }}</pre>
                      @else
                        <div class="h-20 rounded-md bg-muted/60 animate-pulse"></div>
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
            </div>
          @endif
        </x-filament::card>
      </div>

      <div class="space-y-4">
        <x-filament::card heading="Mes statistiques">
          <dl class="space-y-2 text-sm">
            <div class="flex items-center justify-between">
              <dt>Streak actuel</dt>
              <dd>{{ $summary['streak'] ?? 0 }} {{ \Illuminate\Support\Str::plural('jour', $summary['streak'] ?? 0) }}</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Entrées totales</dt>
              <dd>{{ $summary['totalLogs'] ?? 0 }}</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Heures totales</dt>
              <dd>{{ $summary['totalHours'] ?? 0 }} h</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Heures cette semaine</dt>
              <dd>{{ $summary['hoursThisWeek'] ?? 0 }} h</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Progression</dt>
              <dd>{{ $summary['completion'] ?? 0 }}%</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Heures moyennes</dt>
              <dd>{{ $summary['averageHours'] ?? 0 }} h</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt>Dernier log</dt>
              <dd>
                @if (! empty($summary['lastLogAt']))
                  {{ $summary['lastLogAt']->translatedFormat('d/m/Y') }}
                @else
                  —
                @endif
              </dd>
            </div>
          </dl>
        </x-filament::card>

        <x-filament::card heading="Historique récent">
          <div class="space-y-2">
            @forelse ($history as $entry)
              <button
                type="button"
                wire:click="setDate('{{ $entry['date'] ?? $challengeDate }}')"
                class="w-full rounded-lg border border-border/60 bg-background px-3 py-2 text-left text-sm hover:border-primary"
              >
                <div class="flex items-center justify-between">
                  <span>Jour {{ $entry['day_number'] }}</span>
                  <span class="text-xs text-muted-foreground">
                    {{ $entry['date'] ? \Illuminate\Support\Carbon::parse($entry['date'])->translatedFormat('d/m') : '—' }}
                  </span>
                </div>
                <div class="mt-1 flex items-center justify-between text-xs text-muted-foreground">
                  <span>{{ $entry['hours'] }} h</span>
                  <span>
                    {{ count($entry['projects']) }} {{ \Illuminate\Support\Str::plural('projet', count($entry['projects'])) }}
                  </span>
                </div>
              </button>
            @empty
              <p class="text-sm text-muted-foreground">Pas encore d'historique.</p>
            @endforelse
          </div>
        </x-filament::card>

        <x-filament::card heading="Projets les plus actifs">
          <div class="space-y-2 text-sm">
            @forelse ($projectBreakdown as $project)
              <div class="flex items-center justify-between">
                <span>{{ $project['name'] }}</span>
                <span class="text-xs text-muted-foreground">{{ $project['count'] }} {{ \Illuminate\Support\Str::plural('jour', $project['count']) }}</span>
              </div>
            @empty
              <p class="text-sm text-muted-foreground">Aucun projet lié pour l'instant.</p>
            @endforelse
          </div>
        </x-filament::card>
      </div>
    </div>
  @endif
</div>
