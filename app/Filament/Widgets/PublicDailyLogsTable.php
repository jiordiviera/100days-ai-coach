<?php

namespace App\Filament\Widgets;

use App\Models\DailyLog;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PublicDailyLogsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getTableQuery(): Builder
    {
        return DailyLog::query()
            ->with(['user', 'challengeRun'])
            ->whereNotNull('public_token')
            ->latest('date')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('date')
                ->label(__('Date'))
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('user.name')
                ->label(__('Author'))
                ->searchable(),
            Tables\Columns\TextColumn::make('challengeRun.title')
                ->label(__('Challenge'))
                ->toggleable()
                ->wrap()
                ->placeholder(__('—')),
            Tables\Columns\IconColumn::make('hidden_at')
                ->label(__('Hidden'))
                ->boolean()
                ->trueIcon('heroicon-o-no-symbol')
                ->falseIcon('heroicon-o-eye'),
            Tables\Columns\TextColumn::make('moderator.name')
                ->label(__('Moderator'))
                ->placeholder(__('—')),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
