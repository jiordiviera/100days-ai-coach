<?php

namespace Database\Factories;

use App\Models\ChallengeRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChallengeRun>
 */
class ChallengeRunFactory extends Factory
{
    protected $model = ChallengeRun::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'target_days' => 100,
            'status' => 'active',
            'is_public' => $this->faker->boolean(20),
            'public_join_code' => Str::upper(Str::random(8)),
        ];
    }
}
