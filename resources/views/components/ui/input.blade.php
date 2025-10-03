@props(["label" => null, "id" => null, "name" => null])

@php
    $isPassword = $attributes->get("type") === "password";
    $isTextarea = $attributes->get("type") === "textarea";
    $name = $name ?? $attributes->get('wire:model') ?? $attributes->get('wire:model.defer') ?? $attributes->get('wire:model.lazy') ?? null;
@endphp

<div class="mb-4">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm mb-2">{{ $label }}</label>
    @endif

    <div class="relative" @if($isPassword) x-data="{ show: false }" @endif>

        @if($isTextarea)
            <textarea {{$attributes}} ></textarea>
        @else
            <input
                @if($isPassword) :type="show ? 'text' : 'password'" @endif
                {{ $attributes->merge(["class" => $isPassword ? "pr-14" : ""]) }}
            />
        @endif
        @if ($isPassword)
            <span
                type="button"
                class="absolute cursor-pointer inset-y-0 right-0 h-full px-3 min-w-11 flex items-center gap-1 border-l border-border text-muted-foreground hover:text-foreground hover:bg-input/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded-r-md"
                x-on:click="show = !show"
                x-on:keydown.enter.prevent="show = !show"
                x-on:keydown.space.prevent="show = !show"
                x-bind:aria-pressed="show.toString()"
                :title="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                aria-controls="{{ $id }}"
            >
        <svg
            x-cloak
            x-show="!show"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            class="size-5"
            aria-hidden="true"
        >
          <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.6"
              d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 5 12 5c4.64 0 8.577 2.51 9.964 6.678.07.21.07.434 0 .644C20.577 16.49 16.64 19 12 19c-4.64 0-8.577-2.51-9.964-6.678Z"
          />
          <circle cx="12" cy="12" r="3" stroke-width="1.6"/>
        </svg>
        <svg
            x-cloak
            x-show="show"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            class="size-5"
            aria-hidden="true"
        >
          <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.6"
              d="M3 3l18 18M10.584 10.59A3 3 0 0 0 8.59 12.58M6.36 6.364C4.51 7.53 3.084 9.21 2.036 11.678a1.012 1.012 0 0 0 0 .644C3.423 16.49 7.36 19 12 19c1.61 0 3.15-.3 4.54-.85M9.88 4.14C10.56 4.05 11.27 4 12 4c4.64 0 8.577 2.51 9.964 6.678.07.21.07.434 0 .644-.37 1.08-.9 2.06-1.57 2.91"
          />
        </svg>
      </span>
        @endif
    </div>

    @error($name)
    <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
    @enderror
</div>
