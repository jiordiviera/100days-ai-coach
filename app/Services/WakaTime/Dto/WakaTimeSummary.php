<?php

namespace App\Services\WakaTime\Dto;

use Illuminate\Support\Carbon;

class WakaTimeSummary
{
    public function __construct(
        public readonly string $date,
        public readonly string $timezone,
        public readonly int $totalSeconds,
        public readonly array $grandTotal,
        public readonly array $projects,
        public readonly array $languages,
        public readonly array $raw = [],
    ) {
    }

    public function totalHours(): float
    {
        return round($this->totalSeconds / 3600, 2);
    }

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'timezone' => $this->timezone,
            'total_seconds' => $this->totalSeconds,
            'total_hours' => $this->totalHours(),
            'grand_total' => $this->grandTotal,
            'projects' => $this->projects,
            'languages' => $this->languages,
            'fetched_at' => Carbon::now()->toIso8601String(),
            'raw' => $this->raw,
        ];
    }
}
