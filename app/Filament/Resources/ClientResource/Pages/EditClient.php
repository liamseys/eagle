<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add_note')
                ->label('Add note')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->modalWidth('md')
                ->modalDescription(__('Notes can be viewed by other agents but will remain hidden from the client.'))
                ->form([
                    Textarea::make('note')
                        ->label(__('Note'))
                        ->placeholder(__('Write a note, only visible to agents'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->notes()->create([
                        'user_id' => auth()->id(),
                        'body' => $data['note'],
                    ]);

                    Notification::make()
                        ->title(__('Success'))
                        ->body(__('Your note has been added.'))
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }
}
