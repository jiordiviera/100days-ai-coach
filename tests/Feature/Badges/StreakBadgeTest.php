<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use App\Support\BadgeEvaluator;
use App\Services\Badges\StreakPunchlineGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('awards the streak_7 badge with an AI punchline meta', function (): void {
    app()->instance(StreakPunchlineGenerator::class, new class
    {
        public function generate(User $user, ChallengeRun $run): array
        {
            return [
                'text' => 'Incredible seven-day shipping! Keep compounding wins.',
                'ai_model' => 'ai.fake-driver.v1',
                'ai_latency_ms' => 250,
                'ai_cost_usd' => 0.001,
            ];
        }
    });

    $user = User::factory()->create();
    $user->profile()->create([
        'join_reason' => 'self_onboarding',
        'focus_area' => null,
        'preferences' => $user->profilePreferencesDefaults(),
    ]);

    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'title' => 'Streak Quest',
        'status' => 'active',
    ]);

    Carbon::setTestNow(Carbon::parse('2024-10-07')); // ensure consistent dates

    foreach (range(1, 7) as $day) {
        DailyLog::factory()->create([
            'challenge_run_id' => $run->id,
            'user_id' => $user->id,
            'day_number' => $day,
            'date' => Carbon::today()->copy()->subDays(7 - $day)->toDateString(),
            'notes' => 'Progress of day '.$day,
        ]);
    }

    $evaluator = new BadgeEvaluator();

    $result = $evaluator->evaluate($user->load('badges'), $run, [
        'badges' => [[
            'id' => 'streak_7',
            'label' => 'Semaine en feu',
            'color' => 'success',
        ]],
    ]);

    $badge = $user->badges()->where('badge_key', 'streak_7')->first();

    expect($badge)->not()->toBeNull();
    expect($badge->meta['punchline'] ?? null)->toBe('Incredible seven-day shipping! Keep compounding wins.');
    expect($badge->meta['ai']['model'] ?? null)->toBe('ai.fake-driver.v1');
    expect($result['newly_awarded'])->toHaveCount(1);
});
