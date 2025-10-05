<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profilePreferencesDefaults(): array
    {
        return [
            'language' => 'en',
            'timezone' => 'Africa/Douala',
            'reminder_time' => '20:30',
            'channels' => [
                'email' => true,
                'slack' => false,
                'push' => false,
            ],
            'notification_types' => [
                'daily_reminder' => true,
                'weekly_digest' => true,
            ],
            'ai_provider' => 'groq',
            'tone' => 'neutral',
            'wakatime' => [
                'hide_project_names' => true,
            ],
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Projets créés par l'utilisateur
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // Projets où l'utilisateur est membre
    public function memberProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')->withTimestamps();
    }

    // Challenge runs créés (owner)
    public function challengeRunsOwned(): HasMany
    {
        return $this->hasMany(ChallengeRun::class, 'owner_id');
    }

    // Challenge runs auxquels l'utilisateur participe
    public function challengeRuns(): BelongsToMany
    {
        return $this->belongsToMany(ChallengeRun::class, 'challenge_participants')
            ->withPivot(['joined_at'])
            ->withTimestamps();
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class);
    }

    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function repositories(): HasMany
    {
        return $this->hasMany(UserRepository::class);
    }

    public function needsOnboarding(): bool
    {
        return ! $this->profile()->exists();
    }
}
