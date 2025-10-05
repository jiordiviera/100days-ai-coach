<?php

namespace App\Jobs;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use App\Services\WakaTime\Dto\WakaTimeSummary;
use App\Services\WakaTime\WakaTimeClient;
use App\Services\WakaTime\WakaTimeException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncWakaTimeForUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 300;

    public function __construct(
        public readonly string $userId,
        public readonly ?string $date = null,
    ) {
        $this->onQueue('integrations');
    }

    public function handle(WakaTimeClient $client): void
    {
        $user = User::with('profile')->find($this->userId);

        if (! $user || ! $user->profile || ! $user->profile->wakatime_api_key) {
            return;
        }

        $profile = $user->profile;
        $preferences = $profile->preferences ?? $user->profilePreferencesDefaults();
        $settings = $profile->wakatime_settings ?? [];

        $timezone = data_get($preferences, 'timezone', 'UTC');

        $targetDate = $this->resolveDate($timezone);

        $activeRun = $this->resolveActiveRun($user);
        if (! $activeRun) {
            $this->rememberSyncMeta($profile, ['last_error' => 'No active challenge run.']);

            return;
        }

        $startDate = Carbon::parse($activeRun->start_date)
            ->setTimezone($timezone)
            ->startOfDay();

        if ($targetDate->lt($startDate)) {
            $this->rememberSyncMeta($profile, ['last_error' => 'Challenge has not started yet.']);

            return;
        }

        $dayNumber = $startDate->diffInDays($targetDate) + 1;

        if ($dayNumber > (int) $activeRun->target_days) {
            $this->rememberSyncMeta($profile, ['last_error' => 'Target days exceeded; skipping sync.']);

            return;
        }

        try {
            $summary = $client->summary($profile->wakatime_api_key, $targetDate, $timezone);
            Log::info('wakatime.sync.success', [
                'user_id' => $user->id,
                'date' => $targetDate->toDateString(),
                'total_hours' => $summary->totalHours(),
            ]);
        } catch (WakaTimeException $exception) {
            Log::warning('wakatime.sync.failed', [
                'user_id' => $user->id,
                'date' => $targetDate->toDateString(),
                'message' => $exception->getMessage(),
            ]);

            $this->rememberSyncMeta($profile, ['last_error' => $exception->getMessage()]);

            return;
        }

        $this->persistDailyLog(
            $user,
            $activeRun,
            $summary,
            $dayNumber,
            (bool) data_get($settings, 'hide_project_names', data_get($preferences, 'wakatime.hide_project_names', true)),
            $timezone,
        );

        $this->rememberSyncMeta($profile, [
            'last_synced_at' => Carbon::now()->toIso8601String(),
            'last_error' => null,
        ]);
    }

    protected function resolveDate(string $timezone): Carbon
    {
        if ($this->date) {
            return Carbon::parse($this->date, $timezone)->startOfDay();
        }

        return Carbon::now($timezone)->startOfDay();
    }

    protected function resolveActiveRun(User $user): ?ChallengeRun
    {
        return ChallengeRun::query()
            ->where('status', 'active')
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn ($participantQuery) => $participantQuery->where('user_id', $user->id));
            })
            ->latest('start_date')
            ->first();
    }

    protected function persistDailyLog(User $user, ChallengeRun $run, WakaTimeSummary $summary, int $dayNumber, bool $hideProjectNames, string $timezone): void
    {
        $log = DailyLog::firstOrNew([
            'challenge_run_id' => $run->id,
            'user_id' => $user->id,
            'day_number' => $dayNumber,
        ]);

        $wasRecentlyCreated = ! $log->exists;

        $hours = $summary->totalHours();
        $projects = $this->transformProjects($summary->projects, $hideProjectNames);
        $languages = $this->transformLanguages($summary->languages);

        $log->date = Carbon::parse($summary->date, $timezone)->toDateString();
        $log->wakatime_summary = array_replace($summary->toArray(), [
            'projects' => $projects,
            'languages' => $languages,
        ]);
        $log->wakatime_synced_at = Carbon::now();
        $log->completed = true;

        if ($log->hours_coded === null || $log->hours_coded == 0.0) {
            $log->hours_coded = $hours;
        }

        $log->save();

        if ($wasRecentlyCreated || ! $log->summary_md) {
            $log->queueAiGeneration();
        }
    }

    protected function transformProjects(array $projects, bool $hideProjectNames): array
    {
        return collect($projects)
            ->map(function (array $project, int $index) use ($hideProjectNames) {
                $name = Arr::get($project, 'name', 'Unknown project');

                if ($hideProjectNames) {
                    $name = 'Hidden Project #'.($index + 1);
                }

                $seconds = (int) Arr::get($project, 'total_seconds', 0);

                return [
                    'name' => $name,
                    'total_seconds' => $seconds,
                    'total_hours' => round($seconds / 3600, 2),
                ];
            })
            ->values()
            ->all();
    }

    protected function transformLanguages(array $languages): array
    {
        return collect($languages)
            ->map(function (array $language) {
                $seconds = (int) Arr::get($language, 'total_seconds', 0);

                return [
                    'name' => Arr::get($language, 'name', 'Unknown'),
                    'total_seconds' => $seconds,
                    'total_hours' => round($seconds / 3600, 2),
                ];
            })
            ->values()
            ->all();
    }

    protected function rememberSyncMeta($profile, array $attributes): void
    {
        $settings = $profile->wakatime_settings ?? [];
        $settings = array_merge($settings, $attributes);

        if (array_key_exists('last_error', $attributes) && $attributes['last_error'] === null) {
            unset($settings['last_error']);
        }

        $profile->forceFill([
            'wakatime_settings' => $settings,
        ])->save();
    }
}
