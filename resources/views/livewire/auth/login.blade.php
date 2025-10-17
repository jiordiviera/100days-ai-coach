<div
    x-data="githubAuthPopup('{{ route('daily-challenge') }}')"
    x-init="init()"
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

@once
    @include('components.ui.github-auth-popup-script')
@endonce

        <div class="text-center">
            <h1 class="block text-2xl font-bold">{{ __('Sign in') }}</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                {{ __('Don’t have an account?') }}
                <x-filament::link
                    href="{{ route('register') }}"
                    wire:navigate
                >
                    {{ __('Create an account') }}
                </x-filament::link>
            </p>
        </div>

        @if (session('auth.github.error'))
            <div class="mt-4 rounded-lg border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                {{ session('auth.github.error') }}
            </div>
        @endif

        <div class="mt-6">
            <x-filament::button
                tag="a"
                href="{{ route('auth.github.redirect') }}"
                class="w-full justify-center"
                color="gray"
                outlined
                x-on:click.prevent="open('{{ route('auth.github.redirect', ['popup' => '1']) }}')"
            >
                @include('components.ui.icons.github')
                <span>{{ __('Continue with GitHub') }}</span>
            </x-filament::button>
        </div>

        <div
            class="my-6 grid grid-cols-[1fr_auto_1fr] items-center gap-3 text-xs text-muted-foreground"
        >
            <span class="h-px bg-border"></span>
            <span>{{ __('or') }}</span>
            <span class="h-px bg-border"></span>
        </div>

        <div>
            <form wire:submit.prevent="submit" class="grid gap-y-4">
                {{ $this->form }}


                <div class="flex items-center justify-between">
                    <x-filament::link href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </x-filament::link>
                </div>

                <x-filament::button class="w-full" type="submit" wire:loading.attr="disabled">
                    {{ __('Sign in') }}
                </x-filament::button>
            </form>
        </div>
    </div>
</div>
