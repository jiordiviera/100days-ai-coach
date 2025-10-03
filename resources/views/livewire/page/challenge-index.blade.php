<div class="max-w-5xl mx-auto py-8 space-y-8">
    @if (session()->has('message'))
        <div class="p-3 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
            {{ session('message') }}
        </div>
    @endif

        <x-filament::section >
            <x-slot name="heading">Créer un challenge</x-slot>
        <form wire:submit.prevent="create" class="space-y-4">
            {{ $this->form }}

            @if ($hasActiveChallenge)
                <p class="text-sm text-muted-foreground">
                    Terminez votre challenge actuel avant d'en démarrer un nouveau.
                </p>
            @endif

            <x-filament::button type="submit" color="primary" :disabled="$hasActiveChallenge">
                Créer
            </x-filament::button>
        </form>
        </x-filament::section>

    <div class="grid md:grid-cols-2 gap-6">
        <x-filament::section >
            <x-slot name="heading">Mes challenges</x-slot>
            <ul class="space-y-2">
                @forelse ($owned as $run)
                    <li class="flex justify-between items-center">
                        <div>
                            <div class="font-medium">
                                {{ $run->title ?? "100 Days of Code" }}
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Début: {{ $run->start_date->format('Y-m-d') }} •
                                {{ $run->target_days }} jours
                            </div>
                        </div>
                        <x-filament::button tag="a" href="{{ route('challenges.show', $run) }}" color="gray">
                            Ouvrir
                        </x-filament::button>
                    </li>
                @empty
                    <li class="text-sm text-muted-foreground">Aucun challenge créé.</li>
                @endforelse
            </ul>
        </x-filament::section>

        <x-filament::section >
            <x-slot name="heading">Challenges rejoints</x-slot>
            <ul class="space-y-2">
                @forelse ($joined as $run)
                    <li class="flex justify-between items-center">
                        <div>
                            <div class="font-medium">
                                {{ $run->title ?? "100 Days of Code" }}
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Début: {{ $run->start_date->format('Y-m-d') }} •
                                {{ $run->target_days }} jours
                            </div>
                        </div>
                        <x-filament::button tag="a" href="{{ route('challenges.show', $run) }}" color="gray">
                            Ouvrir
                        </x-filament::button>
                    </li>
                @empty
                    <li class="text-sm text-muted-foreground">
                        Aucun challenge rejoint.
                    </li>
                @endforelse
            </ul>
        </x-filament::section>
    </div>
</div>
