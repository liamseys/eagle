<?php

namespace App\Notifications;

use App\Models\TicketComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCommentByAgent extends Notification
{
    use Queueable;

    public TicketComment $ticketComment;

    /**
     * Create a new notification instance.
     */
    public function __construct(TicketComment $ticketComment)
    {
        $this->ticketComment = $ticketComment;
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
            ->replyTo($this->ticketComment->ticket->getSupportEmailWithTicketId())
            ->subject(__('New response to your ticket'))
            ->markdown(
                'mail.ticket.comment', ['ticketComment' => $this->ticketComment]
            );
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
