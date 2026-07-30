<?php

namespace App\Notifications;

use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketClosed extends Notification
{
    use Queueable;

    public Ticket $ticket;

    public ?TicketStatus $previousStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, ?TicketStatus $previousStatus = null)
    {
        $this->ticket = $ticket;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $closedLine = $this->previousStatus === TicketStatus::SOLVED
            ? __('Your ticket was marked as solved and has now been automatically closed.')
            : __('We noticed that there was no response regarding your ticket, so it has been automatically closed.');

        return (new MailMessage)
            ->replyTo($this->ticket->getSupportEmailWithTicketId())
            ->subject(__('Ticket closed'))
            ->line($closedLine)
            ->line(__('If you still need help, feel free to open a new ticket about this issue.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
