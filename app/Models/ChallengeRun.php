<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
        'public_slug',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ChallengeRun $run): void {
            if (blank($run->public_slug)) {
                $run->public_slug = static::generateUniqueSlug($run->title);
            }
        });

        static::created(function (ChallengeRun $run): void {
            if (! $run->owner_id) {
                return;
            }

            $run->participantLinks()->firstOrCreate(
                ['user_id' => $run->owner_id],
                ['joined_at' => now()]
            );
        });

        static::saved(function (ChallengeRun $run): void {
            $originalSlug = $run->getOriginal('public_slug');

            if ($originalSlug && $originalSlug !== $run->public_slug) {
                Cache::forget('public-challenge:'.$originalSlug);
            }

            if ($run->public_slug) {
                Cache::forget('public-challenge:'.$run->public_slug);
            }
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

    protected static function generateUniqueSlug(?string $title): string
    {
        $base = \Illuminate\Support\Str::of($title ?? 'challenge')
            ->slug('-')
            ->limit(48, '')
            ->trim('-')
            ->value() ?: 'challenge';

        do {
            $slug = sprintf('%s-%s', $base, \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6)));
        } while (static::where('public_slug', $slug)->exists());

        return $slug;
    }

    public function ensurePublicSlug(): string
    {
        if (! $this->public_slug) {
            $this->public_slug = static::generateUniqueSlug($this->title);
        }

        return $this->public_slug;
    }
}
