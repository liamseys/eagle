<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requester' => $this->requester,
            'assignee' => $this->assignee,
            'group' => $this->group,
            'ticket_id' => $this->ticket_id,
            'duplicate_of_ticket_id' => $this->duplicate_of_ticket_id,
            'subject' => $this->subject,
            'priority' => $this->priority,
            'type' => $this->type,
            'status' => $this->status,
            'is_escalated' => $this->is_escalated,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
