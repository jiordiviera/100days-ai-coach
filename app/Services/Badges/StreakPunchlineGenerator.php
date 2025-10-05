<?php

namespace App\Services\Badges;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use App\Services\Ai\AiManager;
use Illuminate\Support\Str;
use Throwable;

readonly class StreakPunchlineGenerator
{
    public function __construct(private AiManager $aiManager) {}

    public function generate(User $user, ChallengeRun $run): array
    {
        $logs = DailyLog::query()
            ->where('challenge_run_id', $run->id)
            ->where('user_id', $user->id)
            ->orderByDesc('day_number')
            ->limit(7)
            ->get()
            ->reverse();

        if ($logs->isEmpty()) {
            return $this->fallback();
        }

        $notes = $logs->map(function (DailyLog $log) {
            $snippet = Str::of($log->notes ?: $log->learnings ?: 'Progression notée')
                ->stripTags()
                ->squish()
                ->limit(160);

            return sprintf('Day %d: %s', $log->day_number, $snippet);
        })->implode("\n");

        $learnings = $logs->pluck('learnings')->filter()->map(function ($text) {
            return Str::of($text)->stripTags()->squish()->limit(120);
        })->implode("\n");

        $projects = $logs->flatMap(fn (DailyLog $log) => $log->projects_worked_on ?? [])->unique()->values()->all();

        $synthetic = new DailyLog([
            'day_number' => (int) $logs->last()->day_number,
            'notes' => $notes,
            'learnings' => $learnings,
            'projects_worked_on' => $projects,
            'hours_coded' => round($logs->avg('hours_coded') ?? 0, 2),
        ]);

        $synthetic->setRelation('challengeRun', $run);
        $synthetic->setRelation('user', $user);

        try {
            $result = $this->aiManager->generateInsights($synthetic);

            $punchline = $result->coachTip ?: $result->summary;

            if (! $punchline) {
                return $this->fallback();
            }

            return [
                'text' => $punchline,
                'ai_model' => $result->model,
                'ai_latency_ms' => $result->latencyMs,
                'ai_cost_usd' => $result->costUsd,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return $this->fallback();
        }
    }

    protected function fallback(): array
    {
        return [
            'text' => 'Seven days straight! Your consistency is turning momentum into mastery.',
            'ai_model' => null,
            'ai_latency_ms' => null,
            'ai_cost_usd' => 0.0,
        ];
    }
}
