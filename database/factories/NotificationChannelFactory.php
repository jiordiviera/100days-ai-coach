<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationChannel>
 */
class NotificationChannelFactory extends Factory
{
    protected $model = NotificationChannel::class;

    public function definition(): array
    {
        return [
            'channel' => 'telegram',
            'value' => (string) $this->faker->numerify('#########'),
            'language' => $this->faker->randomElement(['en', 'fr']),
            'is_active' => true,
            'metadata' => null,
            'last_sent_at' => null,
            'last_failed_at' => null,
            'failure_reason' => null,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
