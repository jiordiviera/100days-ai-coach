<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
