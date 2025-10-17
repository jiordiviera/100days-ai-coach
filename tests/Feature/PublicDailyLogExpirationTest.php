<?php

use App\Models\DailyLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('returns 404 when the public link has expired', function () {
    $log = DailyLog::factory()->public()->create([
        'public_token_expires_at' => Carbon::now()->subDay(),
    ]);

    $response = $this->get(route('logs.share', ['token' => $log->public_token]));

    $response->assertNotFound();
});

it('shows the page when the public link is still valid', function () {
    $log = DailyLog::factory()->public()->create([
        'public_token_expires_at' => Carbon::now()->addDay(),
    ]);

    $response = $this->get(route('logs.share', ['token' => $log->public_token]));

    $response->assertOk();
});
