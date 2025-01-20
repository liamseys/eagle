<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Imports\ClientImporter;
use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(ClientImporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
