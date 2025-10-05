<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasUlids;

    /**
     * Reference structure for the JSON preferences payload.
     */
    public const PREFERENCE_SCHEMA = [
        'language' => 'en|fr',
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
        'ai_provider' => 'groq|openai|local',
        'tone' => 'neutral|fun',
    ];

    protected $fillable = [
        'user_id',
        'join_reason',
        'focus_area',
        'username',
        'github_id',
        'github_username',
        'preferences',
        'wakatime_api_key',
        'wakatime_settings',
    ];

    protected $casts = [
        'preferences' => 'array',
        'social_links' => 'array',
        'wakatime_api_key' => 'encrypted',
        'wakatime_settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
