<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filters')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Start date')
                            ->native(false)
                            ->suffixAction(
                                Action::make('clearStartDate')
                                    ->icon('heroicon-m-x-mark')
                                    ->action(fn ($set) => $set('startDate', null))
                                    ->visible(fn ($state) => filled($state))
                            )
                            ->live(),
                        DatePicker::make('endDate')
                            ->label('End date')
                            ->native(false)
                            ->suffixAction(
                                Action::make('clearEndDate')
                                    ->icon('heroicon-m-x-mark')
                                    ->action(fn ($set) => $set('endDate', null))
                                    ->visible(fn ($state) => filled($state))
                            )
                            ->live(),
                    ])
                    ->columns(3),
            ]);
    }
}
