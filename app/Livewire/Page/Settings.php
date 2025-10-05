<?php

namespace App\Livewire\Page;

use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Paramètres')]
#[Layout('components.layouts.app')]
class Settings extends Component implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    public ?array $data = [];

    public array $timezones = [];

    public bool $hasWakatimeKey = false;

    public function mount(): void
    {
        $this->timezones = $this->timezoneOptions();

        /** @var User $user */
        $user = auth()->user();
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

        $this->hasWakatimeKey = (bool) $profile->wakatime_api_key;

        $this->form->fill([
            'profile' => [
                'name' => $user->name,
                'username' => $profile->username,
                'focus_area' => $profile->focus_area,
                'bio' => $profile->bio,
                'avatar_url' => $profile->avatar_url,
                'social_links' => $profile->social_links ?? [],
            ],
            'notifications' => [
                'language' => $preferences['language'] ?? 'en',
                'timezone' => $preferences['timezone'] ?? 'Africa/Douala',
                'reminder_time' => $this->normalizeReminderTime($preferences['reminder_time'] ?? null),
                'channels' => collect($preferences['channels'] ?? [])->filter()->keys()->all(),
                'notification_types' => collect($preferences['notification_types'] ?? [])->filter()->keys()->all(),
            ],
            'ai' => [
                'provider' => $preferences['ai_provider'] ?? 'groq',
                'tone' => $preferences['tone'] ?? 'neutral',
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
        $profile = auth()->user()->profile;

        $sectionHeadingClass = 'text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4';

        return $schema
            ->statePath('data')
            ->schema([
                // Section Profil Public
                Placeholder::make('profile_section')
                    ->content('Profil public')
                    ->extraAttributes(['class' => $sectionHeadingClass]),

                TextInput::make('profile.name')
                    ->label('Nom complet')
                    ->maxLength(255)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('profile.username')
                    ->label('Pseudo')
                    ->maxLength(32)
                    ->helperText('Affiché dans les classements et sur les pages publiques.')
                    ->rules([
                        'nullable',
                        'alpha_dash',
                        Rule::unique('user_profiles', 'username')->ignore($profile?->id),
                    ])
                    ->columnSpan(1),

                TextInput::make('profile.focus_area')
                    ->label('Objectif principal')
                    ->placeholder('Apprendre Laravel, shipping quotidien...')
                    ->maxLength(120)
                    ->columnSpan(1),

                Textarea::make('profile.bio')
                    ->label('Bio')
                    ->rows(3)
                    ->helperText('160 caractères maximum.')
                    ->maxLength(160)
                    ->columnSpan(1),

                TextInput::make('profile.avatar_url')
                    ->label('Avatar (URL)')
                    ->url()
                    ->maxLength(255)
                    ->columnSpan(1),

                // Section Réseaux Sociaux
                TextEntry::make('social_section')
                    ->state('Réseaux sociaux')
                    ->extraAttributes(['class' => $sectionHeadingClass]),

                KeyValue::make('profile.social_links')
                    ->label('Réseaux sociaux')
                    ->keyLabel('Plateforme')
                    ->valueLabel('Nom d\'utilisateur ou URL')
                    ->keyPlaceholder('github')
                    ->valuePlaceholder('@username ou https://...')
                    ->addButtonLabel('Ajouter un réseau social')
                    ->reorderable()
                    ->columnSpan(1),

                // Section Notifications
                Placeholder::make('notifications_section')
                    ->content('Notifications quotidiennes')
                    ->extraAttributes(['class' => $sectionHeadingClass]),

                Select::make('notifications.language')
                    ->label('Langue')
                    ->options([
                        'en' => 'English',
                        'fr' => 'Français',
                    ])
                    ->required()
                    ->columnSpan(1),

                Select::make('notifications.timezone')
                    ->label('Fuseau horaire')
                    ->options($this->timezones)
                    ->searchable()
                    ->required()
                    ->columnSpan(1),

                TimePicker::make('notifications.reminder_time')
                    ->label('Heure du rappel (24h)')
                    ->native(false)
                    ->required()
                    ->columnSpan(1),

                CheckboxList::make('notifications.channels')
                    ->label('Canaux de notification')
                    ->options([
                        'email' => 'Email',
                        'slack' => 'Slack',
                        'push' => 'Push (bientôt)',
                    ])
                    ->columns(3)
                    ->columnSpan(1),

                CheckboxList::make('notifications.notification_types')
                    ->label('Types de notifications')
                    ->options([
                        'daily_reminder' => 'Rappel quotidien',
                        'weekly_digest' => 'Digest hebdomadaire (bientôt)',
                    ])
                    ->columns(2)
                    ->columnSpan(1),

                // Section IA
                Placeholder::make('ai_section')
                    ->content('Assistant IA')
                    ->extraAttributes(['class' => $sectionHeadingClass]),

                Select::make('ai.provider')
                    ->label('Modèle préféré')
                    ->options([
                        'groq' => 'Groq (rapide)',
                        'openai' => 'OpenAI',
                        'local' => 'Modèle local',
                    ])
                    ->required()
                    ->columnSpan(1),

                Select::make('ai.tone')
                    ->label('Ton des conseils')
                    ->options([
                        'neutral' => 'Neutre',
                        'fun' => 'Fun',
                    ])
                    ->required()
                    ->columnSpan(1),

                // Section Intégrations
                Placeholder::make('integrations_section')
                    ->content('Intégrations')
                    ->extraAttributes(['class' => $sectionHeadingClass]),

                TextInput::make('integrations.wakatime_api_key')
                    ->label('Clé API WakaTime')
                    ->password()
                    ->helperText('Collez votre clé API depuis https://wakatime.com/settings/api-key. Laisser vide pour ne pas remplacer la clé existante.')
                    ->columnSpan(1),

                Toggle::make('integrations.wakatime_hide_project_names')
                    ->label('Masquer les noms des projets synchronisés')
                    ->helperText('Utilise des noms génériques lors de l’affichage des données WakaTime.')
                    ->inline(false)
                    ->columnSpan(1),

                Toggle::make('integrations.wakatime_remove_key')
                    ->label('Supprimer la clé WakaTime enregistrée')
                    ->helperText('Activez cette option et enregistrez pour supprimer la clé stockée.')
                    ->inline(false)
                    ->columnSpan(1),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /** @var User $user */
        $user = auth()->user();
        $profile = $user->profile;

        $user->forceFill([
            'name' => Str::squish($data['profile']['name'] ?? $user->name),
        ])->save();

        $username = $data['profile']['username'] ?? null;
        $focusArea = $data['profile']['focus_area'] ?? null;
        $bio = $data['profile']['bio'] ?? null;
        $avatarUrl = $data['profile']['avatar_url'] ?? null;

        $socialLinksArray = [];
        foreach ($data['profile']['social_links'] ?? [] as $platform => $link) {
            if (filled($platform) && filled($link)) {
                $key = Str::of($platform)->lower()->slug('_')->value();
                $socialLinksArray[$key] = trim($link);
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

        $this->hasWakatimeKey = (bool) ($profileUpdates['wakatime_api_key'] ?? $profile->wakatime_api_key);

        $preferences = $profile->preferences ?? [];

        $channels = array_fill_keys($data['notifications']['channels'] ?? [], true);
        $notificationTypes = array_fill_keys($data['notifications']['notification_types'] ?? [], true);

        $reminderTime = $this->normalizeReminderTime($data['notifications']['reminder_time'] ?? null);

        $updatedPreferences = array_replace_recursive($user->profilePreferencesDefaults(), $preferences, [
            'language' => $data['notifications']['language'] ?? 'en',
            'timezone' => $data['notifications']['timezone'] ?? 'Africa/Douala',
            'reminder_time' => $reminderTime,
            'channels' => array_merge([
                'email' => false,
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
        ]);

        $profile->forceFill([
            'preferences' => $updatedPreferences,
        ])->save();

        Notification::make()
            ->title('Paramètres mis à jour')
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

    public function render(): View
    {
        return view('livewire.page.settings', [
            'profile' => auth()->user()->profile,
            'hasWakatimeKey' => $this->hasWakatimeKey,
        ]);
    }
}
