<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = -2;

    public static function getModelLabel(): string
    {
        return __('Utilisateur');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Utilisateurs');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(static::getEloquentQuery())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->copyMessage('Email copié')
                    ->copyMessageDuration(1_000)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('profile.username')
                    ->label('Pseudo')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('profile.preferences.channels.telegram')
                    ->label('Telegram')
                    ->state(fn(User $record) => (bool) data_get($record->profile?->preferences, 'channels.telegram'))
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('profile.preferences.language')
                    ->label('Langue')
                    ->badge()
                    ->icon('heroicon-o-language')
                    ->formatStateUsing(fn(?string $state) => strtoupper((string) $state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('profile.preferences.timezone')
                    ->label('Fuseau horaire')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('daily_logs_count')
                    ->label('Logs')
                    ->counts('dailyLogs')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Administrateur')
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('has_telegram')
                    ->label('Telegram lié')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('notificationChannels', fn($channel) => $channel->where('channel', 'telegram')->where('is_active', true)),
                        false: fn(Builder $query) => $query->whereDoesntHave('notificationChannels', fn($channel) => $channel->where('channel', 'telegram')->where('is_active', true)),
                        blank: fn(Builder $query) => $query,
                    ),
                Tables\Filters\SelectFilter::make('language')
                    ->label('Langue')
                    ->options([
                        'fr' => 'FR',
                        'en' => 'EN',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->whereHas('profile', function (Builder $profile) use ($data): void {
                            $profile->where('preferences->language', $data['value']);
                        });
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('Modifier')),
            ])
            ->toolbarActions([]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Informations générales'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Nom'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Toggle::make('is_admin')
                            ->label(__('Administrateur'))
                            ->helperText(__('Donne accès au panneau d’administration.')),
                        Placeholder::make('created_at')
                            ->label(__('Inscrit le'))
                            ->content(fn(User $record): string => $record->created_at?->translatedFormat('d M Y H:i') ?? '—'),
                    ]),
                Section::make(__('Profil'))
                    ->relationship('profile')
                    ->columns(2)
                    ->schema([
                        TextInput::make('username')
                            ->label(__('Pseudo'))
                            ->maxLength(50),
                        TextInput::make('focus_area')
                            ->label(__('Objectif principal'))
                            ->maxLength(120),
                        Textarea::make('bio')
                            ->label(__('Bio'))
                            ->rows(4)
                            ->columnSpanFull()
                            ->maxLength(160),
                    ]),
                Section::make(__('Notifications'))
                    ->columns(2)
                    ->schema([
                        Placeholder::make('language')
                            ->label(__('Langue'))
                            ->content(fn(User $record): string => (string) data_get($record->profile?->preferences, 'language', '—')),
                        Placeholder::make('timezone')
                            ->label(__('Fuseau horaire'))
                            ->content(fn(User $record): string => (string) data_get($record->profile?->preferences, 'timezone', '—')),
                        Placeholder::make('reminder_time')
                            ->label(__('Rappel quotidien'))
                            ->content(fn(User $record): string => (string) data_get($record->profile?->preferences, 'reminder_time', '—')),
                        Placeholder::make('channels')
                            ->label(__('Canaux actifs'))
                            ->columnSpanFull()
                            ->content(function (User $record): string {
                                $channels = collect(data_get($record->profile?->preferences, 'channels', []))
                                    ->filter(fn($enabled) => $enabled)
                                    ->keys()
                                    ->map(fn(string $channel) => ucfirst($channel));

                                return $channels->isEmpty()
                                    ? __('Aucun canal activé')
                                    : $channels->implode(', ');
                            }),
                    ]),
                Section::make(__('WakaTime'))
                    ->columns(2)
                    ->schema([
                        Placeholder::make('wakatime_status')
                            ->label(__('Statut'))
                            ->content(function (User $record): string {
                                return $record->profile?->wakatime_api_key
                                    ? __('Connecté')
                                    : __('Non configuré');
                            }),
                        Placeholder::make('wakatime_hide_projects')
                            ->label(__('Masquer les projets'))
                            ->content(function (User $record): string {
                                $settings = $record->profile?->wakatime_settings ?? [];
                                $preferences = $record->profile?->preferences ?? [];
                                $hideNames = (bool) data_get(
                                    $settings,
                                    'hide_project_names',
                                    data_get($preferences, 'wakatime.hide_project_names', true)
                                );

                                return $hideNames ? __('Oui') : __('Non');
                            }),
                        Placeholder::make('wakatime_last_synced_at')
                            ->label(__('Dernière synchronisation'))
                            ->content(function (User $record): string {
                                $lastSynced = data_get($record->profile?->wakatime_settings, 'last_synced_at');

                                if (! $lastSynced) {
                                    return __('Jamais');
                                }

                                try {
                                    return Carbon::parse($lastSynced)->translatedFormat('d M Y H:i');
                                } catch (\Throwable) {
                                    return $lastSynced;
                                }
                            }),
                        Placeholder::make('wakatime_last_error')
                            ->label(__('Dernière erreur'))
                            ->columnSpanFull()
                            ->content(function (User $record): string {
                                $lastError = data_get($record->profile?->wakatime_settings, 'last_error');

                                return $lastError ? (string) $lastError : __('Aucune');
                            }),
                    ]),
                Section::make(__('Telegram'))
                    ->columns(2)
                    ->schema([
                        Placeholder::make('telegram_chat_id')
                            ->label(__('Chat ID'))
                            ->content(function (User $record): string {
                                $channel = $record->notificationChannels->firstWhere('channel', 'telegram');

                                return $channel?->value ?? '—';
                            }),
                        Placeholder::make('telegram_username')
                            ->label(__('Utilisateur Telegram'))
                            ->content(function (User $record): string {
                                $channel = $record->notificationChannels->firstWhere('channel', 'telegram');

                                return $channel?->metadata['username'] ?? '—';
                            }),
                        Placeholder::make('telegram_language')
                            ->label(__('Langue Telegram'))
                            ->content(function (User $record): string {
                                $channel = $record->notificationChannels->firstWhere('channel', 'telegram');

                                return $channel?->language ?? '—';
                            }),
                        Placeholder::make('telegram_status')
                            ->label(__('Statut'))
                            ->content(function (User $record): string {
                                $channel = $record->notificationChannels->firstWhere('channel', 'telegram');

                                return $channel?->is_active ? __('Actif') : __('Inactif');
                            }),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['profile', 'notificationChannels'])
            ->withCount('dailyLogs');
    }
}
