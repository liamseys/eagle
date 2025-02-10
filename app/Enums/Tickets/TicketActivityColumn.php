<?php

namespace App\Enums\Tickets;

enum TicketActivityColumn: string
{
    case PRIORITY = 'priority';
    case TYPE = 'type';
    case STATUS = 'status';
}
