@php
    $success = $status === 'success';
    $redirect = $redirectUrl ?? url('/');
    $displayMessage = $message ?: ($success
        ? __('Authentication complete. You can return to the application.')
        : __('Authentication failed. Please try again.'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $success ? __('GitHub authentication successful') : __('GitHub authentication failed') }}</title>
    <style>
        :root {
            color-scheme: light dark;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            margin: 0;
            padding: 2.5rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            color: #f8fafc;
        }

        .card {
            max-width: 420px;
            width: 100%;
            border-radius: 1.25rem;
            padding: 2rem;
            background: rgba(15, 23, 42, 0.88);
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.4);
            text-align: center;
        }

        .card h1 {
            margin: 0 0 0.75rem;
            font-size: 1.35rem;
            font-weight: 600;
        }

        .card p {
            margin: 0 0 1.5rem;
            font-size: 0.95rem;
            line-height: 1.5;
            color: rgba(226, 232, 240, 0.9);
        }

        .card a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.2rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(148, 163, 184, 0.4);
            color: #f8fafc;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ $success ? __('All set!') : __('We hit a snag') }}</h1>
    <p>{{ $displayMessage }}</p>
    <a href="{{ $redirect }}">{{ __('Return to the application') }}</a>
</div>

<script>
    (function () {
        const payload = {
            source: 'github-auth-popup',
            type: @json($success ? 'success' : 'error'),
            redirectUrl: @json($redirect),
            message: @json($displayMessage),
        };

        try {
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage(payload, window.location.origin);
            }
        } catch (error) {
            console.error('Unable to communicate with opener window.', error);
        }

        const shouldClose = () => {
            if (payload.type === 'success') {
                window.close();
                setTimeout(() => {
                    window.location.href = payload.redirectUrl;
                }, 1200);
            } else {
                setTimeout(() => {
                    window.close();
                }, 2000);
            }
        };

        if (document.readyState === 'complete') {
            shouldClose();
        } else {
            window.addEventListener('load', shouldClose);
        }
    })();
</script>
</body>
</html>
