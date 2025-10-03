<div
    class="w-full max-w-md border border-border rounded-xl shadow-2xs bg-background"
>
    <div class="p-6 sm:p-8">
        <div class="mb-6">
            <x-filament::link
                href="{{ route('home') }}"
                wire:navigate
                class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-primary"
            >
                <span aria-hidden="true">&larr;</span>
                <span>Retour à l'accueil</span>
            </x-filament::link>
        </div>

        <div class="text-center">
            <h1 class="block text-2xl font-bold">Connexion</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                Pas encore de compte ?
                <x-filament::link
                    href="{{ route('register') }}"
                    wire:navigate
                >
                    Créer un compte
                </x-filament::link>
            </p>
        </div>

        <div class="mt-6">
            <x-filament::button
                class="w-full justify-center"
                color="gray"
                outlined
                disabled
            >
                @include("components.ui.icons.github")
                <span>Continuer avec GitHub (bientôt)</span>
            </x-filament::button>
        </div>

        <div
            class="my-6 grid grid-cols-[1fr_auto_1fr] items-center gap-3 text-xs text-muted-foreground"
        >
            <span class="h-px bg-border"></span>
            <span>ou</span>
            <span class="h-px bg-border"></span>
        </div>

        <div>
            <form wire:submit.prevent="submit" class="grid gap-y-4">
                {{ $this->form }}


                <div class="flex items-center justify-between">
                    <x-filament::link href="{{ route('password.request') }}" wire:navigate>
                        Mot de passe oublié ?
                    </x-filament::link>
                </div>

                <x-filament::button class="w-full" type="submit" wire:loading.attr="disabled">
                    Se connecter
                </x-filament::button>
            </form>
        </div>
    </div>
</div>
