<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\NotificationChannel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramLinkController extends Controller
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $cacheKey = $this->cacheKey($token);
        $payload = Cache::get($cacheKey);

        if (! $payload) {
            return $this->redirectWithMessage(
                'settings.telegram.link_expired',
                'error'
            );
        }

        if (! $request->user()) {
            return redirect()
                ->route('login')
                ->with('status', Lang::get('telegram.link.login_required'));
        }

        $user = $request->user();
        $chatId = (string) Arr::get($payload, 'chat_id');
        $language = (string) Arr::get($payload, 'language', 'en');
        $username = Arr::get($payload, 'username');

        if ($chatId === '') {
            Log::warning('Telegram link token without chat id', ['token' => $token]);

            return $this->redirectWithMessage('settings.telegram.link_failed', 'error');
        }

        NotificationChannel::query()
            ->where('channel', 'telegram')
            ->where('value', $chatId)
            ->where('notifiable_type', '!=', get_class($user))
            ->delete();

        $user->notificationChannels()->updateOrCreate(
            [
                'channel' => 'telegram',
                'value' => $chatId,
            ],
            [
                'language' => $language,
                'is_active' => true,
                'metadata' => array_filter([
                    'username' => $username ? Str::start($username, '@') : null,
                ]),
            ]
        );

        Cache::forget($cacheKey);

        return $this->redirectWithMessage('settings.telegram.link_success', 'status');
    }

    protected function redirectWithMessage(string $translationKey, string $flashKey): RedirectResponse
    {
        return redirect()
            ->route('settings')
            ->with($flashKey, Lang::get($translationKey));
    }

    protected function cacheKey(string $token): string
    {
        return "telegram:link-token:{$token}";
    }
}
