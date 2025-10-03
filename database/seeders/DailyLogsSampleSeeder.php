<?php

namespace Database\Seeders;

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DailyLogsSampleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'AI Coach Pilot',
            'email' => 'coach@example.com',
        ]);

        $run = ChallengeRun::factory()->for($user, 'owner')->create([
            'title' => '100DaysOfCode Pilot',
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(4),
        ]);

        DailyLog::factory()
            ->count(5)
            ->sequence(
                ['day_number' => 1, 'date' => Carbon::now()->subDays(4)],
                ['day_number' => 2, 'date' => Carbon::now()->subDays(3)],
                ['day_number' => 3, 'date' => Carbon::now()->subDays(2)],
                ['day_number' => 4, 'date' => Carbon::now()->subDay()],
                ['day_number' => 5, 'date' => Carbon::now()]
            )
            ->for($run, 'challengeRun')
            ->for($user, 'user')
            ->create();
    }
}
