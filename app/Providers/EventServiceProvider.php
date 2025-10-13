<?php

namespace App\Providers;

use App\Events\SupportTicketCreated;
use App\Listeners\QueueSupportTicketIssue;
use App\Listeners\SendSupportTicketNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SupportTicketCreated::class => [
            SendSupportTicketNotification::class,
            QueueSupportTicketIssue::class,
        ],
    ];
}
