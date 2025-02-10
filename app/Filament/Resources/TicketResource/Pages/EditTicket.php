<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Actions\Tickets\UpdateTicketPriority;
use App\Actions\Tickets\UpdateTicketStatus;
use App\Actions\Tickets\UpdateTicketType;
use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
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
}
