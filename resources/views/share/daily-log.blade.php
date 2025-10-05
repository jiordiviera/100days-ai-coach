<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Journal #100DaysOfCode — {{ $user->profile?->username ?? $user->name }}</title>
    <meta name="description" content="Entrée partagée du défi #100DaysOfCode.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
      :root {
        color-scheme: dark;
      }
      body {
        margin: 0;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: radial-gradient(circle at top, rgba(59,130,246,.12), transparent 55%), #020617;
        color: #e2e8f0;
        min-height: 100vh;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 64px 20px;
      }
      .card {
        width: min(720px, 100%);
        background: rgba(15,23,42,.75);
        border: 1px solid rgba(148,163,184,.12);
        border-radius: 28px;
        padding: 40px;
        box-shadow: 0 40px 80px rgba(15,23,42,.6);
        backdrop-filter: blur(12px);
      }
      .header {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 32px;
      }
      .badge {
        display: inline-flex;
        padding: 4px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(59,130,246,.15);
        color: #60a5fa;
      }
      h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
        color: #f8fafc;
      }
      .meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 13px;
        color: #94a3b8;
        margin-top: 4px;
      }
      .section {
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid rgba(148,163,184,.12);
      }
      .section h2 {
        margin: 0 0 12px;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: .18em;
        color: #94a3b8;
      }
      .summary {
        background: rgba(148,163,184,.08);
        border-radius: 18px;
        padding: 20px;
      }
      .summary p {
        margin: 0 0 12px;
        line-height: 1.6;
        color: #cbd5f5;
      }
      .summary ul {
        margin: 0 0 12px 20px;
        color: #cbd5f5;
      }
      .summary ul li {
        margin-bottom: 6px;
      }
      .tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }
      .tag {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(16,185,129,.15);
        color: #6ee7b7;
      }
      .coach, .share {
        background: rgba(148,163,184,.08);
        border-radius: 18px;
        padding: 18px;
        font-size: 14px;
        line-height: 1.6;
        color: #cbd5f5;
      }
      .footer {
        margin-top: 36px;
        font-size: 13px;
        color: #64748b;
        text-align: center;
      }
      a.button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 13px;
        color: #60a5fa;
        text-decoration: none;
      }
      @media (max-width: 640px) {
        body {
          padding: 32px 16px;
        }
        .card {
          padding: 28px;
        }
        h1 {
          font-size: 24px;
        }
      }
    </style>
  </head>
  <body>
    <article class="card">
      <div class="header">
        <div>
          <h1>{{ $user->profile?->username ?? $user->name }} — Jour {{ $log->day_number }}</h1>
          <div class="meta">
            @if ($meta['date'])
              <span>{{ \Illuminate\Support\Carbon::parse($meta['date'])->translatedFormat('d F Y') }}</span>
            @endif
            @if ($meta['hours'])
              <span>{{ number_format((float) $meta['hours'], 2) }} h codées</span>
            @endif
            @if ($challenge)
              <span>Challenge : {{ $challenge->title ?? '100DaysOfCode' }}</span>
            @endif
          </div>
        </div>
        <span class="badge">#100DaysOfCode</span>
      </div>

      @if ($log->summary_md)
        <section class="section">
          <h2>Résumé</h2>
          <div class="summary">
            {!! \Illuminate\Support\Str::markdown($log->summary_md) !!}
          </div>
        </section>
      @endif

      @if (! empty($log->tags))
        <section class="section">
          <h2>Tags</h2>
          <div class="tags">
            @foreach ($log->tags as $tag)
              <span class="tag">{{ $tag }}</span>
            @endforeach
          </div>
        </section>
      @endif

      @if ($log->coach_tip)
        <section class="section">
          <h2>Conseil du coach</h2>
          <div class="coach">
            {{ $log->coach_tip }}
          </div>
        </section>
      @endif

      @if ($log->share_draft)
        <section class="section">
          <h2>Brouillon pour partage</h2>
          <div class="share">
            {!! nl2br(e($log->share_draft)) !!}
          </div>
        </section>
      @endif

      <div class="footer">
        Partagé via <strong>100DaysOfCode AI Coach</strong> —
        <a class="button" href="{{ route('home') }}">Découvrir l’app</a>
      </div>
    </article>
  </body>
</html>
