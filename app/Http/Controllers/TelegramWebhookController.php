<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NotificationChannel;
use App\Services\Telegram\TelegramClient;
use App\Services\Telegram\TelegramException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TelegramWebhookController extends Controller
{
    private const SUPPORTED_LANGUAGES = ['en', 'fr'];
    private const DEFAULT_LANGUAGE = 'en';
    private const CACHE_LANGUAGE_TTL = 365; // days
    private const LINK_TOKEN_TTL_MINUTES = 30;
    private const SIGNUP_TOKEN_TTL_MINUTES = 30;

    public function __construct(
        private readonly TelegramClient $telegram,
    ) {}

    public function handleWebhook(Request $request): JsonResponse
    {
        $update = $request->all();
        Log::withContext(['telegram_update_id' => Arr::get($update, 'update_id')]);
        Log::debug('Telegram webhook received', ['payload' => $update]);

        try {
            if ($callback = Arr::get($update, 'callback_query')) {
                $this->handleCallbackQuery($callback);
            } elseif ($message = Arr::get($update, 'message')) {
                $this->handleMessage($message);
            }
        } catch (Throwable $exception) {
            Log::error('Telegram webhook error', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleMessage(array $message): void
    {
        $chatId = (string) Arr::get($message, 'chat.id');

        if ($chatId === '') {
            Log::warning('Telegram webhook missing chat id', ['message' => $message]);

            return;
        }

        $language = $this->resolveLanguage($chatId, $message);
        $channel = $this->findChannel($chatId);

        $text = trim((string) Arr::get($message, 'text', ''));

        if ($text !== '' && Str::startsWith($text, '/')) {
            [$command, $argument] = $this->parseCommand($text);
            $this->dispatchCommand($command, $argument, $chatId, $message, $language, $channel);

            return;
        }

        if ($text !== '') {
            $this->handlePlainMessage($chatId, $language);
        }
    }

    private function handleCallbackQuery(array $callback): void
    {
        $callbackId = (string) Arr::get($callback, 'id');
        $message = Arr::get($callback, 'message', []);
        $chatId = (string) Arr::get($message, 'chat.id');
        $data = (string) Arr::get($callback, 'data', '');

        if ($chatId === '') {
            return;
        }

        $language = $this->resolveLanguage($chatId, $message ?? []);
        $channel = $this->findChannel($chatId);

        $this->telegram->answerCallbackQuery($callbackId);

        if ($data === 'link_account') {
            $this->handleLinkRequest($chatId, $language, $message, $channel);

            return;
        }

        if ($data === 'signup') {
            $this->handleSignupRequest($chatId, $language, $message);

            return;
        }

        if ($data === 'language_picker') {
            $this->showLanguagePicker($chatId, $language);

            return;
        }

        if (Str::startsWith($data, 'language:')) {
            $code = Str::after($data, 'language:');
            $this->handleLanguageCommand($chatId, $code, $language, $channel, true);

            return;
        }

        if ($data === 'support') {
            $this->handleSupportCommand($chatId, $language);

            return;
        }

        Log::debug('Unhandled Telegram callback data', ['data' => $data]);
    }

    private function dispatchCommand(string $command, string $argument, string $chatId, array $message, string $language, ?NotificationChannel $channel): void
    {
        switch ($command) {
            case '/start':
                $this->handleStartCommand($chatId, $message, $language, $channel);
                break;
            case '/help':
                $this->handleHelpCommand($chatId, $language);
                break;
            case '/signup':
                $this->handleSignupRequest($chatId, $language, $message);
                break;
            case '/language':
            case '/lang':
                $this->handleLanguageCommand($chatId, $argument, $language, $channel, false);
                break;
            case '/support':
                $this->handleSupportCommand($chatId, $language);
                break;
            case '/stop':
            case '/unsubscribe':
                $this->handleStopCommand($chatId, $language, $channel);
                break;
            default:
                $this->handleUnknownCommand($chatId, $language);
        }
    }

    private function handleStartCommand(string $chatId, array $message, string $language, ?NotificationChannel $channel): void
    {
        $firstName = Str::of((string) Arr::get($message, 'from.first_name', ''))
            ->squish()
            ->limit(32, '')
            ->value() ?: Lang::get('telegram.generic.friend', locale: $language);

        $this->rememberLanguage($chatId, $language, $channel);

        $chatIdLine = Lang::get('telegram.start.chat_id', ['chat_id' => e($chatId)], $language);
        $settingsUrl = route('settings');

        $text = implode("\n\n", [
            Lang::get('telegram.start.greeting', ['name' => e($firstName)], $language),
            Lang::get('telegram.start.intro', [], $language),
            $chatIdLine,
            Lang::get('telegram.start.instructions', ['settings_url' => e($settingsUrl)], $language),
            Lang::get('telegram.start.help', [], $language),
        ]);

        $keyboard = [
            [
                [
                    'text' => Lang::get('telegram.buttons.link_account', [], $language),
                    'callback_data' => 'link_account',
                ],
                [
                    'text' => Lang::get('telegram.buttons.language', [], $language),
                    'callback_data' => 'language_picker',
                ],
            ],
            [
                [
                    'text' => Lang::get('telegram.buttons.open_settings', [], $language),
                    'url' => $settingsUrl,
                ],
                [
                    'text' => Lang::get('telegram.buttons.signup', [], $language),
                    'callback_data' => 'signup',
                ],
                [
                    'text' => Lang::get('telegram.buttons.support', [], $language),
                    'callback_data' => 'support',
                ],
            ],
        ];

        $this->reply($chatId, [
            'text' => $text,
            'reply_markup' => ['inline_keyboard' => $keyboard],
            'parse_mode' => 'HTML',
        ]);
    }

    private function showLanguagePicker(string $chatId, string $currentLanguage): void
    {
        $keyboard = [
            [
                [
                    'text' => Lang::get('telegram.languages.en', [], $currentLanguage),
                    'callback_data' => 'language:en',
                ],
                [
                    'text' => Lang::get('telegram.languages.fr', [], $currentLanguage),
                    'callback_data' => 'language:fr',
                ],
            ],
        ];

        $this->reply($chatId, [
            'text' => Lang::get('telegram.language.pick', [], $currentLanguage),
            'reply_markup' => ['inline_keyboard' => $keyboard],
        ]);
    }

    private function handleHelpCommand(string $chatId, string $language): void
    {
        $title = Lang::get('telegram.help.title', [], $language);
        $lines = Lang::get('telegram.help.lines', [], $language);
        $body = is_array($lines) ? implode("\n", $lines) : (string) $lines;

        $this->reply($chatId, "{$title}\n{$body}");
    }

    private function handleLanguageCommand(string $chatId, string $argument, string $currentLanguage, ?NotificationChannel $channel, bool $fromCallback): void
    {
        $languageCode = $this->normaliseLanguage($argument);

        if (! $languageCode) {
            $this->reply($chatId, Lang::get('telegram.language.unsupported', [], $currentLanguage));
            if (! $fromCallback) {
                $this->showLanguagePicker($chatId, $currentLanguage);
            }

            return;
        }

        $this->rememberLanguage($chatId, $languageCode, $channel);

        $languageName = Lang::get("telegram.languages.$languageCode", [], $languageCode);
        $message = Lang::get('telegram.language.updated', ['language' => $languageName], $languageCode)
            ."\n".Lang::get('telegram.language.settings_hint', [], $languageCode);

        $this->reply($chatId, $message);
    }

    private function handleLinkRequest(string $chatId, string $language, array $message, ?NotificationChannel $channel): void
    {
        $token = Str::uuid()->toString();
        $username = Arr::get($message, 'chat.username');

        Cache::put(
            $this->linkTokenKey($token),
            [
                'chat_id' => $chatId,
                'language' => $language,
                'username' => $username,
            ],
            now()->addMinutes(self::LINK_TOKEN_TTL_MINUTES)
        );

        $linkUrl = route('settings.telegram.link', ['token' => $token]);
        $settingsUrl = route('settings');

        $text = Lang::get('telegram.link.generated', ['url' => e($settingsUrl)], $language);
        $keyboard = [
            [
                [
                    'text' => Lang::get('telegram.buttons.link_open', [], $language),
                    'url' => $linkUrl,
                ],
            ],
        ];

        $this->reply($chatId, [
            'text' => $text,
            'reply_markup' => ['inline_keyboard' => $keyboard],
        ]);
    }

    private function handleSupportCommand(string $chatId, string $language): void
    {
        $supportUrl = route('support');
        $this->reply($chatId, Lang::get('telegram.support.message', ['url' => e($supportUrl)], $language));
    }

    private function handleStopCommand(string $chatId, string $language, ?NotificationChannel $channel): void
    {
        $this->deactivateChannel($chatId, $channel);
        $this->reply($chatId, Lang::get('telegram.stop.message', [], $language));
    }

    private function handleUnknownCommand(string $chatId, string $language): void
    {
        $this->reply($chatId, Lang::get('telegram.fallback.unknown_command', [], $language));
    }

    private function handlePlainMessage(string $chatId, string $language): void
    {
        $this->reply($chatId, Lang::get('telegram.fallback.default', [], $language));
    }

    private function reply(string $chatId, array|string $payload): void
    {
        try {
            $this->telegram->sendMessage($chatId, $payload);
        } catch (TelegramException $exception) {
            Log::warning('Unable to deliver Telegram response', [
                'chat_id' => $chatId,
                'error' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Unexpected error while sending Telegram response', [
                'chat_id' => $chatId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function handleSignupRequest(string $chatId, string $language, array $message): void
    {
        $token = Str::uuid()->toString();
        $firstName = Arr::get($message, 'from.first_name');
        $username = Arr::get($message, 'chat.username');
        $email = null;

        Cache::put(
            $this->signupTokenKey($token),
            [
                'chat_id' => $chatId,
                'language' => $language,
                'first_name' => $firstName,
                'username' => $username,
                'email' => $email,
            ],
            now()->addMinutes(self::SIGNUP_TOKEN_TTL_MINUTES)
        );

        $signupUrl = route('register', ['telegram_token' => $token]);

        $text = Lang::get('telegram.signup.instructions', ['url' => e($signupUrl)], $language);

        $keyboard = [
            [
                [
                    'text' => Lang::get('telegram.buttons.signup', [], $language),
                    'url' => $signupUrl,
                ],
            ],
        ];

        $this->reply($chatId, [
            'text' => $text,
            'reply_markup' => ['inline_keyboard' => $keyboard],
        ]);
    }

    private function resolveLanguage(string $chatId, array $message): string
    {
        $cached = Cache::get($this->languageCacheKey($chatId));
        if (is_string($cached) && $this->normaliseLanguage($cached)) {
            return $cached;
        }

        $channel = $this->findChannel($chatId);
        if ($channel && $channel->language && $this->normaliseLanguage($channel->language)) {
            $this->rememberLanguage($chatId, $channel->language, $channel);

            return $channel->language;
        }

        $languageCode = $this->normaliseLanguage(Arr::get($message, 'from.language_code')) ?? self::DEFAULT_LANGUAGE;
        $this->rememberLanguage($chatId, $languageCode, $channel);

        return $languageCode;
    }

    private function rememberLanguage(string $chatId, string $language, ?NotificationChannel $channel = null): void
    {
        $language = $this->normaliseLanguage($language) ?? self::DEFAULT_LANGUAGE;
        Cache::put(
            $this->languageCacheKey($chatId),
            $language,
            now()->addDays(self::CACHE_LANGUAGE_TTL)
        );

        $channel ??= $this->findChannel($chatId);

        if ($channel && $channel->language !== $language) {
            $channel->forceFill(['language' => $language])->save();
        }
    }

    private function deactivateChannel(string $chatId, ?NotificationChannel $channel = null): void
    {
        $channel ??= $this->findChannel($chatId);

        if ($channel && $channel->is_active) {
            $channel->forceFill(['is_active' => false])->save();
        }
    }

    private function parseCommand(string $text): array
    {
        $parts = preg_split('/\s+/', trim($text), 2);
        $command = Str::lower($parts[0] ?? '');
        $command = Str::before($command, '@');
        $argument = isset($parts[1]) ? trim($parts[1]) : '';

        return [$command, $argument];
    }

    private function normaliseLanguage(?string $language): ?string
    {
        if (! $language) {
            return null;
        }

        $code = Str::of($language)->lower()->substr(0, 2)->value();

        return in_array($code, self::SUPPORTED_LANGUAGES, true) ? $code : null;
    }

    private function languageCacheKey(string $chatId): string
    {
        return "telegram:lang:{$chatId}";
    }

    private function linkTokenKey(string $token): string
    {
        return "telegram:link-token:{$token}";
    }

    private function signupTokenKey(string $token): string
    {
        return "telegram:signup-token:{$token}";
    }

    private function findChannel(string $chatId): ?NotificationChannel
    {
        return NotificationChannel::query()
            ->where('channel', 'telegram')
            ->where('value', $chatId)
            ->first();
    }
}
