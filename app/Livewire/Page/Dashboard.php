<?php

namespace App\Livewire\Page;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\Task;
use App\Support\BadgeEvaluator;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public function getUserStats(): array
    {
        $user = auth()->user();
        $ownedProjects = $user->projects()->pluck('id');
        $memberProjects = $user->memberProjects()->pluck('project_id');
        $projectCount = $ownedProjects->merge($memberProjects)->unique()->count();

        // Active run = dernier run actif où l'utilisateur est owner ou participant
        $activeRun = ChallengeRun::query()
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn ($qq) => $qq->where('user_id', $user->id));
            })
            ->latest('start_date')
            ->first();

        $active = null;
        if ($activeRun) {
            $target = max(1, (int) $activeRun->target_days);
            $dayNumber = (int) (Carbon::parse($activeRun->start_date)->startOfDay()->diffInDays(Carbon::now()->startOfDay()) + 1);
            $myDone = DailyLog::where('challenge_run_id', $activeRun->id)->where('user_id', $user->id)->count();
            $myPercent = round(min(100, ($myDone / $target) * 100));
            $active = [
                'run' => $activeRun,
                'dayNumber' => $dayNumber,
                'targetDays' => $target,
                'myPercent' => $myPercent,
            ];
        }

        return [
            'projectCount' => $projectCount,
            'taskCount' => Task::where('user_id', $user->id)->count(),
            'completedTaskCount' => Task::where('user_id', $user->id)
                ->where('is_completed', true)
                ->count(),
            'active' => $active,
        ];
    }

    protected function getDailyProgress(?ChallengeRun $run): array
    {
        $default = [
            'runId' => null,
            'hasEntryToday' => false,
            'hoursToday' => null,
            'streak' => 0,
            'totalLogs' => 0,
            'lastEntryAt' => null,
            'completionPercent' => 0,
        ];

        if (! $run) {
            return $default;
        }

        $userId = auth()->id();
        $today = Carbon::today();

        $todayLog = DailyLog::query()
            ->where('challenge_run_id', $run->id)
            ->where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        $logDates = DailyLog::query()
            ->where('challenge_run_id', $run->id)
            ->where('user_id', $userId)
            ->orderByDesc('date')
            ->pluck('date')
            ->unique()
            ->map(fn ($date) => Carbon::parse($date))
            ->values();

        $streak = 0;
        $lastEntryAt = $logDates->first();

        if ($lastEntryAt) {
            $streak = 1;
            $previousDate = $lastEntryAt->copy();

            foreach ($logDates->skip(1) as $date) {
                if ($previousDate->diffInDays($date) === 1) {
                    $streak++;
                    $previousDate = $date->copy();
                } else {
                    break;
                }
            }
        }

        $totalLogs = $logDates->count();
        $percent = $totalLogs > 0 ? (int) round(min(100, ($totalLogs / max(1, (int) $run->target_days)) * 100)) : 0;

        $badges = $this->determineBadges($run, $logDates, $streak, $totalLogs, (bool) $todayLog);

        return [
            'runId' => $run->id,
            'hasEntryToday' => (bool) $todayLog,
            'hoursToday' => $todayLog?->hours_coded,
            'streak' => $streak,
            'totalLogs' => $totalLogs,
            'lastEntryAt' => $lastEntryAt,
            'completionPercent' => $percent,
            'badges' => $badges,
        ];
    }

    public function getRecentProjects($limit = 3)
    {
        $user = auth()->user();
        // Si un challenge actif existe, filtrer les projets liés à ce challenge
        $activeRun = ChallengeRun::query()
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn ($qq) => $qq->where('user_id', $user->id));
            })
            ->latest('start_date')
            ->first();
        //        dd($activeRun);
        $query = $user->projects()->with(['tasks'])->latest();
        if ($activeRun) {
            $query->where('challenge_run_id', $activeRun->id);
        }

        return $query->take($limit)->get();
    }

    public function getRecentTasks($limit = 5): \Illuminate\Database\Eloquent\Collection|array
    {
        return Task::with('project')
            ->where('user_id', auth()->id())
            ->latest()
            ->take($limit)
            ->get();
    }

    public function render(): View
    {
        $stats = $this->getUserStats();
        $recentProjects = $this->getRecentProjects();
        $recentTasks = $this->getRecentTasks();

        $run = $stats['active']['run'] ?? null;
        $dailyProgress = $this->getDailyProgress($run);
        $onboardingChecklist = $this->getOnboardingChecklist($stats, $dailyProgress);
        $earnedBadges = [];
        $newBadges = [];

        if ($run) {
            $badgeEvaluator = new BadgeEvaluator;
            $evaluation = $badgeEvaluator->evaluate(auth()->user()->loadMissing('badges'), $run, $dailyProgress);
            $earnedBadges = $evaluation['earned'];
            $newBadges = $evaluation['newly_awarded'];
        }

        return view('livewire.page.dashboard', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentTasks' => $recentTasks,
            'dailyProgress' => $dailyProgress,
            'earnedBadges' => $earnedBadges,
            'newBadges' => $newBadges,
            'onboardingChecklist' => $onboardingChecklist,
        ]);
    }

    protected function getOnboardingChecklist(array $stats, array $dailyProgress): array
    {
        $user = auth()->user();
        $profile = $user?->profile;

        $preferences = $profile?->preferences ?? $user->profilePreferencesDefaults();
        $checklist = (array) data_get($preferences, 'onboarding.checklist', []);

        $defaults = [
            'first_log' => false,
            'project_linked' => false,
            'reminder_configured' => data_get($checklist, 'reminder_configured', false),
            'public_share' => data_get($checklist, 'public_share', false),
        ];

        $checklist = array_merge($defaults, $checklist);
        $dirty = false;

        if (($dailyProgress['totalLogs'] ?? 0) > 0 && ! $checklist['first_log']) {
            $checklist['first_log'] = true;
            $dirty = true;
        }

        if (($stats['projectCount'] ?? 0) > 0 && ! $checklist['project_linked']) {
            $checklist['project_linked'] = true;
            $dirty = true;
        }

        $hasSharedLog = DailyLog::query()
            ->where('user_id', $user->id)
            ->whereNotNull('public_token')
            ->exists();

        if ($hasSharedLog && ! $checklist['public_share']) {
            $checklist['public_share'] = true;
            $dirty = true;
        }

        if ($dirty && $profile) {
            data_set($preferences, 'onboarding.checklist', $checklist);
            $profile->forceFill(['preferences' => $preferences])->save();
        }

        $items = [
            [
                'key' => 'first_log',
                'label' => 'Consigner ta première entrée',
                'description' => 'Rédige ton log du jour et déclenche l’IA.',
                'completed' => $checklist['first_log'],
                'url' => route('daily-challenge').'#daily-log-form',
            ],
            [
                'key' => 'project_linked',
                'label' => 'Associer un projet',
                'description' => 'Structure ton défi en missions concrètes.',
                'completed' => $checklist['project_linked'],
                'url' => route('projects.index'),
            ],
            [
                'key' => 'reminder_configured',
                'label' => 'Configurer ton rappel quotidien',
                'description' => 'Choisis l’heure idéale pour ne jamais manquer un log.',
                'completed' => $checklist['reminder_configured'],
                'url' => route('settings').'#notifications',
            ],
            [
                'key' => 'public_share',
                'label' => 'Partager ton log',
                'description' => 'Prépare un post LinkedIn/X pour célébrer ton avancée.',
                'completed' => $checklist['public_share'],
                'url' => route('daily-challenge').'#share-section',
            ],
        ];

        $allCompleted = collect($items)->every(fn ($item) => $item['completed']);

        return [
            'items' => $items,
            'all_completed' => $allCompleted,
        ];
    }

    protected function determineBadges(?ChallengeRun $run, Collection $logDates, int $streak, int $totalLogs, bool $hasEntryToday): array
    {
        $badges = [];
        $target = $run ? max(1, (int) $run->target_days) : 100;
        $today = Carbon::today();

        if ($streak >= 3) {
            $badges[] = [
                'id' => 'streak_3',
                'label' => 'Streak 3+',
                'description' => 'Trois jours successifs de régularité.',
                'color' => 'primary',
            ];
        }

        if ($streak >= 7) {
            $badges[] = [
                'id' => 'streak_7',
                'label' => 'Semaine en feu',
                'description' => 'Sept jours consécutifs renseignés.',
                'color' => 'success',
            ];
        }

        if ($totalLogs >= ceil($target / 2)) {
            $badges[] = [
                'id' => 'halfway',
                'label' => 'Mi-parcours',
                'description' => 'Vous avez couvert au moins la moitié du défi.',
                'color' => 'info',
            ];
        }

        $lastSeven = collect(range(0, 6))->map(fn ($offset) => $today->copy()->subDays($offset)->toDateString());
        $logsByDate = $logDates->map(fn (Carbon $date) => $date->toDateString())->flip();

        if ($lastSeven->every(fn ($day) => $logsByDate->has($day))) {
            $badges[] = [
                'id' => 'perfect_week',
                'label' => 'Semaine parfaite',
                'description' => 'Toutes les entrées des 7 derniers jours sont complètes.',
                'color' => 'warning',
            ];
        }

        if ($hasEntryToday && ! $badges) {
            $badges[] = [
                'id' => 'fresh-start',
                'label' => 'Entrée du jour',
                'description' => 'Belle constance aujourd’hui !',
                'color' => 'gray',
            ];
        }

        return $badges;
    }
}
