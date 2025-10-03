@php($expiresAt = $invitation->expires_at?->timezone(config('app.timezone')))

<div>
  <p>Bonjour,</p>

  <p>
    @if ($ownerName)
      {{ $ownerName }}
    @else
      Un membre de l'équipe
    @endif
    vous invite à rejoindre le challenge
    <strong>{{ $run?->title ?? '100 Days of Code' }}</strong>.
  </p>

  <p>
    Pour accepter l'invitation et rejoindre l'espace de suivi, cliquez sur le lien
    ci-dessous ou copiez-le dans votre navigateur :
  </p>

  <p>
    <a href="{{ $link }}" style="color: #2563eb;">
      {{ $link }}
    </a>
  </p>

  @if ($expiresAt)
    <p>Attention : ce lien expirera le {{ $expiresAt->format('d/m/Y à H:i') }}.</p>
  @endif

  <p>À bientôt !</p>

  <p>{{ config('app.name') }}</p>
</div>

