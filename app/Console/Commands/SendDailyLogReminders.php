<?php

namespace App\Console\Commands;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Notifications\DailyLogReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class SendDailyLogReminders extends Command
{
    protected $signature = 'daily-logs:send-reminders';

    protected $description = 'Send daily log reminders to users who have not logged today.';

    public function handle(): int
    {
        $today = Carbon::today();

        $runs = ChallengeRun::query()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->with(['owner', 'participants'])
            ->get();

        $remindersSent = 0;

        foreach ($runs as $run) {
            $users = collect([$run->owner])->merge($run->participants)->filter()->unique('id');

            foreach ($users as $user) {
                $hasEntry = DailyLog::query()
                    ->where('challenge_run_id', $run->id)
                    ->where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->exists();

                if ($hasEntry) {
                    continue;
                }

                $cacheKey = sprintf('daily-log-reminder:%s:%s:%s', $run->id, $user->id, $today->toDateString());

                if (! Cache::add($cacheKey, true, $today->copy()->endOfDay())) {
                    continue;
                }

                Notification::send($user, new DailyLogReminder($run));
                $remindersSent++;
            }
        }

        $this->info("Rappels envoyés : {$remindersSent}");

        return self::SUCCESS;
    }
}
