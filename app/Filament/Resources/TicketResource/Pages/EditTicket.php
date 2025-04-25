<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Actions\Tickets\UpdateTicketPriority;
use App\Actions\Tickets\UpdateTicketStatus;
use App\Actions\Tickets\UpdateTicketType;
use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Filament\Infolists\Components\TicketActivity;
use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make('viewActivity')
                ->label('View activity')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->modalHeading(__('View activity'))
                ->modalDescription(__('Track all updates related to the ticket, including changes in priority, type, or status, and see who made them.'))
                ->modalWidth(MaxWidth::Large)
                ->slideOver()
                ->infolist([
                    TicketActivity::make('ticketActivity')->label(''),
                ]),
            Actions\Action::make('mergeTicket')
                ->label(__('Merge ticket'))
                ->icon('heroicon-o-document-duplicate')
                ->requiresConfirmation()
                ->modalSubmitActionLabel(__('Merge'))
                ->modalWidth(MaxWidth::Large)
                ->modalDescription(__('Warning: This action cannot be undone. This ticket will be merged into the original and marked as a duplicate.'))
                ->form([
                    Select::make('mainTicket')
                        ->label(__('Main ticket'))
                        ->searchable()
                        ->options(fn (): array => Ticket::query()
                            ->orderByDesc('created_at')
                            ->get()
                            ->mapWithKeys(fn (Ticket $ticket) => [
                                $ticket->id => "#{$ticket->ticket_id} - {$ticket->subject}",
                            ])
                            ->toArray()
                        )
                        ->disableOptionWhen(fn (string $value, ?Ticket $record) => $value === (string) $record?->id),
                ])
                ->action(function (array $data, Ticket $record) {
                    $user = auth()->user();

                    $ticket = Ticket::findOrFail($data['mainTicket']);

                    $record->update([
                        'duplicate_of_ticket_id' => $ticket->id,
                    ]);

                    $updateTicketStatus = app(UpdateTicketStatus::class);
                    $updateTicketStatus->handle($record, TicketStatus::CLOSED, [
                        'reason' => 'The ticket was closed because it is a duplicate.',
                    ]);

                    Notification::make()
                        ->title(__('Merged into ticket #'.$ticket->ticket_id))
                        ->success()
                        ->send();
                })->hidden(fn ($livewire) => $livewire->record->duplicate_of_ticket_id),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->submit(null)
            ->action('save');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update([
            'requester_id' => $data['requester_id'],
            'assignee_id' => $data['assignee_id'],
            'group_id' => $data['group_id'],
        ]);

        if (isset($data['priority']) && $record->priority->value !== $data['priority']) {
            $updateTicketPriority = app(UpdateTicketPriority::class);
            $updateTicketPriority->handle($record, TicketPriority::from($data['priority']));
        }

        if (isset($data['type']) && $record->type->value !== $data['type']) {
            $updateTicketStatus = app(UpdateTicketType::class);
            $updateTicketStatus->handle($record, TicketType::from($data['type']));
        }

        if (isset($data['status']) && $record->status->value !== $data['status']) {
            $updateTicketStatus = app(UpdateTicketStatus::class);
            $updateTicketStatus->handle($record, TicketStatus::from($data['status']));
        }

        return $record;
    }

    #[On('comment-created')]
    public function refreshFormDataAction()
    {
        $this->refreshFormData(['status']);
    }
}
