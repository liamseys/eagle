<?php

namespace App\Livewire;

use App\Models\Ticket;
use Livewire\Attributes\On;
use Livewire\Component;

class TicketComments extends Component
{
    public Ticket $ticket;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    #[On('comment-created')]
    public function render()
    {
        $comments = $this->ticket
            ->comments()
            ->with(['ticket', 'authorable'])
            ->get();

        return view('livewire.ticket-comments', [
            'comments' => $comments,
        ]);
    }
}
