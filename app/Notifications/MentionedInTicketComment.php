<?php

namespace App\Notifications;

use App\Filament\Resources\TicketResource;
use App\Models\TicketComment;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentionedInTicketComment extends Notification
{
    use Queueable;

    public function __construct(public TicketComment $ticketComment)
    {
        //
    }

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
            ->subject(__(':name mentioned you in a ticket comment', ['name' => $this->authorName()]))
            ->line(__(':name mentioned you in a comment on the ticket ":subject".', [
                'name' => $this->authorName(),
                'subject' => $this->ticketComment->ticket->subject,
            ]))
            ->action(__('View comment'), $this->commentUrl())
            ->line(__('You are receiving this email because you were mentioned in a ticket comment.'));
    }

    /**
     * Get the Filament database notification representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__(':name mentioned you', ['name' => $this->authorName()]))
            ->body(__('In a comment on the ticket ":subject".', [
                'subject' => $this->ticketComment->ticket->subject,
            ]))
            ->icon('heroicon-o-at-symbol')
            ->iconColor('primary')
            ->actions([
                Action::make('view')
                    ->label(__('View comment'))
                    ->url($this->commentUrl())
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    /**
     * The deep link to the comment on the ticket's edit page.
     */
    public function commentUrl(): string
    {
        return TicketResource::getUrl('edit', ['record' => $this->ticketComment->ticket])
            .'#comment-'.$this->ticketComment->id;
    }

    protected function authorName(): string
    {
        return $this->ticketComment->authorable?->name ?? __('An agent');
    }
}
