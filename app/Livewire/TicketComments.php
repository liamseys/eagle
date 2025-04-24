<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\User;
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
    #[On('ticket-closed')]
    public function render()
    {
        $commentsQuery = $this->ticket
            ->comments()
            ->with(['ticket', 'authorable']);

        if (! auth()->user() instanceof User) {
            $commentsQuery->where('is_public', true);
        }

        return view('livewire.ticket-comments', [
            'comments' => $commentsQuery->get(),
        ]);
    }
}
