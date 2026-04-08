<?php

namespace App\Filament\Clusters\HelpCenter\Resources\CategoryResource\Pages;

use App\Filament\Clusters\HelpCenter\Resources\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->modalWidth(Width::Medium),
        ];
    }
}
