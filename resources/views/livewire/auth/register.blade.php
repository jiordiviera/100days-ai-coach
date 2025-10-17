<div class="w-full max-w-md border border-border rounded-xl shadow-2xs bg-background">
    <div class="p-6 sm:p-8">
        <div class="mb-6">
            <x-filament::link
                href="{{ route('home') }}"
                wire:navigate
                class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-primary"
            >
                <span aria-hidden="true">&larr;</span>
                <span>{{ __('Back to home') }}</span>
            </x-filament::link>
        </div>

        <div class="text-center">
            <h1 class="block text-2xl font-bold">{{ __('Create an account') }}</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                {{ __('Already have an account?') }}
                <x-filament::link href="{{ route('login') }}" wire:navigate>
                    {{ __('Sign in') }}
                </x-filament::link>
            </p>
        </div>

        <div class="mt-6">
            <x-filament::button
                tag="a"
                href="{{ route('auth.github.redirect') }}"
                class="w-full justify-center"
                color="gray"
                outlined
            >
                @include('components.ui.icons.github')
                <span>{{ __('Sign up with GitHub') }}</span>
            </x-filament::button>
        </div>

        <div class="my-6 grid grid-cols-[1fr_auto_1fr] items-center gap-3 text-xs text-muted-foreground">
            <span class="h-px bg-border"></span>
            <span>{{ __('or') }}</span>
            <span class="h-px bg-border"></span>
        </div>

        <div>
            <form wire:submit.prevent="submit" class="grid gap-y-4">
                {{ $this->form }}

                <x-filament::button class="w-full" type="submit" wire:loading.attr="disabled">
                    {{ __('Create account') }}
                </x-filament::button>
            </form>
        </div>

        <p class="mt-6 text-xs text-muted-foreground text-center">
            {{ __('Once created, you will be redirected to the daily journal to start your streak.') }}
        </p>
    </div>
</div>
