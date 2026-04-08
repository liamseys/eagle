<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Imports\ClientImporter;
use App\Filament\Resources\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->icon('heroicon-o-cloud-arrow-up')
                ->importer(ClientImporter::class),
            CreateAction::make()
                ->icon('heroicon-o-plus'),
        ];
    }
}
