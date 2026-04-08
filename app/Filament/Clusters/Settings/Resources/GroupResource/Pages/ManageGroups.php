<?php

namespace App\Filament\Clusters\Settings\Resources\GroupResource\Pages;

use App\Filament\Clusters\Settings\Resources\GroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageGroups extends ManageRecords
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->modalWidth(Width::Large),
        ];
    }
}
