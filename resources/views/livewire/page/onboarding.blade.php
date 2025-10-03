<div class="mx-auto max-w-3xl space-y-6 py-10">
  @if ($pendingInvitations->isNotEmpty())
    <x-filament::section>
      <x-slot name="heading">Invitations en attente</x-slot>
      <x-slot name="description">
        Nous avons détecté des invitations envoyées à {{ auth()->user()->email }}. Rejoignez le challenge correspondant en un clic.
      </x-slot>

      <div class="space-y-4">
        @foreach ($pendingInvitations as $invitation)
          <x-filament::card wire:key="invite-{{ $invitation->id }}">
            <x-slot name="heading">{{ $invitation->run?->title ?? 'Challenge' }}</x-slot>
            <div class="space-y-3 text-sm text-muted-foreground">
              <p>Invité par {{ $invitation->run?->owner?->name ?? 'un membre' }}</p>
              <div class="flex flex-wrap items-center gap-3">
                <x-filament::badge color="info">Code {{ $invitation->token }}</x-filament::badge>
                @if ($invitation->expires_at)
                  <span>Expire le {{ $invitation->expires_at->translatedFormat('d/m/Y H:i') }}</span>
                @endif
              </div>

              <div class="flex flex-wrap gap-3">
                <x-filament::button size="sm" wire:click="acceptInvitation('{{ $invitation->id }}')">
                  Rejoindre maintenant
                </x-filament::button>
                <x-filament::button size="sm" color="gray" tag="a" href="{{ route('challenges.show', ['run' => $invitation->challenge_run_id]) }}">
                  Voir le challenge
                </x-filament::button>
              </div>
            </div>
          </x-filament::card>
        @endforeach
      </div>
    </x-filament::section>
  @endif

  <x-filament::section>
    <x-slot name="heading">Configuration rapide</x-slot>
    <x-slot name="description">
      Personnalisez votre arrivée : nous préparons automatiquement le bon challenge pour vous.
    </x-slot>

    <form wire:submit.prevent="submit" class="space-y-6">
      {{ $this->form }}

      <div class="flex flex-wrap items-center justify-between gap-3">
        <x-filament::button color="gray" tag="a" href="{{ route('dashboard') }}">
          Passer pour l'instant
        </x-filament::button>
        <x-filament::button type="submit">
          Continuer
        </x-filament::button>
      </div>
    </form>
  </x-filament::section>

  @if ($publicChallenges->isNotEmpty())
    <x-filament::section>
      <x-slot name="heading">Challenges publics suggérés</x-slot>
      <x-slot name="description">
        Sélectionnez-en un dans le formulaire si vous souhaitez rejoindre une communauté existante.
      </x-slot>

      <div class="grid gap-3 md:grid-cols-2">
        @foreach ($publicChallenges as $challenge)
          <x-filament::card>
            <x-slot name="heading">{{ $challenge->title }}</x-slot>
            <div class="space-y-2 text-sm text-muted-foreground" x-data="{ copied: false, copy(text) { navigator.clipboard.writeText(text); this.copied = true; setTimeout(() => this.copied = false, 2000); } }" x-cloak>
              <p>Animé par {{ $challenge->owner?->name ?? 'un membre' }}</p>
              <p>Début : {{ $challenge->start_date->translatedFormat('d/m/Y') }}</p>
              <div class="flex items-center justify-between gap-2">
                <span>{{ $challenge->target_days }} jours • Code : <span class="font-semibold text-foreground">{{ $challenge->public_join_code }}</span></span>
                <button type="button" @click="copy('{{ $challenge->public_join_code }}')" class="text-xs px-2 py-1 rounded border border-primary/40 text-primary hover:bg-primary/10">
                  <span x-show="! copied">Copier</span>
                  <span x-show="copied">Copié !</span>
                </button>
              </div>
            </div>
          </x-filament::card>
        @endforeach
      </div>
    </x-filament::section>
  @endif
</div>
