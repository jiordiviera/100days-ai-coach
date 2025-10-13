<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    public function definition(): array
    {
        $categories = ['bug', 'idea', 'question'];

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'category' => $this->faker->randomElement($categories),
            'message' => $this->faker->paragraph(),
            'status' => 'open',
            'github_issue_url' => null,
            'resolved_at' => null,
        ];
    }
}
