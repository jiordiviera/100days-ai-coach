<?php

namespace App\Support;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\Badges\StreakPunchlineGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BadgeEvaluator
{
    public function evaluate(User $user, ChallengeRun $run, array $dailyProgress): array
    {
        $definitions = collect(config('badges.badges', []));
        $currentBadges = $user->badges->pluck('badge_key')->all();

        $computed = $dailyProgress['badges'] ?? [];

        $newlyAwarded = [];
        $generator = app(StreakPunchlineGenerator::class);

        foreach ($computed as $badge) {
            $key = $badge['id'] ?? null;
            if (! $key) {
                continue;
            }

            if (! in_array($key, $currentBadges, true)) {
                $meta = [
                    'run_id' => $run->id,
                    'awarded_for' => $badge,
                ];

                if ($key === 'streak_7') {
                    $punchline = $generator->generate($user, $run);
                    $meta['punchline'] = $punchline['text'] ?? null;
                    $meta['ai'] = [
                        'model' => $punchline['ai_model'] ?? null,
                        'latency_ms' => $punchline['ai_latency_ms'] ?? null,
                        'cost_usd' => $punchline['ai_cost_usd'] ?? null,
                    ];
                }

                UserBadge::create([
                    'user_id' => $user->id,
                    'badge_key' => $key,
                    'meta' => $meta,
                    'awarded_at' => Carbon::now(),
                ]);
                $definition = $definitions->get($key, []);
                $newlyAwarded[] = [
                    'key' => $key,
                    'label' => $definition['label'] ?? ($badge['label'] ?? $key),
                    'description' => $definition['description'] ?? ($badge['description'] ?? null),
                    'color' => $definition['color'] ?? ($badge['color'] ?? 'primary'),
                ];
            }
        }

        $earned = $user->badges()->latest('awarded_at')->get()->map(function (UserBadge $badge) use ($definitions) {
            $def = $definitions->get($badge->badge_key, []);

            return [
                'key' => $badge->badge_key,
                'label' => $def['label'] ?? $badge->badge_key,
                'description' => $def['description'] ?? null,
                'color' => $def['color'] ?? 'primary',
                'awarded_at' => $badge->awarded_at,
            ];
        })->all();

        return [
            'earned' => $earned,
            'newly_awarded' => $newlyAwarded,
        ];
    }

    public function computeBadges(ChallengeRun $run, User $user): array
    {
        $logs = DailyLog::query()
            ->where('challenge_run_id', $run->id)
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->get();

        $streak = $this->computeStreak($logs);
        $totalLogs = $logs->count();
        $target = max(1, (int) $run->target_days);
        $todayDate = Carbon::today()->toDateString();
        $hasToday = $logs->contains(fn ($log) => $log->date === $todayDate);

        $dates = $logs->map(fn ($log) => $log->date ? Carbon::parse($log->date)->toDateString() : null)->filter();
        $lastSeven = collect(range(0, 6))->map(fn ($offset) => Carbon::today()->copy()->subDays($offset)->toDateString());
        $perfectWeek = $lastSeven->every(fn ($day) => $dates->contains($day));

        $badges = [];

        if ($streak >= 3) {
            $badges[] = [
                'id' => 'streak_3',
                'label' => 'Streak 3+',
                'color' => 'primary',
            ];
        }

        if ($streak >= 7) {
            $badges[] = [
                'id' => 'streak_7',
                'label' => 'Semaine en feu',
                'color' => 'success',
            ];
        }

        if ($totalLogs >= ceil($target / 2)) {
            $badges[] = [
                'id' => 'halfway',
                'label' => 'Mi-parcours',
                'color' => 'info',
            ];
        }

        if ($perfectWeek) {
            $badges[] = [
                'id' => 'perfect_week',
                'label' => 'Semaine parfaite',
                'color' => 'warning',
            ];
        }

        if ($hasToday) {
            $badges[] = [
                'id' => 'fresh_start',
                'label' => 'Entrée du jour',
                'color' => 'gray',
            ];
        }

        return $badges;
    }

    protected function computeStreak(Collection $logs): int
    {
        if ($logs->isEmpty()) {
            return 0;
        }

        $dates = $logs
            ->filter(fn (DailyLog $log) => $log->date)
            ->map(fn (DailyLog $log) => Carbon::parse($log->date)->startOfDay())
            ->unique()
            ->sortDesc()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $today = Carbon::today();
        $expected = $today->copy();
        $streak = 0;

        foreach ($dates as $date) {
            if ($streak === 0) {
                if ($date->isSameDay($expected) || $date->isSameDay($expected->copy()->subDay())) {
                    $streak++;
                    $expected = $date->copy()->subDay();
                } else {
                    break;
                }

                continue;
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
}
