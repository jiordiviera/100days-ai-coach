<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\UserBadge;
use App\Models\UserProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicProfileController extends Controller
{
    public function __invoke(string $username): View
    {
        $cacheKey = "public-profile:{$username}";

        $data = Cache::get($cacheKey);
        if (! $data) {
            $data = $this->buildProfilePayload($username);

            if ($data) {
                Cache::put($cacheKey, $data, now()->addMinutes(10));
            }
        }

        if (! $data) {
            abort(404);
        }

        return view('public.profile', $data);
    }

    protected function buildProfilePayload(string $username): ?array
    {
        $profile = UserProfile::query()
            ->with('user')
            ->where('username', $username)
            ->where('is_public', true)
            ->first();

        if (! $profile || ! $profile->user) {
            return null;
        }

        $user = $profile->user;

        $publicLogs = DailyLog::query()
            ->with('challengeRun:id,title,target_days')
            ->where('user_id', $user->id)
            ->whereNotNull('public_token')
            ->latest('date')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $allLogs = DailyLog::query()
            ->where('user_id', $user->id)
            ->orderBy('date')
            ->orderBy('day_number')
            ->get();

        $streaks = $this->computeStreaks($allLogs);

        $recentBadges = UserBadge::query()
            ->where('user_id', $user->id)
            ->latest('awarded_at')
            ->limit(8)
            ->get();

        $metaTitle = sprintf('%s · %s', $profile->username ?: $user->name, config('app.name'));
        $metaDescription = $profile->bio ?: sprintf('Progression de %s dans le challenge #100DaysOfCode.', $user->name);

        return [
            'profile' => $profile,
            'user' => $user,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
            ],
            'streaks' => $streaks,
            'publicLogs' => $publicLogs,
            'recentBadges' => $recentBadges,
            'stats' => [
                'total_logs' => $allLogs->count(),
                'public_logs' => $publicLogs->count(),
                'total_hours' => round((float) $allLogs->sum('hours_coded'), 1),
                'projects' => $this->aggregateProjects($allLogs),
            ],
            'socialLinks' => $this->formatSocialLinks($profile->social_links ?? []),
        ];
    }

    protected function computeStreaks(Collection $logs): array
    {
        if ($logs->isEmpty()) {
            return [
                'current' => 0,
                'longest' => 0,
                'last_log_at' => null,
            ];
        }

        $dates = $logs
            ->filter(fn (DailyLog $log) => $log->date)
            ->map(fn (DailyLog $log) => Carbon::parse($log->date)->startOfDay())
            ->unique(fn (Carbon $date) => $date->toDateString())
            ->sort()
            ->values();

        $longest = 0;
        $current = 0;
        $lastDate = null;

        foreach ($dates as $date) {
            if (! $lastDate || $date->isSameDay($lastDate->copy()->addDay())) {
                $current++;
            } else {
                $longest = max($longest, $current);
                $current = 1;
            }

            $lastDate = $date;
        }

        $longest = max($longest, $current);

        $today = Carbon::today();
        $isContinuing = $lastDate && ($lastDate->isSameDay($today) || $lastDate->isSameDay($today->copy()->subDay()));
        if (! $isContinuing) {
            $current = 0;
        }

        return [
            'current' => $current,
            'longest' => $longest,
            'last_log_at' => $lastDate,
        ];
    }

    protected function aggregateProjects(Collection $logs): array
    {
        $projects = [];

        foreach ($logs as $log) {
            foreach ($log->projects_worked_on ?? [] as $project) {
                $key = (string) $project;
                $projects[$key] = ($projects[$key] ?? 0) + 1;
            }
        }

        arsort($projects);

        return array_slice($projects, 0, 6, true);
    }

    protected function formatSocialLinks(array $links): array
    {
        return Arr::map($links, fn ($value) => trim((string) $value));
    }
}
