<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PublicDailyLogsTable;
use App\Filament\Widgets\SiteStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;

class AdminDashboard extends BaseDashboard
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationLabel = 'Admin Dashboard';

    protected static ?string $title = 'Admin Dashboard';

    /**
     * @return array<int, class-string<Widget>>
     */
    public function getWidgets(): array
    {
        $widgets = collect(parent::getWidgets())
            ->reject(fn ($widget) => in_array($widget, [SiteStatsOverview::class, PublicDailyLogsTable::class], true))
            ->all();

        return [
            SiteStatsOverview::class,
            PublicDailyLogsTable::class,
            ...$widgets,
        ];
    }
}
