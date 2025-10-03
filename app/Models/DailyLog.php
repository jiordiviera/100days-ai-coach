<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    use HasUlids;

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
    ];

    protected $casts = [
        'date' => 'date',
        'hours_coded' => 'decimal:2',
        'projects_worked_on' => 'array',
        'completed' => 'boolean',
    ];

    public function challengeRun(): BelongsTo
    {
        return $this->belongsTo(ChallengeRun::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
