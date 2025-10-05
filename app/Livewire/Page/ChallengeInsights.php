<?php

namespace App\Livewire\Page;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\TaskComment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Insights Challenge')]
#[Layout('components.layouts.app')]
class ChallengeInsights extends Component
{
    public ChallengeRun $run;

    public array $overview = [];

    public array $participantStats = [];

    public array $milestones = [];

    public array $activity = [];

    public array $projectStats = [];

    public function mount(ChallengeRun $run): void
    {
        $this->run = $run->load([
            'owner',
            'participants',
            'projects.tasks',
        ]);

        abort_unless($this->canView(), 403);

        $this->prepareInsights();
    }

    protected function canView(): bool
    {
        $user = auth()->user();
        if ($user->id === $this->run->owner_id) {
            return true;
        }

        return $this->run->participants->contains(fn ($participant) => $participant->id === $user->id);
    }

    protected function prepareInsights(): void
    {
        $logs = DailyLog::query()
            ->where('challenge_run_id', $this->run->id)
            ->get();

        $participants = $this->collectParticipants();

        $this->overview = $this->buildOverview($logs, $participants);
        $this->participantStats = $this->buildParticipantStats($logs, $participants);
        $this->milestones = $this->buildMilestones($logs, $participants);
        $this->activity = $this->buildActivitySeries($logs);
        $this->projectStats = $this->buildProjectStats();
    }

    protected function collectParticipants(): Collection
    {
        return collect([$this->run->owner])
            ->merge($this->run->participants)
            ->filter()
            ->unique('id')
            ->values();
    }

    protected function buildOverview(Collection $logs, Collection $participants): array
    {
        $target = max(1, (int) $this->run->target_days);
        $totalParticipants = $participants->count();
        $totalLogs = $logs->count();
        $totalHours = (float) $logs->sum('hours_coded');
        $averageHours = $totalLogs > 0 ? round($totalHours / $totalLogs, 1) : 0.0;
        $hoursPerParticipant = $totalParticipants > 0 ? round($totalHours / $totalParticipants, 1) : 0.0;

        $completionAverage = 0;
        if ($totalParticipants > 0) {
            $completionAverage = $participants->map(function ($user) use ($logs, $target) {
                $count = $logs->where('user_id', $user->id)->count();

                return ($count / $target) * 100;
            })->average();
            $completionAverage = (int) round(min(100, $completionAverage));
        }

        $startDate = $this->run->start_date ? Carbon::parse($this->run->start_date) : null;
        $daysElapsed = $startDate ? ($startDate->diffInDays(Carbon::today()) + 1) : null;
        $daysRemaining = ($startDate && $target) ? max(0, $target - $daysElapsed) : null;

        $commentsCount = TaskComment::query()
            ->whereHas('task.project', fn ($query) => $query->where('challenge_run_id', $this->run->id))
            ->count();

        $projectTasks = $this->run->projects->flatMap->tasks;
        $completedTasks = $projectTasks->where('is_completed', true)->count();

        return [
            'totalParticipants' => $totalParticipants,
            'totalLogs' => $totalLogs,
            'totalHours' => round($totalHours, 1),
            'averageHours' => $averageHours,
            'hoursPerParticipant' => $hoursPerParticipant,
            'completionAverage' => $completionAverage,
            'projectsCount' => $this->run->projects->count(),
            'tasksTotal' => $projectTasks->count(),
            'tasksCompleted' => $completedTasks,
            'commentsCount' => $commentsCount,
            'startDate' => $startDate,
            'daysElapsed' => $daysElapsed,
            'daysRemaining' => $daysRemaining,
            'targetDays' => $target,
        ];
    }

    protected function buildParticipantStats(Collection $logs, Collection $participants): array
    {
        $target = max(1, (int) $this->run->target_days);
        $logsByUser = $logs->groupBy('user_id');

        $rows = [];
        foreach ($participants as $user) {
            $userLogs = $logsByUser->get($user->id, collect());
            $logsCount = $userLogs->count();
            $hours = (float) $userLogs->sum('hours_coded');
            $lastLog = $userLogs->sortByDesc(fn ($log) => $log->date ?? $log->created_at)->first();
            $streak = $this->computeStreak($userLogs);
            $percent = (int) round(min(100, ($logsCount / $target) * 100));

            $rows[] = [
                'user' => $user,
                'logs' => $logsCount,
                'hours' => round($hours, 1),
                'streak' => $streak,
                'percent' => $percent,
                'lastLogAt' => $lastLog?->date ? Carbon::parse($lastLog->date) : null,
            ];
        }

        return collect($rows)
            ->sortByDesc('logs')
            ->values()
            ->all();
    }

    protected function buildMilestones(Collection $logs, Collection $participants): array
    {
        $target = max(1, (int) $this->run->target_days);
        $startDate = $this->run->start_date ? Carbon::parse($this->run->start_date) : null;
        $completionAverage = $this->overview['completionAverage'] ?? 0;

        $checkpoints = [
            ['label' => '25%', 'ratio' => 0.25],
            ['label' => '50%', 'ratio' => 0.50],
            ['label' => '75%', 'ratio' => 0.75],
            ['label' => '100%', 'ratio' => 1.00],
        ];

        return collect($checkpoints)->map(function (array $checkpoint) use ($target, $startDate, $completionAverage) {
            $targetDay = (int) ceil($target * $checkpoint['ratio']);
            $expectedDate = $startDate?->copy()->addDays($targetDay - 1);

            return [
                'label' => $checkpoint['label'],
                'targetDay' => $targetDay,
                'expectedDate' => $expectedDate,
                'achieved' => $completionAverage >= ($checkpoint['ratio'] * 100),
            ];
        })->all();
    }

    protected function buildActivitySeries(Collection $logs): array
    {
        $series = $logs
            ->filter(fn ($log) => $log->date)
            ->groupBy(fn ($log) => Carbon::parse($log->date)->toDateString())
            ->map(function ($group, $date) {
                $totalHours = (float) $group->sum('hours_coded');

                return [
                    'date' => Carbon::parse($date),
                    'logs' => $group->count(),
                    'hours' => round($totalHours, 2),
                ];
            })
            ->sortBy('date')
            ->values()
            ->all();

        return array_slice($series, max(0, count($series) - 10));
    }

    protected function buildProjectStats(): array
    {
        return $this->run->projects->map(function ($project) {
            $tasks = $project->tasks;
            $total = $tasks->count();
            $completed = $tasks->where('is_completed', true)->count();

            return [
                'project' => $project,
                'tasksTotal' => $total,
                'tasksCompleted' => $completed,
                'completion' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
        })->all();
    }

    protected function computeStreak(Collection $userLogs): int
    {
        if ($userLogs->isEmpty()) {
            return 0;
        }

        $dates = $userLogs
            ->filter(fn ($log) => $log->date)
            ->map(fn ($log) => Carbon::parse($log->date)->startOfDay())
            ->unique()
            ->sortDesc()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $today = Carbon::today();
        $expected = $today->copy();
        $streak = 0;

        foreach ($dates as $idx => $date) {
            if ($streak === 0) {
                if ($date->isSameDay($expected)) {
                    $streak++;
                    $expected->subDay();

                    continue;
                }

                if ($date->isSameDay($expected->copy()->subDay())) {
                    $streak++;
                    $expected = $date->copy()->subDay();

                    continue;
                }

                break;
            }

            if ($date->isSameDay($expected)) {
                $streak++;
                $expected->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    public function render(): View
    {
        return view('livewire.page.challenge-insights');
    }
}
