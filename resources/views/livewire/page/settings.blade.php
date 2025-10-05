<div class="mx-auto max-w-5xl space-y-8 py-8 px-4">
    {{-- Formulaire principal --}}
    <x-filament::card class="overflow-hidden">
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Paramètres</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gérez vos préférences et informations de
                        profil</p>
                </div>
            </div>
        </x-slot>

        <form wire:submit.prevent="save" class="space-y-6">
            {{-- Rendu direct du formulaire avec style personnalisé --}}
            <div class="space-y-8">
                {{ $this->form }}
            </div>

            {{-- Boutons d'action --}}
            <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                <x-filament::button type="button" color="gray" outlined wire:click="mount">
                    Réinitialiser
                </x-filament::button>

                <x-filament::button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-50">
                    <span wire:loading.remove wire:target="save">Enregistrer les modifications</span>
                    <span wire:loading wire:loading.flex wire:target="save" class="flex items-center gap-2 flex-row">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Enregistrement...
                    </span>
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>

    {{-- Carte GitHub --}}
    @if ($profile?->github_id)
        <x-filament::card>
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" viewBox="0 0 1024 1024" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M8 0C3.58 0 0 3.58 0 8C0 11.54 2.29 14.53 5.47 15.59C5.87 15.66 6.02 15.42 6.02 15.21C6.02 15.02 6.01 14.39 6.01 13.72C4 14.09 3.48 13.23 3.32 12.78C3.23 12.55 2.84 11.84 2.5 11.65C2.22 11.5 1.82 11.13 2.49 11.12C3.12 11.11 3.57 11.7 3.72 11.94C4.44 13.15 5.59 12.81 6.05 12.6C6.12 12.08 6.33 11.73 6.56 11.53C4.78 11.33 2.92 10.64 2.92 7.58C2.92 6.71 3.23 5.99 3.74 5.43C3.66 5.23 3.38 4.41 3.82 3.31C3.82 3.31 4.49 3.1 6.02 4.13C6.66 3.95 7.34 3.86 8.02 3.86C8.7 3.86 9.38 3.95 10.02 4.13C11.55 3.09 12.22 3.31 12.22 3.31C12.66 4.41 12.38 5.23 12.3 5.43C12.81 5.99 13.12 6.7 13.12 7.58C13.12 10.65 11.25 11.33 9.47 11.53C9.76 11.78 10.01 12.26 10.01 13.01C10.01 14.08 10 14.94 10 15.21C10 15.42 10.15 15.67 10.55 15.59C13.71 14.53 16 11.53 16 8C16 3.58 12.42 0 8 0Z"
                                transform="scale(64)" fill="#ffff" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Compte GitHub connecté</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Connecté en tant que <span
                                class="font-medium text-gray-900 dark:text-white">{{ $profile->github_username ?? 'profil lié' }}</span>
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                            Votre avatar et pseudo GitHub sont synchronisés avec votre profil.
                        </p>
                    </div>
                </div>
                <div>
                    <x-filament::badge color="success">
                        Connecté
                    </x-filament::badge>
                </div>
            </div>
        </x-filament::card>
    @else
        <x-filament::card>
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" viewBox="0 0 1024 1024" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M8 0C3.58 0 0 3.58 0 8C0 11.54 2.29 14.53 5.47 15.59C5.87 15.66 6.02 15.42 6.02 15.21C6.02 15.02 6.01 14.39 6.01 13.72C4 14.09 3.48 13.23 3.32 12.78C3.23 12.55 2.84 11.84 2.5 11.65C2.22 11.5 1.82 11.13 2.49 11.12C3.12 11.11 3.57 11.7 3.72 11.94C4.44 13.15 5.59 12.81 6.05 12.6C6.12 12.08 6.33 11.73 6.56 11.53C4.78 11.33 2.92 10.64 2.92 7.58C2.92 6.71 3.23 5.99 3.74 5.43C3.66 5.23 3.38 4.41 3.82 3.31C3.82 3.31 4.49 3.1 6.02 4.13C6.66 3.95 7.34 3.86 8.02 3.86C8.7 3.86 9.38 3.95 10.02 4.13C11.55 3.09 12.22 3.31 12.22 3.31C12.66 4.41 12.38 5.23 12.3 5.43C12.81 5.99 13.12 6.7 13.12 7.58C13.12 10.65 11.25 11.33 9.47 11.53C9.76 11.78 10.01 12.26 10.01 13.01C10.01 14.08 10 14.94 10 15.21C10 15.42 10.15 15.67 10.55 15.59C13.71 14.53 16 11.53 16 8C16 3.58 12.42 0 8 0Z"
                                transform="scale(64)" fill="#ffff" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Connecter GitHub</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Associez votre compte GitHub pour faciliter la connexion et synchroniser votre profil.
                        </p>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <x-filament::button tag="a" href="{{ route('auth.github.redirect') }}" color="gray" outlined size="sm">
                        <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                clip-rule="evenodd" />
                        </svg>
                        Connecter GitHub
                    </x-filament::button>
                </div>
            </div>
        </x-filament::card>
    @endif

    {{-- Carte WakaTime --}}
    <x-filament::card>
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-800">
                    {{-- <svg class="h-6 w-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg> --}}
                    <img src="{{ asset('images/wakatime-white.svg') }}" alt="WakaTime Logo" class="h-6 w-6">
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Synchronisation WakaTime</h3>
                    @if ($hasWakatimeKey)
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Clé enregistrée. Les données seront synchronisées automatiquement chaque jour.
                        </p>
                        @if ($profile?->wakatime_settings['last_synced_at'] ?? false)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                Dernière synchro :
                                {{ \Illuminate\Support\Carbon::parse($profile->wakatime_settings['last_synced_at'])->diffForHumans() }}
                            </p>
                        @endif
                        @if ($profile?->wakatime_settings['last_error'] ?? false)
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">
                                Dernière erreur : {{ $profile->wakatime_settings['last_error'] }}
                            </p>
                        @endif
                    @else
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Ajoutez votre clé API WakaTime pour préremplir vos daily logs avec le temps codé réel.
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                            Vous pouvez générer une clé depuis votre tableau de bord WakaTime.
                        </p>
                    @endif
                </div>
            </div>
            <div>
                <x-filament::badge :color="$hasWakatimeKey ? 'success' : 'gray'">
                    {{ $hasWakatimeKey ? 'Activé' : 'Inactif' }}
                </x-filament::badge>
            </div>
        </div>
    </x-filament::card>
</div>