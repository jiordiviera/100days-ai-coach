<?php

namespace App\Filament\Widgets;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\SupportTicket;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class SiteStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        return [
            Stat::make(
                label: __('Total Users'),
                value: User::query()
                    ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                    ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                    ->count()
            )
                ->description(__('All registered makers'))
                ->icon('heroicon-o-user-group'),
            Stat::make(
                label: __('Active Runs'),
                value: ChallengeRun::query()->where('status', 'active')->count())
                ->description(__('Challenges currently in progress'))
                ->icon('heroicon-o-flag'),
            Stat::make(__('Public Logs'), DailyLog::query()->publiclyVisible()->count())
                ->description(__('Visible entries shared by the community'))
                ->icon('heroicon-o-document-text'),
            Stat::make(
                label: __('Open Tickets'),
                value: SupportTicket::query()->where('status', 'open')->count()
            )
                ->description(__('Feedback en attente de réponse'))
                ->icon(Heroicon::OutlinedLifebuoy)
                ->color('warning'),
        ];
    }
}
