<?php

namespace App\Filament\Clusters\Settings\Resources\GroupResource\Pages;

use App\Filament\Clusters\Settings\Resources\GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageGroups extends ManageRecords
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->modalWidth(MaxWidth::Large),
        ];
    }
}
