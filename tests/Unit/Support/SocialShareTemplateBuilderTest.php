<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\User;
use App\Support\SocialShareTemplateBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds social templates with custom hashtags and highlights', function (): void {
    $user = User::factory()->create();

    $preferences = $user->profilePreferencesDefaults();
    $preferences['social']['share_hashtags'] = ['#Ship30', ' buildInPublic '];

    $profile = $user->profile()->create([
        'preferences' => $preferences,
    ]);

    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'target_days' => 100,
    ]);

    $log = DailyLog::factory()
        ->for($run, 'challengeRun')
        ->for($user, 'user')
        ->create([
            'day_number' => 5,
            'hours_coded' => 2.5,
            'tags' => ['Refactor API', 'Wrote tests'],
            'notes' => "Refactored legacy controller\nAdded feature specs",
            'share_draft' => null,
            'share_templates' => null,
        ]);

    $builder = app(SocialShareTemplateBuilder::class);

    $templates = $builder->build($log, [
        'summary' => 'Day 5 recap: refactored the API and tightened tests.',
        'tags' => ['Refactor API', 'Wrote tests'],
        'share_draft' => 'Fallback draft content',
    ]);

    expect($templates)->toHaveKeys(['linkedin', 'x'])
        ->and($templates['linkedin'])->toContain('Jour 5/100')
        ->and($templates['linkedin'])->toContain('Points forts')
        ->and($templates['linkedin'])->toContain('#Ship30')
        ->and($templates['linkedin'])->toContain('#buildInPublic')
        ->and($templates['x'])->toContain('#Ship30')
        ->and($templates['x'])->toContain('#buildInPublic');
});
