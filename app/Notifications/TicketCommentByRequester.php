<?php

namespace App\Notifications;

use App\Filament\Resources\TicketResource;
use App\Models\TicketComment;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCommentByRequester extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public TicketComment $ticketComment) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__(':name replied to ticket #:ticketId', [
                'name' => $this->requesterName(),
                'ticketId' => $this->ticketComment->ticket->ticket_id,
            ]))
            ->line(__(':name replied to the ticket ":subject".', [
                'name' => $this->requesterName(),
                'subject' => $this->ticketComment->ticket->subject,
            ]))
            ->action(__('View reply'), $this->commentUrl())
            ->line(__('You are receiving this email because you are responsible for this ticket.'));
    }

    /**
     * Get the Filament database notification representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__(':name replied', ['name' => $this->requesterName()]))
            ->body(__('On the ticket ":subject".', [
                'subject' => $this->ticketComment->ticket->subject,
            ]))
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->iconColor('primary')
            ->actions([
                Action::make('view')
                    ->label(__('View reply'))
                    ->url($this->commentUrl())
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    /**
     * The deep link to the reply on the ticket's edit page.
     */
    public function commentUrl(): string
    {
        return TicketResource::getUrl('edit', ['record' => $this->ticketComment->ticket])
            .'#comment-'.$this->ticketComment->id;
    }

    protected function requesterName(): string
    {
        return $this->ticketComment->authorable?->name ?? __('The requester');
    }
}
