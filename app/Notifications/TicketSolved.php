<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Settings\AdvancedSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketSolved extends Notification
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
        $hours = app(AdvancedSettings::class)->auto_close_solved_after_hours;

        return (new MailMessage)
            ->replyTo($this->ticket->getSupportEmailWithTicketId())
            ->subject(__('Ticket solved'))
            ->line(__('An agent has marked your ticket as solved.'))
            ->line(__('To reopen the ticket, simply reply to this email. Otherwise, it will be automatically closed after :hours hours.', ['hours' => $hours]));
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
