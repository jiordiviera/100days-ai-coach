<!DOCTYPE html>
<html
    lang="{{ str_replace("_", "-", app()->getLocale()) }}"
    class="scroll-smooth dark"
>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Espace d'authentification" />
    <title>{{ $title ?? config("app.name") . " – Auth" }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link
        href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|inter:300,400,500,600,700"
        rel="stylesheet"
    />

    <!-- Styles -->
    @vite(["resources/css/app.css"])
    @livewireStyles
    @filamentStyles
</head>
<body
    class="font-sans antialiased min-h-screen overflow-x-hidden bg-background text-foreground dark"
>
@livewire('notifications')
<main class="grid lg:grid-cols-2 w-full min-h-screen">
    <!-- Colonne image (50%) -->
    <aside class="hidden lg:block relative">
        <img
            src="{{ $image ?? "/images/auth-illustration.svg" }}"
            alt="{{ $imageAlt ?? "Illustration" }}"
            class="absolute inset-0 w-full h-full object-cover"
        />
        <div
            class="absolute inset-0 bg-gradient-to-b from-background/10 to-background/30"
        ></div>
        @if (! empty($heroTitle) || ! empty($heroSubtitle))
            <div class="absolute bottom-0 left-0 right-0 p-10">
                @isset($heroTitle)
                    <h2 class="text-2xl font-semibold">{{ $heroTitle }}</h2>
                @endisset

                @isset($heroSubtitle)
                    <p class="text-sm text-muted-foreground mt-2 max-w-md">
                        {{ $heroSubtitle }}
                    </p>
                @endisset
            </div>
        @endif
    </aside>

    <!-- Colonne contenu (50%)) -->
    <section class="px-4 py-10 flex items-center justify-center">
        {{ $slot }}
    </section>
</main>

@filamentScripts
@livewireScripts
</body>
</html>
