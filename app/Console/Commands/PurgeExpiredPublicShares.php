<?php

namespace App\Console\Commands;

use App\Models\DailyLog;
use Illuminate\Console\Command;

class PurgeExpiredPublicShares extends Command
{
    protected $signature = 'daily-logs:purge-public-links {--dry-run : Affiche uniquement le nombre d’entrées concernées}';

    protected $description = 'Révoque les liens publics expirés des journaux #100DaysOfCode.';

    public function handle(): int
    {
        $expiredQuery = DailyLog::query()
            ->whereNotNull('public_token')
            ->whereNotNull('public_token_expires_at')
            ->where('public_token_expires_at', '<=', now());

        $count = $expiredQuery->count();

        if ($this->option('dry-run')) {
            $this->info(sprintf('%d liens publics expirés seraient révoqués.', $count));

            return self::SUCCESS;
        }

        if ($count === 0) {
            $this->info('Aucun lien public expiré à révoquer.');

            return self::SUCCESS;
        }

        $expiredQuery->chunkById(100, function ($logs): void {
            /** @var DailyLog $log */
            foreach ($logs as $log) {
                $log->forceFill([
                    'public_token' => null,
                    'public_token_expires_at' => null,
                    'share_templates' => null,
                ])->save();
            }
        });

        $this->info(sprintf('%d liens publics expirés ont été révoqués.', $count));

        return self::SUCCESS;
    }
}
