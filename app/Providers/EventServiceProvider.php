<?php

namespace App\Providers;

use App\Events\DailyLogAiFailed;
use App\Events\DailyLogAiGenerated;
use App\Events\SupportTicketCreated;
use App\Listeners\NotifyUserOfAiFailure;
use App\Listeners\QueueSupportTicketIssue;
use App\Listeners\RecordDailyLogAiMetrics;
use App\Listeners\SendSupportTicketNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DailyLogAiGenerated::class => [],
        DailyLogAiFailed::class => [
            NotifyUserOfAiFailure::class,
        ],
        SupportTicketCreated::class => [
            SendSupportTicketNotification::class,
            QueueSupportTicketIssue::class,
        ],
    ];

    protected $subscribe = [
        RecordDailyLogAiMetrics::class,
    ];
}
