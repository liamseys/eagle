<?php

namespace App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages;

use App\Filament\Clusters\HelpCenter\Resources\FormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

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
}
