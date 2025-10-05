<?php

namespace App\Livewire\Page;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Leaderboard extends Component
{
    use WithPagination;

    public ?string $challengeFilter = null;

    public array $challengeOptions = [];

    protected $queryString = [
        'challengeFilter' => ['except' => null],
        'page' => ['except' => 1],
    ];

    protected int $perPage = 20;

    public function mount(): void
    {
        $this->challengeOptions = ChallengeRun::query()
            ->orderByDesc('start_date')
            ->pluck('title', 'id')
            ->toArray();
    }

    public function updatedChallengeFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.page.leaderboard', [
            'leaderboard' => $this->buildLeaderboard(),
        ]);
    }

    protected function buildLeaderboard(): LengthAwarePaginator
    {
        $baseQuery = DailyLog::query()
            ->selectRaw('user_id, COUNT(DISTINCT date) as days_active_total, MAX(date) as last_log_date')
            ->when($this->challengeFilter, fn ($query) => $query->where('challenge_run_id', $this->challengeFilter))
            ->groupBy('user_id');

        $stats = $baseQuery->get();

        if ($stats->isEmpty()) {
            return $this->emptyPaginator();
        }

        $userIds = $stats->pluck('user_id')->all();
        $users = User::with('profile')->whereIn('id', $userIds)->get()->keyBy('id');

        $logsByUser = DailyLog::query()
            ->select('user_id', 'date')
            ->when($this->challengeFilter, fn ($query) => $query->where('challenge_run_id', $this->challengeFilter))
            ->whereIn('user_id', $userIds)
            ->orderBy('user_id')
            ->orderByDesc('date')
            ->get()
            ->groupBy('user_id');

        $entries = $stats->map(function ($stat) use ($users, $logsByUser) {
            $userId = $stat->user_id;
            $user = $users->get($userId);
            $logs = $logsByUser->get($userId, collect());

            $streak = $this->computeStreak($logs);

            return [
                'user_id' => $userId,
                'user' => $user,
                'streak' => $streak,
                'days_active_total' => (int) $stat->days_active_total,
                'last_log_date' => $stat->last_log_date ? Carbon::parse($stat->last_log_date) : null,
            ];
        });

        $sorted = $entries->sort(function (array $a, array $b) {
            if ($a['streak'] === $b['streak']) {
                if ($a['days_active_total'] === $b['days_active_total']) {
                    return $b['last_log_date'] <=> $a['last_log_date'];
                }

                return $b['days_active_total'] <=> $a['days_active_total'];
            }

            return $b['streak'] <=> $a['streak'];
        })->values();

        $total = $sorted->count();
        $currentPage = $this->getPage();
        $items = $sorted->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    protected function computeStreak(Collection $logs): int
    {
        if ($logs->isEmpty()) {
            return 0;
        }

        $dates = $logs
            ->pluck('date')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->startOfDay())
            ->unique()
            ->sortDesc()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $expected = $dates->first()->copy();
        $streak = 0;

        foreach ($dates as $date) {
            if ($date->equalTo($expected)) {
                $streak++;
                $expected->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    protected function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $this->perPage, $this->getPage(), [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}
