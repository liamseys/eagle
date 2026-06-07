<?php

namespace App\Notifications;

use App\Models\Ticket;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SlaApproachingBreach extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket, public string $targetLabel) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->warning()
            ->icon('heroicon-o-exclamation-triangle')
            ->title(__(':target SLA approaching breach', ['target' => $this->targetLabel]))
            ->body(__('Ticket #:id is approaching its :target deadline.', [
                'id' => $this->ticket->ticket_id,
                'target' => mb_strtolower($this->targetLabel),
            ]))
            ->getDatabaseMessage();
    }
}
