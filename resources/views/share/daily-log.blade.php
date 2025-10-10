@php
  $displayName = $user->profile?->username ?? $user->name ?? 'Participant';
  $minutes = isset($meta['hours']) ? (int) round(((float) $meta['hours']) * 60) : null;
  $entryDate = isset($meta['date']) && $meta['date'] ? \Illuminate\Support\Carbon::parse($meta['date'])->translatedFormat('d F Y') : null;
  $challengeTitle = $challenge?->title ?? '#100DaysOfCode';
@endphp

<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@seo('title', 'Journal partagé — '.$displayName)</title>
  <x-seo::meta />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-slate-950 text-slate-100 !font-['Outfit',_sans-serif]">
  <div class="fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute -top-32 right-0 h-96 w-96 rounded-full bg-sky-500/20 blur-3xl"></div>
    <div class="absolute bottom-0 left-10 h-80 w-80 rounded-full bg-indigo-500/10 blur-3xl"></div>
  </div>

  <main class="relative mx-auto flex min-h-screen w-full max-w-3xl flex-col gap-10 px-6 py-16 md:py-20">
    <section
      class="space-y-6 rounded-3xl border border-white/10 bg-slate-900/80 p-8 shadow-2xl shadow-black/40 backdrop-blur-xl md:p-10">
      <header class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-3">
          <span
            class="inline-flex items-center gap-2 rounded-full bg-sky-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-sky-300">
            #100DaysOfCode
          </span>
          <div class="space-y-1">
            <h1 class="text-3xl font-semibold text-slate-50 sm:text-4xl">{{ $displayName }} · Jour
              {{ $log->day_number }}</h1>
            <p class="text-sm text-slate-400">
              {{ $challengeTitle }}
              @if ($entryDate)
                · {{ $entryDate }}
              @endif
              @if ($minutes !== null)
                · {{ $minutes }} min codées
              @endif
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('home') }}"
            class="inline-flex items-center gap-2 rounded-full border border-slate-600/60 bg-slate-800/60 px-4 py-2 text-xs font-semibold text-slate-200 transition hover:border-sky-500/60 hover:text-sky-300">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M9.707 3.293a1 1 0 010 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 11-1.414 1.414l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 0z"
                clip-rule="evenodd" />
            </svg>
            Découvrir {{ config('app.name') }}
          </a>
        </div>
      </header>

      @if ($log->summary_md)
        <div class="space-y-3">
          <h2 class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Résumé IA</h2>
          <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-6 text-sm leading-relaxed text-slate-200">
            {!! \Illuminate\Support\Str::markdown($log->summary_md) !!}
          </div>
        </div>
      @endif

      @if (!empty($log->tags))
        <div class="space-y-3">
          <h2 class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Mots-clés</h2>
          <div class="flex flex-wrap gap-2">
            @foreach ($log->tags as $tag)
              <span
                class="inline-flex items-center gap-1 rounded-full border border-emerald-400/40 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-200">
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                  <path
                    d="M4.25 2A2.25 2.25 0 002 4.25v2.69c0 .598.237 1.172.659 1.593l9.808 9.808a2.25 2.25 0 003.182 0l2.068-2.068a2.25 2.25 0 000-3.183l-9.808-9.807A2.25 2.25 0 006.94 2H4.25z">
                  </path>
                </svg>
                {{ $tag }}
              </span>
            @endforeach
          </div>
        </div>
      @endif

      @if ($log->coach_tip)
        <div class="space-y-3">
          <h2 class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Conseil du coach</h2>
          <div class="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-5 text-sm text-slate-200">
            {{ $log->coach_tip }}
          </div>
        </div>
      @endif

      @php($templates = $log->share_templates ?? [])
      @php($linkedinTemplate = $templates['linkedin'] ?? $log->share_draft)
      @php($xTemplate = $templates['x'] ?? null)

      @if ($linkedinTemplate || $xTemplate)
        <div class="space-y-3">
          <h2 class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Brouillon à partager</h2>
          <div class="space-y-3">
            @if ($linkedinTemplate)
              <div class="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-5 text-sm text-slate-200">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">LinkedIn</p>
                <p class="mt-2 whitespace-pre-line break-words">{{ $linkedinTemplate }}</p>
              </div>
            @endif
            @if ($xTemplate)
              <div class="rounded-2xl border border-slate-700/60 bg-slate-900/60 p-5 text-sm text-slate-200">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">X</p>
                <p class="mt-2 whitespace-pre-line break-words">{{ $xTemplate }}</p>
              </div>
            @endif
          </div>
        </div>
      @endif

      <footer
        class="flex flex-col items-center justify-between gap-3 border-t border-slate-700/60 pt-6 text-xs text-slate-500 sm:flex-row">
        <span>Partagé via {{ config('app.name') }}.</span>
        <span>Rejoins le challenge et construis ta propre streak !</span>
      </footer>
    </section>
  </main>
</body>

</html>
