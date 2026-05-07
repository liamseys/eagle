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
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make('viewActivity')
                ->label('View activity')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->modalHeading(__('View activity'))
                ->modalDescription(__('Track all updates related to the ticket, including changes in priority, type, or status, and see who made them.'))
                ->modalWidth(Width::Large)
                ->slideOver()
                ->schema([
                    TicketActivity::make('ticketActivity')->label(''),
                ]),
            Action::make('mergeTicket')
                ->label(__('Merge ticket'))
                ->icon('heroicon-o-document-duplicate')
                ->requiresConfirmation()
                ->modalSubmitActionLabel(__('Merge'))
                ->modalWidth(Width::Large)
                ->modalDescription(__('Warning: This action cannot be undone. This ticket will be merged into the original and marked as a duplicate.'))
                ->schema([
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

                    if ($record->status !== TicketStatus::CLOSED) {
                        app(UpdateTicketStatus::class)->handle($record, TicketStatus::CLOSED, [
                            'reason' => 'The ticket was closed because it is a duplicate.',
                        ]);
                    }

                    Notification::make()
                        ->title(__('Merged into ticket #'.$ticket->ticket_id))
                        ->success()
                        ->send();
                })->hidden(fn ($livewire) => $livewire->record->duplicate_of_ticket_id),
            ActionGroup::make([
                Action::make('closeNow')
                    ->label(__('Close now'))
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function (Ticket $record) {
                        app(UpdateTicketStatus::class)->handle($record, TicketStatus::CLOSED);

                        Notification::make()
                            ->title(__('Ticket closed'))
                            ->success()
                            ->send();
                    }),
                Action::make('scheduleClose')
                    ->label(__('Schedule for closing'))
                    ->icon('heroicon-o-clock')
                    ->disabled(fn (Ticket $record): bool => $record->scheduled_close_at !== null)
                    ->modalHeading(__('Schedule ticket for closing'))
                    ->modalDescription(__('The ticket will be automatically closed at the selected date and time.'))
                    ->modalSubmitActionLabel(__('Schedule'))
                    ->modalWidth(Width::Medium)
                    ->schema([
                        DateTimePicker::make('scheduled_close_at')
                            ->label(__('Close ticket on'))
                            ->seconds(false)
                            ->native(false)
                            ->required()
                            ->after('now'),
                    ])
                    ->fillForm(fn (Ticket $record): array => [
                        'scheduled_close_at' => $record->scheduled_close_at,
                    ])
                    ->action(function (array $data, Ticket $record) {
                        $record->update([
                            'scheduled_close_at' => $data['scheduled_close_at'],
                        ]);

                        Notification::make()
                            ->title(__('Ticket scheduled for closing'))
                            ->success()
                            ->send();
                    }),
            ])
                ->label(__('Close'))
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->button()
                ->dropdownPlacement('bottom-end')
                ->hidden(fn ($livewire) => $livewire->record->status === TicketStatus::CLOSED),
            DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->submit(null)
            ->action('save');
    }

    protected function getFormActions(): array
    {
        return [];
    }

    /**
     * @return array<Action>
     */
    public function getInlineFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update([
            'requester_id' => $data['requester_id'],
            'assignee_id' => $data['assignee_id'],
            'group_id' => $data['group_id'],
        ]);

        $priority = $data['priority'] ?? null;
        if ($priority !== null) {
            $priority = $priority instanceof TicketPriority ? $priority : TicketPriority::from($priority);
            if ($record->priority !== $priority) {
                app(UpdateTicketPriority::class)->handle($record, $priority);
            }
        }

        $type = $data['type'] ?? null;
        if ($type !== null) {
            $type = $type instanceof TicketType ? $type : TicketType::from($type);
            if ($record->type !== $type) {
                app(UpdateTicketType::class)->handle($record, $type);
            }
        }

        $status = $data['status'] ?? null;
        if ($status !== null) {
            $status = $status instanceof TicketStatus ? $status : TicketStatus::from($status);
            if ($record->status !== $status) {
                app(UpdateTicketStatus::class)->handle($record, $status);
            }
        }

        return $record;
    }

    #[On('comment-created')]
    public function refreshFormDataAction()
    {
        $this->refreshFormData(['status']);
    }
}
