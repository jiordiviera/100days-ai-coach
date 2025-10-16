<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $project = Project::first() ?? Project::create([
            'name' => 'Projet de démonstration',
            'description' => 'Projet créé automatiquement par le seeder des tâches.',
            'user_id' => $user->id,
        ]);

        $tasks = [
            [
                'title' => 'Complete Laravel Project',
                'description' => 'Finish the task manager application using Laravel.',
                'is_completed' => true,
            ],
            [
                'title' => 'Write Unit Tests',
                'description' => 'Ensure all functionalities are covered with unit tests.',
            ],
            [
                'title' => 'Deploy Application',
                'description' => 'Deploy the application to the production server.',
            ],
        ];

        foreach ($tasks as $attributes) {
            Task::create($attributes + [
                'user_id' => $user->id,
                'project_id' => $project->id,
            ]);
        }
    }
}
