<?php

namespace App\Listeners;

use App\Events\DailyLogAiFailed;
use App\Models\DailyLog;
use App\Notifications\DailyLogAiFailedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserOfAiFailure implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(DailyLogAiFailed $event): void
    {
        $log = DailyLog::with('user')->find($event->dailyLogId);

        if (! $log || ! $log->user) {
            return;
        }

        $log->user->notify(new DailyLogAiFailedNotification($log, $event->message));
    }
}
