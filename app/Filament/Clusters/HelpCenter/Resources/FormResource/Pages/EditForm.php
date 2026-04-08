<?php

namespace App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages;

use App\Filament\Clusters\HelpCenter\Resources\FormResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewForm')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn ($record) => route('forms.show', [
                    'locale' => config('app.locale'),
                    'form' => $record->slug,
                ]), true),
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
}
