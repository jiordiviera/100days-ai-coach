@php($expiresAt = $invitation->expires_at?->timezone(config('app.timezone')))

<div>
  <p>{{ __('Hello,') }}</p>

  <p>
    @if ($ownerName)
      {{ $ownerName }}
    @else
      {{ __('A member of the team') }}
    @endif
    {{ __('invites you to join the challenge :name.', ['name' => $run?->title ?? __('100 Days of Code')]) }}
  </p>

  <p>
    {{ __('To accept the invitation and join the workspace, click the link below or copy it into your browser:') }}
  </p>

  <p>
    <a href="{{ $link }}" style="color: #2563eb;">
      {{ $link }}
    </a>
  </p>

  @if ($expiresAt)
    <p>{{ __('Note: this link will expire on :date.', ['date' => $expiresAt->format('d/m/Y à H:i')]) }}</p>
  @endif

  <p>{{ __('See you soon!') }}</p>

  <p>{{ config('app.name') }}</p>
</div>
