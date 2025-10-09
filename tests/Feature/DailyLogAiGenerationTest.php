<?php

use App\Jobs\GenerateDailyLogInsights;
use App\Livewire\Page\DailyChallenge;
use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use App\Services\Ai\AiManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['ai.default' => 'fake']);
    Cache::flush();
});

it('dispatches the AI generation job when saving a daily log', function (): void {
    $this->travelTo(Carbon::parse('2024-10-05 09:00:00'));

    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'status' => 'active',
        'start_date' => now()->subDay(),
    ]);

    $this->actingAs($user);

    Bus::fake();

    Livewire::test(DailyChallenge::class)
        ->set('dailyForm.description', str_repeat('Progress logged today. ', 1))
        ->call('saveEntry');

    $log = DailyLog::first();

    expect($log)->not()->toBeNull();

    Bus::assertDispatched(GenerateDailyLogInsights::class, function (GenerateDailyLogInsights $job) use ($log) {
        return $job->dailyLogId === $log->id && $job->force === false;
    });
});

it('persists AI insights when the job is handled', function (): void {
    $this->travelTo(Carbon::parse('2024-10-05 09:00:00'));

    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'status' => 'active',
        'start_date' => now()->subDays(2),
    ]);

    $log = DailyLog::factory()->for($run, 'challengeRun')->for($user, 'user')->create([
        'day_number' => 3,
        'notes' => 'Refactored the AI pipeline and wrote comprehensive tests.',
    ]);

    $job = new GenerateDailyLogInsights($log->id);
    $job->handle(app(AiManager::class));

    $log->refresh();

    expect($log->summary_md)->toStartWith('Day 3 recap')
        ->and($log->coach_tip)->not()->toBeEmpty()
        ->and($log->share_templates)->toHaveKeys(['linkedin', 'x'])
        ->and($log->share_draft)->toStartWith('Jour ')
        ->and($log->share_templates['linkedin'])->toContain('Points forts')
        ->and($log->share_templates['x'])->toContain('#100DaysOfCode')
        ->and($log->ai_model)->toBe('ai.fake-driver.v1')
        ->and($log->ai_latency_ms)->toBeGreaterThan(0)
        ->and((float) $log->ai_cost_usd)->toBeGreaterThanOrEqual(0.0);
});

it('throttles AI regeneration to once per day by default', function (): void {
    $this->travelTo(Carbon::parse('2024-10-05 08:00:00'));

    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'status' => 'active',
        'start_date' => now()->subDays(2),
    ]);

    $log = DailyLog::factory()->for($run, 'challengeRun')->for($user, 'user')->create([
        'day_number' => 3,
        'notes' => 'Worked on caching strategy for AI summaries.',
    ]);

    $manager = app(AiManager::class);

    $firstJob = new GenerateDailyLogInsights($log->id);
    $firstJob->handle($manager);

    $log->refresh();
    $log->forceFill(['summary_md' => 'manual override'])->save();

    $secondJob = new GenerateDailyLogInsights($log->id);
    $secondJob->handle($manager);

    $log->refresh();

    expect($log->summary_md)->toBe('manual override');
});

it('allows manual regeneration when the throttle is clear', function (): void {
    $this->travelTo(Carbon::parse('2024-10-05 08:30:00'));

    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'status' => 'active',
        'start_date' => now()->subDays(2),
    ]);

    DailyLog::factory()->for($run, 'challengeRun')->for($user, 'user')->create([
        'day_number' => 3,
        'date' => now()->toDateString(),
        'notes' => 'Shipping features and writing documentation.',
    ]);

    $this->actingAs($user);

    Bus::fake();

    Livewire::test(DailyChallenge::class)
        ->call('regenerateAi');

    Bus::assertDispatched(GenerateDailyLogInsights::class, 1);
});

it('prevents manual regeneration when throttled', function (): void {
    $this->travelTo(Carbon::parse('2024-10-05 08:30:00'));

    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'status' => 'active',
        'start_date' => now()->subDays(2),
    ]);

    $log = DailyLog::factory()->for($run, 'challengeRun')->for($user, 'user')->create([
        'day_number' => 3,
        'date' => now()->toDateString(),
        'notes' => 'Analyzing AI latency metrics.',
    ]);

    $job = new GenerateDailyLogInsights($log->id);
    $job->handle(app(AiManager::class));

    $this->actingAs($user);

    Bus::fake();

    Livewire::test(DailyChallenge::class)
        ->call('regenerateAi');

    Bus::assertNotDispatched(GenerateDailyLogInsights::class);
});

it('polls the AI panel until insights are ready', function (): void {
    $this->travelTo(Carbon::parse('2024-10-05 09:00:00'));

    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'status' => 'active',
        'start_date' => now()->toDateString(),
    ]);

    $log = DailyLog::factory()->for($run, 'challengeRun')->for($user, 'user')->create([
        'day_number' => 1,
        'summary_md' => null,
        'tags' => null,
        'coach_tip' => null,
        'share_draft' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(DailyChallenge::class);

    $component->assertSet('shouldPollAi', true)
        ->assertSet('aiPanel.status', 'pending');

    $log->forceFill([
        'summary_md' => '## Ready summary',
        'tags' => ['ready'],
        'coach_tip' => 'Ship it!',
        'share_draft' => 'Jour 1/100 — LinkedIn template',
        'share_templates' => [
            'linkedin' => 'Jour 1/100 — LinkedIn template',
            'x' => 'Day 1/100: quick update',
        ],
    ])->save();

    $component->call('pollAiPanel')
        ->assertSet('shouldPollAi', false)
        ->assertSet('aiPanel.status', 'ready')
        ->assertSet('aiPanel.summary', '## Ready summary')
        ->assertSet('aiPanel.share_templates.linkedin', 'Jour 1/100 — LinkedIn template')
        ->assertSee('Copier LinkedIn', false)
        ->assertSee('Copier X', false);
});

it('falls back to offline insights when the AI provider fails', function (): void {
    $this->travelTo(Carbon::parse('2024-10-05 10:00:00'));

    $user = User::factory()->create();
    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'status' => 'active',
        'start_date' => now()->subDays(3),
    ]);

    $log = DailyLog::factory()->for($run, 'challengeRun')->for($user, 'user')->create([
        'day_number' => 4,
        'notes' => 'Adapted the UI to support social share drafts.',
        'tags' => ['ui', 'social-share'],
    ]);

    $mock = \Mockery::mock(AiManager::class);
    $mock->shouldReceive('generateInsights')
        ->once()
        ->andThrow(new \RuntimeException('Groq unavailable'));

    $this->swap(AiManager::class, $mock);

    $job = new GenerateDailyLogInsights($log->id);
    $job->handle(app(AiManager::class));

    $log->refresh();

    expect($log->summary_md)->toContain('### Jour')
        ->and($log->ai_model)->toBe('ai.fallback.offline')
        ->and($log->share_templates)->toHaveKey('linkedin')
        ->and($log->share_templates['linkedin'])->toContain('Jour')
        ->and($log->share_templates['x'])->toContain('#100DaysOfCode');
});
