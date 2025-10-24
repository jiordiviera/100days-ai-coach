<?php

namespace App\Livewire\Page;

use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Paramètres')]
#[Layout('components.layouts.app')]
class Settings extends Component implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    private const TELEGRAM_LANGUAGES = ['auto', 'en', 'fr'];

    #[Url(as: 'tab')]
    public string $activeTab = 'profile';

    public ?array $data = [];

    public array $timezones = [];

    public bool $hasWakatimeKey = false;

    public function mount(): void
    {
        $this->timezones = $this->timezoneOptions();

        /** @var User $user */
        $user = Auth::user();
        $profile = $user->profile;

        if (! $profile) {
            $profile = $user->profile()->create([
                'join_reason' => 'self_onboarding',
                'focus_area' => null,
                'preferences' => $user->profilePreferencesDefaults(),
            ]);
        }

        $preferences = $profile->preferences ?? $user->profilePreferencesDefaults();
        $settings = $profile->wakatime_settings ?? [];
        $defaultHashtags = data_get($user->profilePreferencesDefaults(), 'social.share_hashtags', ['#100DaysOfCode', '#buildinpublic']);

        $telegramChannel = $user->notificationChannels()->firstWhere('channel', 'telegram');
        $telegramLanguage = $telegramChannel?->language ?: 'auto';

        if (! in_array($telegramLanguage, self::TELEGRAM_LANGUAGES, true)) {
            $telegramLanguage = 'auto';
        }

        $this->hasWakatimeKey = (bool) $profile->wakatime_api_key;

        // Transform social_links from key-value to repeater format
        $socialLinksRepeater = [];
        if ($profile->social_links) {
            foreach ($profile->social_links as $platform => $url) {
                $socialLinksRepeater[] = [
                    'platform' => $platform,
                    'url' => $url,
                ];
            }
        }

        $this->form->fill([
            'profile' => [
                'name' => $user->name,
                'username' => $profile->username,
                'is_public' => (bool) $profile->is_public,
                'focus_area' => $profile->focus_area,
                'bio' => $profile->bio,
                'avatar_url' => $profile->avatar_url,
                'social_links' => $socialLinksRepeater,
            ],
            'notifications' => [
                'language' => $preferences['language'] ?? 'en',
                'timezone' => $preferences['timezone'] ?? 'Africa/Douala',
                'reminder_time' => $this->normalizeReminderTime($preferences['reminder_time'] ?? null),
                'channels' => collect($preferences['channels'] ?? [])->filter()->keys()->all(),
                'notification_types' => collect($preferences['notification_types'] ?? [])->filter()->keys()->all(),
                'telegram' => [
                    'chat_id' => $telegramChannel?->value ?? '',
                    'username' => data_get($telegramChannel?->metadata, 'username', ''),
                    'language' => $telegramLanguage,
                ],
            ],
            'ai' => [
                'provider' => $preferences['ai_provider'] ?? 'groq',
                'tone' => $preferences['tone'] ?? 'neutral',
                'share_hashtags' => array_values(data_get($preferences, 'social.share_hashtags', $defaultHashtags)),
            ],
            'integrations' => [
                'wakatime_api_key' => '',
                'wakatime_remove_key' => false,
                'wakatime_hide_project_names' => (bool) data_get($settings, 'hide_project_names', data_get($preferences, 'wakatime.hide_project_names', true)),
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $profile = Auth::user()->profile;

        return $schema
            ->statePath('data')
            ->schema([
                // Profile Tab Content
                TextInput::make('profile.name')
                    ->label(__('settings.profile.name_label'))
                    ->maxLength(255)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('profile.username')
                    ->label(__('settings.profile.username_label'))
                    ->maxLength(32)
                    ->helperText(__('settings.profile.username_helper'))
                    ->rules([
                        'nullable',
                        'alpha_dash',
                        Rule::unique('user_profiles', 'username')->ignore($profile?->id),
                    ])
                    ->columnSpan(1),

                TextInput::make('profile.focus_area')
                    ->label(__('settings.profile.focus_label'))
                    ->placeholder(__('settings.profile.focus_placeholder'))
                    ->maxLength(120)
                    ->columnSpan(1),

                Textarea::make('profile.bio')
                    ->label(__('settings.profile.bio_label'))
                    ->rows(3)
                    ->helperText(__('settings.profile.bio_helper'))
                    ->maxLength(160)
                    ->columnSpan(1),

                TextInput::make('profile.avatar_url')
                    ->label(__('settings.profile.avatar_label'))
                    ->url()
                    ->maxLength(255)
                    ->columnSpan(1),

                Toggle::make('profile.is_public')
                    ->label(__('settings.profile.public_toggle_label'))
                    ->helperText(__('settings.profile.public_toggle_helper'))
                    ->inline(false)
                    ->columnSpan(1),

                // Social Links Tab Content
                // KeyValue::make('profile.social_links')
                //     ->label(__('settings.social.label'))
                //     ->keyLabel(__('settings.social.key_label'))
                //     ->valueLabel(__('settings.social.value_label'))
                //     ->keyPlaceholder(__('settings.social.key_placeholder'))
                //     ->valuePlaceholder(__('settings.social.value_placeholder'))
                //     ->addActionLabel(__('settings.social.add_button'))
                //     ->reorderable()
                //     ->columnSpan(2),
                Repeater::make('profile.social_links')
                    ->schema([
                        Select::make('platform')
                            ->label(__('settings.social.platform_label'))
                            ->options([
                                'github' => 'GitHub',
                                'linkedin' => 'LinkedIn',
                                'twitter' => 'X (Twitter)',
                                'youtube' => 'YouTube',
                                'facebook' => 'Facebook',
                                'instagram' => 'Instagram',
                                'tiktok' => 'TikTok',
                                'twitch' => 'Twitch',
                                'discord' => 'Discord',
                                'website' => __('settings.social.platform_website'),
                                'portfolio' => __('settings.social.platform_portfolio'),
                                'blog' => __('settings.social.platform_blog'),
                                'other' => __('settings.social.platform_other'),
                            ])
                            ->native(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($get('url')) {
                                    $set('url', '');
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('url')
                            ->label(__('settings.social.url_label'))
                            ->required()
                            ->placeholder(function (callable $get) {
                                return match ($get('platform')) {
                                    'github' => '@username',
                                    'linkedin' => 'username',
                                    'twitter' => '@username',
                                    'youtube' => '@username',
                                    'facebook' => 'username',
                                    'instagram' => 'username',
                                    'tiktok' => 'username',
                                    'twitch' => 'username',
                                    'discord' => 'invite-code',
                                    'website' => 'https://example.com',
                                    'portfolio' => 'https://portfolio.example.com',
                                    'blog' => 'https://blog.example.com',
                                    default => 'https://',
                                };
                            })
                            ->helperText(function (callable $get) {
                                return match ($get('platform')) {
                                    'github' => __('settings.social.helper.github'),
                                    'linkedin' => __('settings.social.helper.linkedin'),
                                    'twitter' => __('settings.social.helper.twitter'),
                                    'discord' => __('settings.social.helper.discord'),
                                    default => null,
                                };
                            })
                            ->prefixIcon(function (callable $get) {
                                return match ($get('platform')) {
                                    'github' => 'heroicon-o-code-bracket',
                                    'linkedin' => 'heroicon-o-briefcase',
                                    'twitter' => 'heroicon-o-chat-bubble-left',
                                    'youtube' => 'heroicon-o-video-camera',
                                    'facebook' => 'heroicon-o-user-group',
                                    'instagram' => 'heroicon-o-camera',
                                    'tiktok' => 'heroicon-o-musical-note',
                                    'twitch' => 'heroicon-o-play',
                                    'discord' => 'heroicon-o-chat-bubble-bottom-center-text',
                                    'website' => 'heroicon-o-globe-alt',
                                    'portfolio' => 'heroicon-o-briefcase',
                                    'blog' => 'heroicon-o-document-text',
                                    default => 'heroicon-o-link',
                                };
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['platform']
                            ? ucfirst($state['platform']).($state['url'] ? ' - '.parse_url($state['url'], PHP_URL_HOST) : '')
                            : null
                    )
                    ->addActionLabel(__('settings.social.add_link'))
                    ->defaultItems(0)
                    ->columnSpan(2),

                // Notifications Tab Content
                Select::make('notifications.language')
                    ->label(__('settings.notifications.language_label'))
                    ->native(false)
                    ->options([
                        'en' => __('settings.notifications.language_options.en'),
                        'fr' => __('settings.notifications.language_options.fr'),
                    ])
                    ->required()
                    ->columnSpan(1),

                Select::make('notifications.timezone')
                    ->label(__('settings.notifications.timezone_label'))
                    ->options($this->timezones)
                    ->native(false)
                    ->preload(true)
                    ->searchable()
                    ->required()
                    ->columnSpan(1),

                TimePicker::make('notifications.reminder_time')
                    ->label(__('settings.notifications.reminder_time_label'))
                    ->native(false)
                    ->required()
                    ->columnSpan(1),

                CheckboxList::make('notifications.channels')
                    ->label(__('settings.notifications.channels_label'))
                    ->options([
                        'email' => __('settings.notifications.channel_options.email'),
                        'telegram' => __('settings.notifications.channel_options.telegram'),
                        'slack' => __('settings.notifications.channel_options.slack'),
                        'push' => __('settings.notifications.channel_options.push'),
                    ])
                    ->columns(3)
                    ->columnSpan(2),

                Fieldset::make('notifications.telegram_config')
                    ->label(__('settings.notifications.telegram.section'))
                    ->schema([
                        TextEntry::make('notifications.telegram_description')
                            ->state(__('settings.notifications.telegram.description'))
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'text-sm text-muted-foreground']),
                        TextInput::make('notifications.telegram.chat_id')
                            ->label(__('settings.notifications.telegram.chat_id_label'))
                            ->helperText(__('settings.notifications.telegram.chat_id_helper'))
                            ->maxLength(64)
                            ->rules(['nullable', 'regex:/^\\d+$/'])
                            ->columnSpan(1),
                        TextInput::make('notifications.telegram.username')
                            ->label(__('settings.notifications.telegram.username_label'))
                            ->helperText(__('settings.notifications.telegram.username_helper'))
                            ->maxLength(32)
                            ->columnSpan(1),
                        Select::make('notifications.telegram.language')
                            ->label(__('settings.notifications.telegram.language_label'))
                            ->options([
                                'auto' => __('settings.notifications.telegram.language_options.auto'),
                                'en' => __('settings.notifications.telegram.language_options.en'),
                                'fr' => __('settings.notifications.telegram.language_options.fr'),
                            ])
                            ->default('auto')
                            ->native(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->visible(fn (Get $get): bool => in_array('telegram', $get('notifications.channels') ?? [], true)),

                CheckboxList::make('notifications.notification_types')
                    ->label(__('settings.notifications.types_label'))
                    ->options([
                        'daily_reminder' => __('settings.notifications.type_options.daily_reminder'),
                        'weekly_digest' => __('settings.notifications.type_options.weekly_digest'),
                    ])
                    ->columns(2)
                    ->columnSpan(2),

                // AI Tab Content
                Select::make('ai.provider')
                    ->label(__('settings.ai.provider_label'))
                    ->native(false)
                    ->options([
                        'groq' => __('settings.ai.provider_options.groq'),
                        'openai' => __('settings.ai.provider_options.openai'),
                        'local' => __('settings.ai.provider_options.local'),
                    ])
                    ->required()
                    ->columnSpan(1),

                Select::make('ai.tone')
                    ->label(__('settings.ai.tone_label'))
                    ->native(false)
                    ->options([
                        'neutral' => __('settings.ai.tone_options.neutral'),
                        'fun' => __('settings.ai.tone_options.fun'),
                    ])
                    ->required()
                    ->columnSpan(1),

                TagsInput::make('ai.share_hashtags')
                    ->label(__('settings.ai.hashtags_label'))
                    ->placeholder(__('settings.ai.hashtags_placeholder'))
                    ->helperText(__('settings.ai.hashtags_helper'))
                    ->separator(',')
                    ->columnSpan(2),

                // Integrations Tab Content
                TextInput::make('integrations.wakatime_api_key')
                    ->label(__('settings.integrations.wakatime_key_label'))
                    ->password()
                    ->helperText(fn () => new HtmlString(__('settings.integrations.wakatime_key_helper')))
                    ->columnSpan(2),

                Toggle::make('integrations.wakatime_hide_project_names')
                    ->label(__('settings.integrations.wakatime_hide_label'))
                    ->helperText(__('settings.integrations.wakatime_hide_helper'))
                    ->inline(false)
                    ->columnSpan(1),

                Toggle::make('integrations.wakatime_remove_key')
                    ->label(__('settings.integrations.wakatime_remove_label'))
                    ->helperText(__('settings.integrations.wakatime_remove_helper'))
                    ->inline(false)
                    ->columnSpan(1),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /** @var User $user */
        $user = Auth::user();
        $profile = $user->profile;

        $user->forceFill([
            'name' => Str::squish($data['profile']['name'] ?? $user->name),
        ])->save();

        $username = $data['profile']['username'] ?? null;
        $profilePublic = (bool) ($data['profile']['is_public'] ?? false);
        $focusArea = $data['profile']['focus_area'] ?? null;
        $bio = $data['profile']['bio'] ?? null;
        $avatarUrl = $data['profile']['avatar_url'] ?? null;
        $previousUsername = $profile->username;

        if ($profilePublic && blank($username)) {
            $this->addError('data.profile.username', __('settings.profile.username_required_body'));

            Notification::make()
                ->title(__('settings.profile.username_required_title'))
                ->body(__('settings.profile.username_required_body'))
                ->warning()
                ->send();

            return;
        }

        $socialLinksArray = [];
        foreach ($data['profile']['social_links'] ?? [] as $link) {
            if (filled($link['platform'] ?? null) && filled($link['url'] ?? null)) {
                $key = Str::of($link['platform'])->lower()->slug('_')->value();
                $socialLinksArray[$key] = trim($link['url']);
            }
        }

        $integrations = $data['integrations'] ?? [];
        $wakatimeKeyInput = trim($integrations['wakatime_api_key'] ?? '');
        $removeWakatimeKey = (bool) ($integrations['wakatime_remove_key'] ?? false);
        $hideProjectNames = (bool) ($integrations['wakatime_hide_project_names'] ?? false);

        $wakatimeSettings = $profile->wakatime_settings ?? [];
        $wakatimeSettings['hide_project_names'] = $hideProjectNames;

        if ($removeWakatimeKey) {
            unset($wakatimeSettings['last_error']);
        }

        $profileUpdates = [
            'username' => $username ? Str::of($username)->lower()->slug()->value() : null,
            'is_public' => $profilePublic,
            'focus_area' => $focusArea ? Str::limit($focusArea, 120) : null,
            'bio' => $bio ? Str::limit($bio, 160) : null,
            'avatar_url' => $avatarUrl ?: null,
            'social_links' => $socialLinksArray ?: null,
            'wakatime_settings' => $wakatimeSettings ?: null,
        ];

        if ($removeWakatimeKey) {
            $profileUpdates['wakatime_api_key'] = null;
        } elseif ($wakatimeKeyInput !== '') {
            $profileUpdates['wakatime_api_key'] = $wakatimeKeyInput;
        }

        $profile->forceFill($profileUpdates)->save();

        $cacheKeys = [];
        if ($previousUsername) {
            $cacheKeys[] = "public-profile:{$previousUsername}";
        }

        if ($profile->username) {
            $cacheKeys[] = "public-profile:{$profile->username}";
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        $this->hasWakatimeKey = (bool) ($profileUpdates['wakatime_api_key'] ?? $profile->wakatime_api_key);

        $preferences = $profile->preferences ?? [];

        $channels = array_fill_keys($data['notifications']['channels'] ?? [], true);
        $notificationTypes = array_fill_keys($data['notifications']['notification_types'] ?? [], true);

        $reminderTime = $this->normalizeReminderTime($data['notifications']['reminder_time'] ?? null);

        $telegramInput = $data['notifications']['telegram'] ?? [];
        $telegramChatId = trim((string) ($telegramInput['chat_id'] ?? ''));
        $telegramUsername = trim((string) ($telegramInput['username'] ?? ''));
        $telegramLanguageInput = $telegramInput['language'] ?? 'auto';

        if (! in_array($telegramLanguageInput, self::TELEGRAM_LANGUAGES, true)) {
            $telegramLanguageInput = 'auto';
        }

        $telegramLanguage = $telegramLanguageInput === 'auto'
            ? ($data['notifications']['language'] ?? 'en')
            : $telegramLanguageInput;

        if (! in_array($telegramLanguage, ['en', 'fr'], true)) {
            $telegramLanguage = 'en';
        }

        $telegramChannelRecord = $user->notificationChannels()->firstWhere('channel', 'telegram');

        if (($channels['telegram'] ?? false)) {
            if ($telegramChatId === '') {
                $this->addError('data.notifications.telegram.chat_id', __('settings.notifications.telegram.errors.missing_chat_id'));

                Notification::make()
                    ->title(__('settings.notifications.telegram.errors.title'))
                    ->body(__('settings.notifications.telegram.errors.missing_chat_id'))
                    ->danger()
                    ->send();

                return;
            }

            $sanitizedUsername = Str::of($telegramUsername)->trim()->ltrim('@')->value();
            $metadata = array_filter([
                'username' => $sanitizedUsername !== '' ? $sanitizedUsername : null,
            ]);

            $channelPayload = [
                'value' => $telegramChatId,
                'language' => $telegramLanguage,
                'is_active' => true,
                'metadata' => $metadata ?: null,
            ];

            if (! $telegramChannelRecord) {
                $user->notificationChannels()->create(array_merge($channelPayload, [
                    'channel' => 'telegram',
                ]));
            } else {
                $telegramChannelRecord->forceFill($channelPayload)->save();
            }
        } elseif ($telegramChannelRecord && $telegramChannelRecord->is_active) {
            $telegramChannelRecord->forceFill([
                'is_active' => false,
            ])->save();
        }

        $defaultHashtags = data_get($user->profilePreferencesDefaults(), 'social.share_hashtags', ['#100DaysOfCode', '#buildinpublic']);
        $currentHashtags = data_get($preferences, 'social.share_hashtags', $defaultHashtags);
        $rawHashtags = $data['ai']['share_hashtags'] ?? $currentHashtags;
        $shareHashtags = $this->sanitizeHashtags($rawHashtags);

        if (empty($shareHashtags)) {
            $shareHashtags = $defaultHashtags;
        }

        $updatedPreferences = array_replace_recursive($user->profilePreferencesDefaults(), $preferences, [
            'language' => $data['notifications']['language'] ?? 'en',
            'timezone' => $data['notifications']['timezone'] ?? 'Africa/Douala',
            'reminder_time' => $reminderTime,
            'channels' => array_merge([
                'email' => false,
                'telegram' => false,
                'slack' => false,
                'push' => false,
            ], $channels),
            'notification_types' => array_merge([
                'daily_reminder' => false,
                'weekly_digest' => false,
            ], $notificationTypes),
            'ai_provider' => $data['ai']['provider'] ?? 'groq',
            'tone' => $data['ai']['tone'] ?? 'neutral',
            'wakatime' => [
                'hide_project_names' => $hideProjectNames,
            ],
            'social' => [
                'share_hashtags' => $shareHashtags,
            ],
        ]);

        $profile->forceFill([
            'preferences' => $updatedPreferences,
        ])->save();

        Notification::make()
            ->title(__('settings.messages.updated'))
            ->success()
            ->send();
    }

    protected function normalizeReminderTime(?string $value): string
    {
        if (blank($value)) {
            return '20:30';
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return '20:30';
        }
    }

    /**
     * @param  array<string>|string|null  $value
     * @return array<int, string>
     */
    protected function sanitizeHashtags($value): array
    {
        $items = match (true) {
            is_array($value) => $value,
            is_string($value) => preg_split('/[\s,]+/', $value) ?: [],
            default => [],
        };

        return collect($items)
            ->map(fn ($tag) => is_string($tag) ? trim($tag) : '')
            ->filter()
            ->map(function (string $tag) {
                $body = preg_replace('/[^A-Za-z0-9_]/', '', ltrim($tag, "# \t\n\r\0\x0B"));

                if (! $body) {
                    return null;
                }

                return '#'.$body;
            })
            ->filter()
            ->unique()
            ->take(6)
            ->values()
            ->all();
    }

    protected function timezoneOptions(): array
    {
        $preferred = [
            'UTC' => 'UTC',
            'Africa/Douala' => 'Africa/Douala',
            'Europe/Paris' => 'Europe/Paris',
            'America/New_York' => 'America/New_York',
            'America/Los_Angeles' => 'America/Los_Angeles',
            'Asia/Tokyo' => 'Asia/Tokyo',
            'Asia/Singapore' => 'Asia/Singapore',
            'Australia/Sydney' => 'Australia/Sydney',
        ];

        $common = collect(\DateTimeZone::listIdentifiers())
            ->filter(fn ($tz) => Str::contains($tz, ['/']))
            ->mapWithKeys(fn ($tz) => [$tz => $tz])
            ->all();

        return $preferred + $common;
    }

    public function getTabFields(string $tab): array
    {
        $allFields = [
            'profile.name',
            'profile.username',
            'profile.focus_area',
            'profile.bio',
            'profile.avatar_url',
            'profile.is_public',
            'profile.social_links',
            'notifications.language',
            'notifications.timezone',
            'notifications.reminder_time',
            'notifications.channels',
            'notifications.telegram_config',
            'notifications.notification_types',
            'ai.provider',
            'ai.tone',
            'ai.share_hashtags',
            'integrations.wakatime_api_key',
            'integrations.wakatime_hide_project_names',
            'integrations.wakatime_remove_key',
        ];

        $fieldsByTab = [
            'profile' => [
                'profile.name',
                'profile.username',
                'profile.focus_area',
                'profile.bio',
                'profile.avatar_url',
                'profile.is_public',
            ],
            'social' => [
                'profile.social_links',
            ],
            'notifications' => [
                'notifications.language',
                'notifications.timezone',
                'notifications.reminder_time',
                'notifications.channels',
                'notifications.telegram_config',
                'notifications.notification_types',
            ],
            'ai' => [
                'ai.provider',
                'ai.tone',
                'ai.share_hashtags',
            ],
            'integrations' => [
                'integrations.wakatime_api_key',
                'integrations.wakatime_hide_project_names',
                'integrations.wakatime_remove_key',
            ],
        ];

        return $fieldsByTab[$tab] ?? [];
    }

    public function render(): View
    {
        return view('livewire.page.settings', [
            'profile' => Auth::user()->profile,
            'hasWakatimeKey' => $this->hasWakatimeKey,
        ]);
    }
}
