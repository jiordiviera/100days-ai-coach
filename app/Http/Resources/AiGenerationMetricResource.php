<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AiGenerationMetric */
class AiGenerationMetricResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $success = (int) $this->success_count;
        $failure = (int) $this->failure_count;
        $totalLatency = (int) $this->total_latency_ms;
        $avgLatency = $success > 0 ? (int) floor($totalLatency / $success) : null;

        return [
            'date' => $this->date?->toDateString(),
            'model' => $this->model,
            'success_count' => $success,
            'failure_count' => $failure,
            'total_latency_ms' => $totalLatency,
            'average_latency_ms' => $avgLatency,
            'total_cost_usd' => (float) $this->total_cost_usd,
            'last_generated_at' => $this->last_generated_at,
            'last_error_at' => $this->last_error_at,
            'last_error_message' => $this->last_error_message,
            'metadata' => $this->metadata,
        ];
    }
}
