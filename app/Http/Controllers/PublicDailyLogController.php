<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicDailyLogController extends Controller
{
    public function show(Request $request, string $token)
    {
        /** @var DailyLog|null $log */
        $log = DailyLog::query()
            ->where('public_token', $token)
            ->with(['user.profile', 'challengeRun'])
            ->first();

        if (! $log) {
            abort(404);
        }

        $displayName = $log->user?->profile?->username ?? $log->user?->name ?? 'Participant';
        $title = sprintf('Jour %d — %s', (int) $log->day_number, $displayName);
        $excerpt = Str::limit(strip_tags($log->summary_md ?? $log->notes ?? 'Entrée partagée #100DaysOfCode.'), 160);

        seo()
            ->title($title)
            ->description($excerpt)
            ->tag('og:type', 'article')
            ->tag('og:article:author', $displayName)
            ->twitterTitle($title)
            ->twitterDescription($excerpt);

        return view('share.daily-log', [
            'log' => $log,
            'user' => $log->user,
            'challenge' => $log->challengeRun,
            'meta' => [
                'hours' => $log->hours_coded,
                'date' => $log->date,
            ],
        ]);
    }
}
