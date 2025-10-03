<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChallengeRun extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'start_date',
        'target_days',
        'status',
        'is_public',
        'public_join_code',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (ChallengeRun $run): void {
            if (! $run->owner_id) {
                return;
            }

            $run->participantLinks()->firstOrCreate(
                ['user_id' => $run->owner_id],
                ['joined_at' => now()]
            );
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'challenge_participants')
            ->withPivot(['joined_at'])
            ->withTimestamps();
    }

    public function participantLinks(): HasMany
    {
        return $this->hasMany(ChallengeParticipant::class, 'challenge_run_id');
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
