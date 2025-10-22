<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramClient;
use App\Services\Telegram\TelegramException;
use App\Models\NotificationChannel;
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
    private const CACHE_TTL_YEARS = 1;

    protected TelegramClient $telegram;

    public function __construct(TelegramClient $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handleWebhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $update = $request->all();
        Log::withContext(['telegram_update_id' => Arr::get($update, 'update_id')]);
        Log::debug('Telegram webhook received', ['payload' => $update]);

        $message = Arr::get($update, 'message');

        if (! $message) {
            return response()->json(['status' => 'ignored']);
        }

        $chatId = (string) Arr::get($message, 'chat.id');

        if ($chatId === '') {
            Log::warning('Telegram webhook missing chat id', ['message' => $message]);

            return response()->json(['status' => 'ignored']);
        }

        $language = $this->resolveLanguage($chatId, $message);
        $channel = $this->findChannel($chatId);

        $rawText = (string) Arr::get($message, 'text', '');
        $text = trim($rawText);

        if ($text !== '' && Str::startsWith($text, '/')) {
            [$command, $argument] = $this->parseCommand($text);
            $this->dispatchCommand($command, $argument, $chatId, $message, $language, $channel);
        } elseif ($text !== '') {
            $this->handlePlainMessage($chatId, $language);
        } else {
            Log::debug('Telegram message ignored (no textual content)', ['chat_id' => $chatId]);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function dispatchCommand(string $command, string $argument, string $chatId, array $message, string $language, ?NotificationChannel $channel): void
    {
        switch ($command) {
            case '/start':
                $this->handleStartCommand($chatId, $message, $language, $channel);

                return;
            case '/help':
                $this->handleHelpCommand($chatId, $language);

                return;
            case '/language':
            case '/lang':
                $this->handleLanguageCommand($chatId, $argument, $language, $channel);

                return;
            case '/support':
                $this->handleSupportCommand($chatId, $language);

                return;
            case '/stop':
            case '/unsubscribe':
                $this->handleStopCommand($chatId, $language, $channel);

                return;
        }

        $this->handleUnknownCommand($chatId, $language);
    }

    protected function handleStartCommand(string $chatId, array $message, string $language, ?NotificationChannel $channel): void
    {
        $firstName = Str::of((string) Arr::get($message, 'from.first_name', ''))
            ->squish()
            ->limit(32, '')
            ->value() ?: Lang::get('telegram.generic.friend', locale: $language);

        $this->rememberLanguage($chatId, $language, $channel);

        $greeting = Lang::get('telegram.start.greeting', ['name' => e($firstName)], $language);
        $intro = Lang::get('telegram.start.intro', [], $language);
        $chatIdLine = Lang::get('telegram.start.chat_id', ['chat_id' => e($chatId)], $language);
        $settingsUrl = route('settings');
        $instructions = Lang::get('telegram.start.instructions', ['settings_url' => e($settingsUrl)], $language);
        $help = Lang::get('telegram.start.help', [], $language);

        $this->reply($chatId, implode("\n\n", [$greeting, $intro, $chatIdLine, $instructions, $help]));
    }

    protected function handleHelpCommand(string $chatId, string $language): void
    {
        $title = Lang::get('telegram.help.title', [], $language);
        $lines = Lang::get('telegram.help.lines', [], $language);
        $body = is_array($lines) ? implode("\n", $lines) : (string) $lines;
        $this->reply($chatId, $title."\n".$body);
    }

    protected function handleLanguageCommand(string $chatId, string $argument, string $currentLanguage, ?NotificationChannel $channel): void
    {
        $languageCode = $this->normaliseLanguage($argument);

        if (! $languageCode) {
            $this->reply($chatId, Lang::get('telegram.language.unsupported', [], $currentLanguage));

            return;
        }

        $this->rememberLanguage($chatId, $languageCode, $channel);

        $languageName = Lang::get("telegram.languages.$languageCode", [], $languageCode);
        $message = Lang::get('telegram.language.updated', ['language' => $languageName], $languageCode);
        $message .= "\n".Lang::get('telegram.language.settings_hint', [], $languageCode);

        $this->reply($chatId, $message);
    }

    protected function handleSupportCommand(string $chatId, string $language): void
    {
        $supportUrl = route('support');
        $this->reply($chatId, Lang::get('telegram.support.message', ['url' => e($supportUrl)], $language));
    }

    protected function handleStopCommand(string $chatId, string $language, ?NotificationChannel $channel): void
    {
        $this->deactivateChannel($chatId, $channel);
        $this->reply($chatId, Lang::get('telegram.stop.message', [], $language));
    }

    protected function handleUnknownCommand(string $chatId, string $language): void
    {
        $this->reply($chatId, Lang::get('telegram.fallback.unknown_command', [], $language));
    }

    protected function handlePlainMessage(string $chatId, string $language): void
    {
        $this->reply($chatId, Lang::get('telegram.fallback.default', [], $language));
    }

    protected function reply(string $chatId, string $message): void
    {
        try {
            $this->telegram->sendMessage($chatId, $message);
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

    protected function resolveLanguage(string $chatId, array $message): string
    {
        $cached = Cache::get($this->cacheKey($chatId));
        if (is_string($cached) && $this->normaliseLanguage($cached)) {
            return $cached;
        }

        $channel = $this->findChannel($chatId);
        if ($channel && $channel->language && $this->normaliseLanguage($channel->language)) {
            $this->rememberLanguage($chatId, $channel->language, $channel);

            return $channel->language;
        }

        $languageCode = $this->normaliseLanguage(Arr::get($message, 'from.language_code'));
        if (! $languageCode) {
            $languageCode = self::DEFAULT_LANGUAGE;
        }

        $this->rememberLanguage($chatId, $languageCode, $channel);

        return $languageCode;
    }

    protected function rememberLanguage(string $chatId, string $language, ?NotificationChannel $channel = null): void
    {
        $language = $this->normaliseLanguage($language) ?? self::DEFAULT_LANGUAGE;
        Cache::put($this->cacheKey($chatId), $language, now()->addYears(self::CACHE_TTL_YEARS));

        $channel ??= $this->findChannel($chatId);

        if ($channel && $channel->language !== $language) {
            $channel->forceFill(['language' => $language])->save();
        }
    }

    protected function deactivateChannel(string $chatId, ?NotificationChannel $channel = null): void
    {
        $channel ??= $this->findChannel($chatId);

        if ($channel && $channel->is_active) {
            $channel->forceFill(['is_active' => false])->save();
        }
    }

    protected function parseCommand(string $text): array
    {
        $parts = preg_split('/\s+/', trim($text), 2);
        $command = Str::lower($parts[0] ?? '');
        $command = Str::before($command, '@');
        $argument = isset($parts[1]) ? trim($parts[1]) : '';

        return [$command, $argument];
    }

    protected function normaliseLanguage(?string $language): ?string
    {
        if (! $language) {
            return null;
        }

        $code = Str::lower(Str::substr($language, 0, 2));

        return in_array($code, self::SUPPORTED_LANGUAGES, true) ? $code : null;
    }

    protected function cacheKey(string $chatId): string
    {
        return "telegram:lang:{$chatId}";
    }

    protected function findChannel(string $chatId): ?NotificationChannel
    {
        return NotificationChannel::query()
            ->where('channel', 'telegram')
            ->where('value', $chatId)
            ->first();
    }
}
