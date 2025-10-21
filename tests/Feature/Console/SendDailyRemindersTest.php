<?php

use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\NotificationChannel;
use App\Models\NotificationOutbox;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\DailyReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function createUserWithProfile(array $preferencesOverrides = []): User
{
    $user = User::factory()->create();

    $user->profile()->create([
        'join_reason' => 'self_onboarding',
        'focus_area' => null,
        'preferences' => array_replace_recursive($user->profilePreferencesDefaults(), $preferencesOverrides),
    ]);

    expect(data_get($user->profile->preferences, 'notification_types.daily_reminder'))->toBeTrue();
    expect(data_get($user->profile->preferences, 'channels.email'))->toBeTrue();

    return $user;
}

it('queues and sends reminders to users without daily log', function (): void {
    Notification::fake();
    Http::fake();
    Carbon::setTestNow(Carbon::parse('2024-10-05 19:40', 'UTC'));

    $user = createUserWithProfile([
        'timezone' => 'Africa/Douala',
        'reminder_time' => '20:30',
    ]);

    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'title' => 'Reminders FTW',
        'status' => 'active',
        'start_date' => Carbon::parse('2024-09-29'),
    ]);

    $timezone = data_get($user->profile->preferences, 'timezone');
    $reminderTime = data_get($user->profile->preferences, 'reminder_time');
    $localNow = now()->copy()->setTimezone($timezone);
    $reminderDateTime = Carbon::parse($reminderTime, $timezone)->setDate($localNow->year, $localNow->month, $localNow->day);

    expect($localNow->lessThan($reminderDateTime))->toBeFalse();
    $command = app(App\Console\Commands\SendDailyReminders::class);
    $reflector = new ReflectionClass($command);
    $resolveRun = $reflector->getMethod('resolveActiveRun');
    $resolveRun->setAccessible(true);
    expect($resolveRun->invoke($command, $user))->not()->toBeNull();

    app(App\Console\Commands\SendDailyReminders::class)->handle();

    $entries = NotificationOutbox::where('user_id', $user->id)->where('type', 'daily_reminder')->get();
    expect($entries)->toHaveCount(1);
    expect($entries->first()->status)->toBe('sent');

    Notification::assertSentTo($user, function (DailyReminderNotification $notification, array $channels): bool {
        return $channels === ['mail'];
    });

    // Running again the same day should not duplicate reminders
    app(App\Console\Commands\SendDailyReminders::class)->handle();
    expect(NotificationOutbox::where('user_id', $user->id)->where('type', 'daily_reminder')->count())->toBe(1);
});

it('skips reminders when a log already exists for the day', function (): void {
    Notification::fake();
    Http::fake();
    Carbon::setTestNow(Carbon::parse('2024-10-05 19:40', 'UTC'));

    $user = createUserWithProfile([
        'timezone' => 'Africa/Douala',
        'reminder_time' => '20:30',
    ]);

    $run = ChallengeRun::factory()->for($user, 'owner')->create([
        'title' => 'Reminders FTW',
        'status' => 'active',
        'start_date' => Carbon::parse('2024-09-29'),
    ]);

    DailyLog::factory()->create([
        'challenge_run_id' => $run->id,
        'user_id' => $user->id,
        'day_number' => 7,
        'date' => Carbon::parse('2024-10-05')->toDateString(),
    ]);

    app(App\Console\Commands\SendDailyReminders::class)->handle();

    Notification::assertNothingSent();
    $entry = NotificationOutbox::where('user_id', $user->id)->where('type', 'daily_reminder')->first();
    expect($entry)->not()->toBeNull();
    expect($entry->status)->toBe('skipped');
});

it('sends reminders across active telegram channel when configured', function (): void {
    Notification::fake();
    Http::fake();
    Carbon::setTestNow(Carbon::parse('2024-10-05 19:40', 'UTC'));

    $user = createUserWithProfile([
        'timezone' => 'Africa/Douala',
        'reminder_time' => '20:30',
        'channels' => [
            'email' => true,
            'telegram' => true,
        ],
    ]);

    NotificationChannel::factory()
        ->for($user, 'notifiable')
        ->create([
            'channel' => 'telegram',
            'value' => '123456789',
        ]);

    ChallengeRun::factory()->for($user, 'owner')->create([
        'title' => 'Telegram reminders',
        'status' => 'active',
        'start_date' => Carbon::parse('2024-09-29'),
    ]);

    app(App\Console\Commands\SendDailyReminders::class)->handle();

    $entries = NotificationOutbox::where('user_id', $user->id)
        ->where('type', 'daily_reminder')
        ->get();

    expect($entries)->toHaveCount(2);
    expect($entries->pluck('channel')->all())
        ->toMatchArray(['mail', 'telegram']);

    Notification::assertSentTo($user, function (DailyReminderNotification $notification, array $channels): bool {
        return in_array('mail', $channels, true);
    });

    Notification::assertSentTo($user, function (DailyReminderNotification $notification, array $channels): bool {
        return in_array(TelegramChannel::class, $channels, true);
    });
});
