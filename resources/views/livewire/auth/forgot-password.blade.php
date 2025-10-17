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
                <span>{{ __('Back to home') }}</span>
            </x-filament::link>
        </div>

        <div class="text-center">
            <h1 class="block text-2xl font-bold">{{ __('Forgot your password?') }}</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                {{ __('Enter your email to receive a reset link.') }}
            </p>
        </div>

        <div class="mt-6">
            <form wire:submit.prevent="submit" class="grid gap-y-4">
                {{ $this->form }}

                <x-filament::button
                    class="w-full"
                    type="submit"
                    wire:loading.attr="disabled"
                >
                    {{ __('Send reset link') }}
                </x-filament::button>
            </form>

            <p class="mt-4 text-center text-sm text-muted-foreground">
                <x-filament::link href="{{ route('login') }}" wire:navigate>
                    {{ __('Back to sign in') }}
                </x-filament::link>
            </p>
        </div>
    </div>
</div>
