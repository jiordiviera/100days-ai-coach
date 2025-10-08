<?php

namespace App\Models;

use App\Jobs\GenerateDailyLogInsights;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DailyLog extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * Dispatch the AI insights generation job for this log.
     */
    public function queueAiGeneration(bool $force = false): void
    {
        GenerateDailyLogInsights::dispatch($this->id, $force);
    }

    protected $fillable = [
        'challenge_run_id',
        'user_id',
        'day_number',
        'date',
        'hours_coded',
        'projects_worked_on',
        'notes',
        'learnings',
        'challenges_faced',
        'completed',
        'retro',
        'summary_md',
        'tags',
        'coach_tip',
        'share_draft',
        'share_templates',
        'ai_model',
        'ai_latency_ms',
        'ai_cost_usd',
        'public_token',
        'wakatime_summary',
        'wakatime_synced_at',
    ];

    protected $casts = [
        'date' => 'date',
        'hours_coded' => 'decimal:2',
        'projects_worked_on' => 'array',
        'tags' => 'array',
        'share_templates' => 'array',
        'ai_cost_usd' => 'decimal:3',
        'ai_latency_ms' => 'integer',
        'completed' => 'boolean',
        'retro' => 'boolean',
        'wakatime_summary' => 'array',
        'wakatime_synced_at' => 'datetime',
    ];

    public function challengeRun(): BelongsTo
    {
        return $this->belongsTo(ChallengeRun::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ensure a shareable ULID token is present on the log.
     */
    public function ensurePublicToken(): string
    {
        if (! $this->public_token) {
            $this->public_token = (string) Str::ulid();

            if ($this->exists) {
                $this->save();
            }
        }

        return $this->public_token;
    }
}
