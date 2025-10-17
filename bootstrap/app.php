<?php

use App\Console\Commands\PurgeExpiredPublicShares;
use App\Console\Commands\SendDailyLogReminders;
use App\Console\Commands\SendWeeklyDigest;
use App\Console\Commands\SyncWakaTime;
use App\Http\Middleware\EnsureUserIsOnboarded;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\SeoServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Mail\MailServiceProvider;
use Livewire\LivewireServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        AppServiceProvider::class,
        AuthServiceProvider::class,
        SeoServiceProvider::class,
        LivewireServiceProvider::class,
        MailServiceProvider::class,
    ])
    ->withCommands([
        SendDailyLogReminders::class,
        SyncWakaTime::class,
        SendWeeklyDigest::class,
        PurgeExpiredPublicShares::class,
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('wakatime:sync')->dailyAt('01:00');
        $schedule->command('digest:weekly')->dailyAt('06:00');
        $schedule->command('daily-logs:purge-public-links')->dailyAt('03:00');
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', EnsureUserIsOnboarded::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
