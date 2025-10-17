<?php

use App\Models\AiGenerationMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('denies access to non-admin users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('metrics.ai'));

    $response->assertForbidden();
});

it('returns aggregated metrics for admins', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    AiGenerationMetric::create([
        'date' => Carbon::today()->toDateString(),
        'model' => 'gpt-4o-mini',
        'success_count' => 3,
        'failure_count' => 1,
        'total_latency_ms' => 900,
        'total_cost_usd' => 0.123,
        'last_generated_at' => Carbon::now()->subMinute(),
    ]);

    $response = $this->actingAs($admin)->getJson(route('metrics.ai'));

    $response->assertOk()
        ->assertJsonFragment([
            'model' => 'gpt-4o-mini',
            'success_count' => 3,
            'failure_count' => 1,
            'total_latency_ms' => 900,
            'total_cost_usd' => 0.123,
        ]);
});
