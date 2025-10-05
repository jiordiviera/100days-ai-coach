<?php

namespace App\Console\Commands;

use App\Models\DailyLog;
use App\Models\NotificationOutbox;
use App\Models\User;
use App\Notifications\WeeklyDigestNotification;
use App\Services\Ai\AiManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class SendWeeklyDigest extends Command
{
    protected $signature = 'digest:weekly';

    protected $description = 'Envoie le digest hebdomadaire IA aux utilisateurs opt-in.';

    public function handle(AiManager $aiManager): int
    {
        $nowUtc = now();

        User::query()
            ->with('profile')
            ->each(function (User $user) use ($nowUtc, $aiManager): void {
                $profile = $user->profile;

                if (! $profile) {
                    return;
                }

                $preferences = $profile->preferences ?? $user->profilePreferencesDefaults();

                if (! data_get($preferences, 'notification_types.weekly_digest', false)) {
                    return;
                }

                if (! data_get($preferences, 'channels.email', false)) {
                    return;
                }

                $timezone = data_get($preferences, 'timezone', 'UTC');
                $localNow = $nowUtc->copy()->setTimezone($timezone);

                if (! $localNow->isSunday()) {
                    return;
                }

                $weekEnd = $localNow->copy()->startOfDay();
                $weekStart = $weekEnd->copy()->subDays(6);

                if ($this->alreadySentFor($user->id, $weekEnd)) {
                    return;
                }

                $logs = DailyLog::query()
                    ->where('user_id', $user->id)
                    ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->orderBy('date')
                    ->get();

                if ($logs->isEmpty()) {
                    return;
                }

                $totalHours = (float) $logs->sum(fn ($log) => (float) ($log->hours_coded ?? 0));
                $logCount = $logs->count();

                $payload = [
                    'week_start' => $weekStart->toDateString(),
                    'week_ending' => $weekEnd->toDateString(),
                    'timezone' => $timezone,
                    'log_count' => $logCount,
                    'total_hours' => round($totalHours, 2),
                ];

                $outbox = NotificationOutbox::query()->create([
                    'user_id' => $user->id,
                    'type' => 'weekly_digest',
                    'channel' => 'mail',
                    'payload' => $payload,
                    'scheduled_at' => $weekEnd->copy()->setTimezone('UTC'),
                    'status' => 'queued',
                ]);

                try {
                    $result = $aiManager->generateInsights($this->buildSyntheticLog($user, $logs), true);

                    $metrics = [
                        'log_count' => $logCount,
                        'total_hours' => round($totalHours, 2),
                    ];

                    $ai = [
                        'summary' => $result->summary,
                        'tags' => $result->tags,
                        'coach_tip' => $result->coachTip,
                        'share_draft' => $result->shareDraft,
                        'model' => $result->model,
                        'latency_ms' => $result->latencyMs,
                        'cost_usd' => $result->costUsd,
                    ];

                    Notification::send($user, new WeeklyDigestNotification($weekStart, $weekEnd, $metrics, $ai, $timezone));

                    $outbox->forceFill([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'error' => null,
                        'payload' => array_merge($payload, ['ai' => Arr::only($ai, ['model', 'latency_ms', 'cost_usd'])]),
                    ])->save();
                } catch (Throwable $exception) {
                    $outbox->forceFill([
                        'status' => 'failed',
                        'error' => $exception->getMessage(),
                    ])->save();

                    Log::error('weekly-digest.failed', [
                        'user_id' => $user->id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });

        $this->info('Digests hebdomadaires traités.');

        return self::SUCCESS;
    }

    protected function alreadySentFor(string $userId, Carbon $weekEnd): bool
    {
        return NotificationOutbox::query()
            ->where('user_id', $userId)
            ->where('type', 'weekly_digest')
            ->where('payload->week_ending', $weekEnd->toDateString())
            ->exists();
    }

    protected function buildSyntheticLog(User $user, $logs): DailyLog
    {
        $notes = $logs->map(fn (DailyLog $log) => $log->notes ?: ($log->summary_md ? strip_tags($log->summary_md) : null))
            ->filter()
            ->map(fn ($text, $index) => sprintf('Day %d: %s', $index + 1, Str::of($text)->squish()->limit(180)))
            ->implode("\n");

        $learnings = $logs->pluck('learnings')->filter()->map(fn ($text) => Str::of($text)->squish()->limit(160))->implode("\n");

        $projects = $logs->flatMap(fn (DailyLog $log) => $log->projects_worked_on ?? [])->unique()->values()->all();

        $synthetic = new DailyLog([
            'day_number' => (int) $logs->max('day_number'),
            'notes' => $notes,
            'learnings' => $learnings,
            'projects_worked_on' => $projects,
            'hours_coded' => $logs->avg('hours_coded') ?: 0,
        ]);

        $first = $logs->first();
        if ($first) {
            $synthetic->setRelation('challengeRun', $first->challengeRun);
        }

        $synthetic->setRelation('user', $user);

        return $synthetic;
    }
}
