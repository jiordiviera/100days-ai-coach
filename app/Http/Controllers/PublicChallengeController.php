<?php

namespace App\Http\Controllers;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicChallengeController extends Controller
{
    public function __invoke(string $slug): View
    {
        $cacheKey = "public-challenge:{$slug}";

        $data = Cache::get($cacheKey);
        if (! $data) {
            $data = $this->buildChallengePayload($slug);

            if ($data) {
                Cache::put($cacheKey, $data, now()->addMinutes(10));
            }
        }

        if (! $data) {
            abort(404);
        }

        $title = $data['meta']['title'] ?? config('app.name');
        $description = Str::limit($data['meta']['description'] ?? '', 160);

        seo()
            ->title($title)
            ->description($description)
            ->tag('og:type', 'website')
            ->twitterTitle($title)
            ->twitterDescription($description);

        return view('public.challenge', $data);
    }

    protected function buildChallengePayload(string $slug): ?array
    {
        $run = ChallengeRun::query()
            ->with([
                'owner.profile',
                'participantLinks.user.profile',
            ])
            ->where('public_slug', $slug)
            ->where('is_public', true)
            ->first();

        if (! $run) {
            return null;
        }

        $participants = $run->participantLinks
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();

        $publicParticipants = $participants
            ->filter(fn ($user) => optional($user->profile)->is_public)
            ->values();

        $publicLogs = DailyLog::query()
            ->with('user.profile')
            ->where('challenge_run_id', $run->id)
            ->publiclyVisible()
            ->latest('date')
            ->latest('created_at')
            ->limit(12)
            ->get();

        $totalLogs = DailyLog::query()
            ->where('challenge_run_id', $run->id)
            ->count();

        $metaTitle = sprintf('%s · Challenge public', $run->title);
        $metaDescription = $run->description
            ?: sprintf('Challenge #100DaysOfCode piloté par %s.', optional($run->owner)->name ?? 'la communauté');

        return [
            'run' => $run,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
            ],
            'participants' => $participants,
            'publicParticipants' => $publicParticipants,
            'publicLogs' => $publicLogs,
            'stats' => [
                'started_at' => $run->start_date ? Carbon::parse($run->start_date) : null,
                'target_days' => $run->target_days,
                'total_members' => $participants->count(),
                'public_members' => $publicParticipants->count(),
                'total_logs' => $totalLogs,
                'public_logs' => $publicLogs->count(),
                'completion_percent' => $this->computeCompletionPercent($totalLogs, $run->target_days, $participants->count()),
            ],
            'cta' => [
                'join_code' => $run->public_join_code,
            ],
            'ownerSocial' => $this->formatSocialLinks(optional($run->owner->profile)->social_links ?? []),
        ];
    }

    protected function computeCompletionPercent(int $totalLogs, int $targetDays, int $memberCount): int
    {
        $targetDays = max(1, $targetDays);
        $totalTargets = max(1, $targetDays * max(1, $memberCount));

        return (int) round(min(100, ($totalLogs / $totalTargets) * 100));
    }

    protected function formatSocialLinks(array $links): array
    {
        return Arr::map($links, fn ($value) => trim((string) $value));
    }
}
