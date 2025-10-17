<?php

use App\Events\DailyLogAiFailed;
use App\Listeners\NotifyUserOfAiFailure;
use App\Models\DailyLog;
use App\Notifications\DailyLogAiFailedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('notifies the user when AI generation fails', function () {
    Notification::fake();

    $log = DailyLog::factory()->create();

    $listener = new NotifyUserOfAiFailure();
    $listener->handle(new DailyLogAiFailed($log->id, 'Service unavailable'));

    Notification::assertSentTo(
        [$log->user],
        DailyLogAiFailedNotification::class,
        function (DailyLogAiFailedNotification $notification, array $channels) use ($log) {
            expect($channels)->toContain('mail');

            $mail = $notification->toMail($log->user);

            return collect($mail->introLines)->contains(fn (string $line) => str_contains($line, 'Service unavailable'));
        }
    );
});
