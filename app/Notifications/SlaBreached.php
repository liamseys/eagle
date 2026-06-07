<?php

namespace App\Notifications;

use App\Models\Ticket;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SlaBreached extends Notification
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
            ->danger()
            ->icon('heroicon-o-fire')
            ->title(__(':target SLA breached', ['target' => $this->targetLabel]))
            ->body(__('Ticket #:id has breached its :target deadline.', [
                'id' => $this->ticket->ticket_id,
                'target' => mb_strtolower($this->targetLabel),
            ]))
            ->getDatabaseMessage();
    }
}
