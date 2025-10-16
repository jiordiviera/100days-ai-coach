<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyLogResource\Pages;
use App\Models\ChallengeRun;
use App\Models\DailyLog;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DailyLogResource extends Resource
{
    protected static ?string $model = DailyLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('day_number')
                    ->label(__('Day'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label(__('Author'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('challengeRun.title')
                    ->label(__('Challenge'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state) => $state ?? __('N/A')),
                IconColumn::make('is_visible')
                    ->label(__('Visible'))
                    ->state(fn (DailyLog $record): bool => blank($record->hidden_at))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash'),
                TextColumn::make('moderator.name')
                    ->label(__('Last Moderator'))
                    ->toggleable()
                    ->placeholder(__('—')),
            ])
            ->filters([
                SelectFilter::make('visibility')
                    ->label(__('Visibility'))
                    ->options([
                        'visible' => __('Visible'),
                        'hidden' => __('Hidden'),
                    ])
                    ->query(function (Builder $query, array $state): void {
                        match ($state['value']) {
                            'visible' => $query->whereNull('hidden_at'),
                            'hidden' => $query->whereNotNull('hidden_at'),
                            default => null,
                        };
                    }),
                SelectFilter::make('challenge_run_id')
                    ->label(__('Challenge Run'))
                    ->options(fn () => ChallengeRun::query()
                        ->orderBy('title')
                        ->pluck('title', 'id')
                        ->toArray()),
            ])
            ->actions([
                Action::make('hide')
                    ->label(__('Hide'))
                    ->color('danger')
                    ->icon('heroicon-o-eye-slash')
                    ->visible(fn (DailyLog $record): bool => blank($record->hidden_at))
                    ->form([
                        Textarea::make('moderation_notes')
                            ->label(__('Reason'))
                            ->rows(3)
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (DailyLog $record, array $data): void {
                        $record->update([
                            'hidden_at' => now(),
                            'moderated_by_id' => Auth::id(),
                            'moderation_notes' => $data['moderation_notes'] ?? null,
                        ]);
                    }),
                Action::make('unhide')
                    ->label(__('Restore'))
                    ->color('success')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (DailyLog $record): bool => filled($record->hidden_at))
                    ->requiresConfirmation()
                    ->action(function (DailyLog $record): void {
                        $record->update([
                            'hidden_at' => null,
                            'moderated_by_id' => Auth::id(),
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('hideSelected')
                    ->label(__('Hide Selected'))
                    ->color('danger')
                    ->icon('heroicon-o-eye-slash')
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('moderation_notes')
                            ->label(__('Reason'))
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function ($records, array $data): void {
                        $records->each(function (DailyLog $record) use ($data): void {
                            if (blank($record->hidden_at)) {
                                $record->update([
                                    'hidden_at' => now(),
                                    'moderated_by_id' => Auth::id(),
                                    'moderation_notes' => $data['moderation_notes'] ?? null,
                                ]);
                            }
                        });
                    }),
                BulkAction::make('restoreSelected')
                    ->label(__('Restore Selected'))
                    ->color('success')
                    ->icon('heroicon-o-eye')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each(function (DailyLog $record): void {
                            if (filled($record->hidden_at)) {
                                $record->update([
                                    'hidden_at' => null,
                                    'moderated_by_id' => Auth::id(),
                                ]);
                            }
                        });
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyLogs::route('/'),
        ];
    }
}
