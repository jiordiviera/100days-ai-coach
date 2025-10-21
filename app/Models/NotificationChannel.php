<?php

namespace App\Models;

use Database\Factories\NotificationChannelFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationChannel extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'channel',
        'value',
        'language',
        'is_active',
        'metadata',
        'last_sent_at',
        'last_failed_at',
        'failure_reason',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
        'last_failed_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): NotificationChannelFactory
    {
        return NotificationChannelFactory::new();
    }
}
