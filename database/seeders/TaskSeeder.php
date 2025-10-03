<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::create([
            'title' => 'Complete Laravel Project',
            'description' => 'Finish the task manager application using Laravel.',
            'is_completed' => true,
            'user_id' => 1,
        ]);
        Task::create([
            'title' => 'Write Unit Tests',
            'description' => 'Ensure all functionalities are covered with unit tests.',
            'is_completed' => false,
            'user_id' => 1,
        ]);
        Task::create([
            'title' => 'Deploy Application',
            'description' => 'Deploy the application to the production server.',
            'is_completed' => false,
            'user_id' => 1,
        ]);
    }
}
