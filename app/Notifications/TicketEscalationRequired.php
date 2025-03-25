<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketEscalationRequired extends Notification
{
    use Queueable;

    public Ticket $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
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
        return (new MailMessage)
            ->replyTo($this->ticket->getSupportEmailWithTicketId())
            ->subject(__('We need some information from you'))
            ->line(__('We need a few things to move forward:'))
            ->line(__('1. Ask your Account Manager to escalate the case (#:ticket_id) if they have not already.', ['ticket_id' => $this->ticket->ticket_id]))
            ->line(__('2. Provide the business need or purpose behind your request.'))
            ->line(__('In the meantime, please visit :url for helpful resources and self-serve support options.', ['url' => config('app.url')]));
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
