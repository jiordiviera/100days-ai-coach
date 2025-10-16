<?php

namespace App\Events;

use App\Models\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public SupportTicket $ticket) {}
}
