<?php

namespace App\Filament\Clusters\HelpCenter\Resources\CategoryResource\Pages;

use App\Filament\Clusters\HelpCenter\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
