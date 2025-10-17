<div class="rounded-3xl border border-border/60 bg-card/90 p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-foreground">{{ __('Repository #100DaysOfCode') }}</h2>
            <p class="text-xs text-muted-foreground">
                {{ __('Clone the official GitHub template to track your progress day after day.') }}
            </p>
        </div>
        @if ($repository)
            <span
                class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-600">
        {{ __('Ready') }}
      </span>
        @endif
    </div>

    @if ($errorMessage)
        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($repository)
        <div class="mt-5 space-y-3 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-muted-foreground">{{ __('Repository') }}</span>
                <a
                    href="{{ $repository->repo_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-full border border-border/70 px-3 py-1 text-xs font-semibold text-muted-foreground transition hover:border-primary/50 hover:text-primary"
                >
                    {{ $repository->repo_owner }}/{{ $repository->repo_name }}
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M12.293 2.293a1 1 0 011.414 0l4 4a1 1 0 01-.707 1.707H15a1 1 0 110-2h.586L11 3.414V5a1 1 0 11-2 0V2a1 1 0 011-1h3a1 1 0 01.707.293zM5 5a3 3 0 00-3 3v7a3 3 0 003 3h7a3 3 0 003-3v-2a1 1 0 112 0v2a5 5 0 01-5 5H5a5 5 0 01-5-5V8a5 5 0 015-5h2a1 1 0 110 2H5z"
                              clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
            <div class="flex items-center justify-between text-xs text-muted-foreground">
                <span>{{ __('Visibility') }}</span>
                <span
                    class="font-medium text-foreground">{{ \Illuminate\Support\Str::ucfirst($repository->visibility) }}</span>
            </div>
        </div>
    @elseif ($isReady && ! $errorMessage)
        <form wire:submit.prevent="createRepository" class="mt-5 space-y-4">
            {{ $this->form }}
            <div class="flex items-center gap-3">
                <x-filament::button
                    size="sm"
                    type="submit"
                    :disabled="$isProcessing"

                >
                    <span wire:loading wire:target="createRepository"
                          class="h-4 w-4 animate-spin rounded-full border-2 border-primary-foreground/70 border-t-transparent"></span>
                    <span>{{ $isProcessing ? __('Creating...') : __('Create repository') }}</span>
                </x-filament::button>
                <p class="text-xs text-muted-foreground">
                    {{ __('The template :name will be cloned to the selected account.', ['name' => config('services.github.template.repository')]) }}
                </p>
            </div>
        </form>
    @endif
</div>
