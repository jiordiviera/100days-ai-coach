<?php

namespace App\Console\Commands;

use App\Jobs\SyncWakaTimeForUser;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncWakaTime extends Command
{
    protected $signature = 'wakatime:sync {--date= : Sync for a specific YYYY-MM-DD date in user timezone}';

    protected $description = 'Synchronise les activités WakaTime pour tous les utilisateurs configurés.';

    public function handle(): int
    {
        $date = $this->option('date');
        if ($date) {
            try {
                Carbon::parse($date);
            } catch (\Throwable) {
                $this->error('Date invalide. Utilisez le format YYYY-MM-DD.');

                return self::FAILURE;
            }
        }

        $count = 0;
        $queuedMode = (bool) $date;

        UserProfile::query()
            ->whereNotNull('wakatime_api_key')
            ->chunkById(100, function ($profiles) use (&$count, $date): void {
                foreach ($profiles as $profile) {
                    if ($date) {
                        SyncWakaTimeForUser::dispatch($profile->user_id, $date);
                    } else {
                        SyncWakaTimeForUser::dispatchSync($profile->user_id);
                    }

                    $count++;
                }
            });

        $message = $queuedMode
            ? "{$count} synchronisations WakaTime en file d'attente."
            : "{$count} synchronisations WakaTime exécutées.";

        $this->info($message);

        return self::SUCCESS;
    }
}
