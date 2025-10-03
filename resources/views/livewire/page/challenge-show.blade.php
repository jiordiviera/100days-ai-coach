<div class="max-w-5xl mx-auto py-8 space-y-6">
  <div class="bg-muted-foreground/20 shadow rounded-lg p-6">
    <div class="grid sm:grid-cols-3 gap-4 text-sm">
      <div>
        <div class="text-muted-foreground">Participants</div>
        <div class="text-2xl font-semibold">{{ $participantsCount }}</div>
      </div>
      <div>
        <div class="text-muted-foreground">Objectif</div>
        <div class="text-2xl font-semibold">{{ $run->target_days }} jours</div>
      </div>
      <div>
        <div class="text-muted-foreground">Progression globale</div>
        <div class="text-2xl font-semibold">{{ $globalPercent }}%</div>
      </div>
    </div>
  </div>
  <div class="bg-muted-foreground/20 shadow rounded-lg p-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold">
            {{ $run->title ?? "100 Days of Code" }}
          </h1>
          <p class="text-sm text-muted-foreground mt-1">
            Début: {{ $run->start_date->format("Y-m-d") }} • Objectif:
            {{ $run->target_days }} jours
          </p>
          <p class="text-sm text-muted-foreground">
            Owner: {{ $run->owner->name }}
          </p>
          @if ($run->is_public && $run->public_join_code)
            <div
              class="mt-3 inline-flex items-center gap-2 rounded-lg border border-primary/40 bg-primary/10 px-3 py-1 text-xs"
              x-data="{ copied: false, copy(text) { navigator.clipboard.writeText(text); this.copied = true; setTimeout(() => this.copied = false, 2000); } }"
              x-cloak
            >
              <span class="font-semibold text-primary">Code public :</span>
              <span class="font-mono text-primary">{{ $run->public_join_code }}</span>
              <button
                type="button"
                class="rounded border border-primary px-2 py-1 text-primary hover:bg-primary hover:text-white"
                @click="copy('{{ $run->public_join_code }}')"
              >
                <span x-show="! copied">Copier</span>
                <span x-show="copied">Copié !</span>
              </button>
            </div>
          @endif
        </div>
      <div class="flex gap-2">
        <x-filament::button tag="a" href="{{ route('challenges.insights', $run->id) }}" color="gray">
          Insights
        </x-filament::button>
        <x-filament::button tag="a" href="{{ route('daily-challenge') }}">
          Journal du jour
        </x-filament::button>
      </div>
    </div>
  </div>

  <div class="bg-muted-foreground/20 shadow rounded-lg p-6">
    <h2 class="font-semibold mb-4">Participants</h2>
    <div class="space-y-3">
      @foreach ($progress as $item)
        <div>
          <div class="flex justify-between items-center text-sm mb-1">
            <div class="flex items-center gap-2">
              <span class="font-medium">{{ $item["user"]->name }}</span>
              @if ($item["user"]->id === $run->owner_id)
                <span
                  class="text-xs px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                >
                  Owner
                </span>
              @endif
            </div>
            <div class="flex items-center gap-2">
              <span
                class="text-xs px-2 py-0.5 rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200"
              >
                Streak: {{ $item["streak"] }}
              </span>
              <span>
                {{ $item["done"] }} / {{ $run->target_days }}
                ({{ $item["percent"] }}%)
              </span>
              @if (auth()->id() === $run->owner_id && $item["user"]->id !== $run->owner_id)
                <x-filament::button
                  color="danger"
                  wire:click="removeParticipant('{{ optional($run->participantLinks->firstWhere('user_id', $item['user']->id))->getKey() }}')"
                >
                  Retirer
                </x-filament::button>
              @endif
            </div>
          </div>
          <div class="h-2 rounded bg-border overflow-hidden">
            <div
              class="h-full bg-primary"
              style="width: {{ $item["percent"] }}%"
            ></div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  @if (auth()->id() === $run->owner_id)
    <div class="bg-muted-foreground/20 shadow rounded-lg p-6 space-y-4">
      <h2 class="font-semibold">Inviter des participants</h2>
      @if (session()->has("message"))
        <div
          class="p-3 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
        >
          {{ session("message") }}
        </div>
      @endif

      <form
        wire:submit.prevent="sendInvite"
        class="flex gap-2 items-end flex-wrap"
      >
        <div class="grow min-w-60">
          {{ $this->form }}
        </div>
        <x-filament::button type="submit" color="primary">
          Envoyer l'invitation
        </x-filament::button>
      </form>
      @if ($lastInviteLink)
        <div class="text-sm">
          Lien d'invitation (également envoyé par e-mail):
          <x-filament::link class="text-primary underline truncate max-w-[200px]" href="{{ $lastInviteLink }}">
            {{ $lastInviteLink }}
          </x-filament::link>
        </div>
      @endif

      <div>
        <h3 class="font-medium mb-2">Invitations en attente</h3>
        <ul class="space-y-1">
          @forelse ($pendingInvites as $inv)
            <li class="text-sm flex justify-between items-center">
              <span>{{ $inv->email }}</span>
              <span class="flex items-center gap-3">
                <x-filament::button
                  wire:click="copyLink('{{ route('challenges.accept', $inv->token) }}')"
                  size="sm"
                >
                  Copier
                </x-filament::button>
                <x-filament::button
                  color="danger"
                  wire:confirm="Voulez-vous vraiment révoquer cette invitation ?"
                  wire:click="revokeInvite('{{ $inv->getKey() }}')"
                  size="sm"
                >
                  Révoquer
                </x-filament::button>
              </span>
            </li>
          @empty
            <li class="text-sm text-muted-foreground">
              Aucune invitation en attente.
            </li>
          @endforelse
        </ul>
      </div>
    </div>
  @endif

  <div class="bg-muted-foreground/20 shadow rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold">Mes derniers logs</h2>
      @if (auth()->id() !== $run->owner_id)
        <x-filament::button color="gray" wire:click="leave">
          Quitter le challenge
        </x-filament::button>
      @endif
    </div>
    <ul class="space-y-1 text-sm">
      @forelse ($myRecentLogs as $log)
        <li class="flex justify-between">
          <span>
            Jour {{ $log->day_number }}
            @if ($log->date)
                • {{ $log->date->format("Y-m-d") }}
            @endif
          </span>
          <span>{{ $log->hours_coded }} h</span>
        </li>
      @empty
        <li class="text-muted-foreground">Aucun log pour le moment.</li>
      @endforelse
    </ul>
  </div>

  <div class="bg-muted-foreground/20 shadow rounded-lg p-6">
    <h2 class="font-semibold mb-3">
      Calendrier ({{ $run->target_days }} jours)
    </h2>
    @php
      $cols = 10;
      $rows = ceil($run->target_days / $cols);
    @endphp

    <div class="grid grid-cols-10 gap-1">
      @for ($d = 1; $d <= $run->target_days; $d++)
        @php
          $done = in_array($d, $myDoneDays, true);
        @endphp

        <div
          class="h-6 rounded text-[10px] flex items-center justify-center {{ $done ? "bg-primary text-foreground" : "bg-muted text-foreground" }}"
          title="Jour {{ $d }}"
        >
          {{ $d }}
        </div>
      @endfor
    </div>
    <div class="text-xs text-muted-foreground mt-2">
      Votre progression jour par jour
    </div>
  </div>
</div>
