<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AiGenerationMetric extends Model
{
    use HasUlids;

    protected $fillable = [
        'date',
        'model',
        'success_count',
        'failure_count',
        'total_latency_ms',
        'total_cost_usd',
        'last_generated_at',
        'last_error_at',
        'last_error_message',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'total_cost_usd' => 'decimal:3',
        'metadata' => 'array',
        'last_generated_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];
}
