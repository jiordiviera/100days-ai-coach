<?php

namespace Database\Factories;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyLog>
 */
class DailyLogFactory extends Factory
{
    protected $model = DailyLog::class;

    public function definition(): array
    {
        $tagsPool = ['laravel', 'php', 'ai', 'livewire', 'filament'];

        return [
            'challenge_run_id' => ChallengeRun::factory(),
            'user_id' => User::factory(),
            'day_number' => $this->faker->numberBetween(1, 100),
            'date' => $this->faker->dateTimeBetween('-10 days', 'now'),
            'hours_coded' => $this->faker->randomFloat(2, 0, 8),
            'projects_worked_on' => [$this->faker->slug()],
            'notes' => $this->faker->paragraph(),
            'learnings' => $this->faker->sentence(),
            'challenges_faced' => $this->faker->sentence(),
            'completed' => true,
            'summary_md' => '### Summary\n- '.$this->faker->sentence(),
            'tags' => $this->faker->randomElements($tagsPool, $this->faker->numberBetween(1, 3)),
            'coach_tip' => $this->faker->sentences(2, true),
            'share_draft' => $this->faker->paragraph(),
            'ai_model' => 'gpt-4o-mini',
            'ai_latency_ms' => $this->faker->numberBetween(120, 900),
            'ai_cost_usd' => $this->faker->randomFloat(3, 0, 5),
            'public_token' => null,
            'wakatime_summary' => null,
            'wakatime_synced_at' => null,
        ];
    }
}
