<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'challenge_run_id',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'project_user',
        )->withTimestamps();
    }

    public function challengeRun(): BelongsTo
    {
        return $this->belongsTo(ChallengeRun::class);
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $scope) use ($user) {
            $scope->where('user_id', $user->id)
                ->orWhereHas('members', fn (Builder $memberQuery) => $memberQuery->where('users.id', $user->id))
                ->orWhere(function (Builder $runQuery) use ($user) {
                    $runQuery
                        ->whereHas('challengeRun', fn (Builder $challengeQuery) => $challengeQuery
                            ->where('owner_id', $user->id)
                            ->orWhereHas('participantLinks', fn (Builder $participantQuery) => $participantQuery->where('user_id', $user->id)));
                });
        });
    }
}
