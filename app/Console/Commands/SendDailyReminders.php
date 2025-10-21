<?php

namespace App\Console\Commands;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\NotificationOutbox;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\DailyReminderNotification;
use App\Services\Notifications\NotificationChannelResolver;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendDailyReminders extends Command
{
    protected $signature = 'daily-logs:send-reminders';

    protected $description = 'Send timezone-aware daily reminders to users without a log entry for the day.';

    public function handle(): int
    {
        $nowUtc = now();
        $resolver = app(NotificationChannelResolver::class);

        User::query()
            ->with(['profile', 'notificationChannels'])
            ->each(function (User $user) use ($nowUtc, $resolver): void {
                $profile = $user->profile;

                if (! $profile) {
                    return;
                }

                $preferences = $profile->preferences ?? [];

                if (! data_get($preferences, 'notification_types.daily_reminder', false)) {
                    return;
                }

                $channels = $resolver->resolve($user, 'daily_reminder');

                if (empty($channels)) {
                    return;
                }

                $timezone = data_get($preferences, 'timezone', 'Africa/Douala');
                $reminderTime = data_get($preferences, 'reminder_time', '20:30');

                $localNow = $nowUtc->copy()->setTimezone($timezone);
                $reminderDateTime = Carbon::parse($reminderTime, $timezone)
                    ->setDate($localNow->year, $localNow->month, $localNow->day);

                if ($localNow->lessThan($reminderDateTime)) {
                    return;
                }

                if ($localNow->diffInMinutes($reminderDateTime) > 90) {
                    return;
                }

                $localDate = $reminderDateTime->toDateString();

                $run = $this->resolveActiveRun($user);

                if (! $run) {
                    foreach ($channels as $channel) {
                        $this->recordOutbox($user->id, $channel, 'skipped', $timezone, $reminderDateTime, [
                            'reason' => 'no_active_run',
                        ]);
                    }

                    return;
                }

                if ($this->hasLogForDate($user->id, $run->id, $localDate)) {
                    foreach ($channels as $channel) {
                        $this->recordOutbox($user->id, $channel, 'skipped', $timezone, $reminderDateTime, [
                            'run_id' => $run->id,
                            'reason' => 'log_already_recorded',
                        ]);
                    }

                    return;
                }

                $payload = [
                    'run_id' => $run->id,
                    'challenge_title' => $run->title,
                    'local_date' => $localDate,
                    'timezone' => $timezone,
                    'reminder_time' => $reminderTime,
                ];

                foreach ($channels as $channel) {
                    $driver = $channel === 'telegram' ? TelegramChannel::class : $channel;

                    if ($this->hasReminderAlready($user->id, $localDate, $channel)) {
                        continue;
                    }

                    $outbox = $this->recordOutbox($user->id, $channel, 'queued', $timezone, $reminderDateTime, $payload);

                    try {
                        Notification::sendNow($user, new DailyReminderNotification($run, $localDate, [
                            'timezone' => $timezone,
                            'reminder_time' => $reminderTime,
                        ]), [$driver]);

                        $outbox->forceFill([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'error' => null,
                        ])->save();
                    } catch (Throwable $exception) {
                        report($exception);

                        $outbox->forceFill([
                            'status' => 'failed',
                            'error' => $exception->getMessage(),
                        ])->save();
                    }
                }
            });

        return self::SUCCESS;
    }

    protected function hasReminderAlready(string $userId, string $localDate, string $channel): bool
    {
        return NotificationOutbox::query()
            ->where('user_id', $userId)
            ->where('type', 'daily_reminder')
            ->where('channel', $channel)
            ->whereDate('scheduled_at', $localDate)
            ->exists();
    }

    protected function recordOutbox(string $userId, string $channel, string $status, string $timezone, Carbon $reminderDateTime, array $payload = []): NotificationOutbox
    {
        return NotificationOutbox::query()->create([
            'user_id' => $userId,
            'type' => 'daily_reminder',
            'channel' => $channel,
            'payload' => $payload,
            'scheduled_at' => $reminderDateTime->copy()->setTimezone('UTC'),
            'status' => $status,
            'error' => null,
        ]);
    }

    protected function resolveActiveRun(User $user): ?ChallengeRun
    {
        return ChallengeRun::query()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn (Builder $participantQuery) => $participantQuery->where('user_id', $user->id));
            })
            ->latest('start_date')
            ->first();
    }

    protected function hasLogForDate(string $userId, string $runId, string $localDate): bool
    {
        return DailyLog::query()
            ->where('challenge_run_id', $runId)
            ->where('user_id', $userId)
            ->whereDate('date', $localDate)
            ->exists();
    }
}
