<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Enums\Tickets\TicketPriority;
use App\Filament\Clusters\Settings;
use App\Settings\WorkflowSettings;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageWorkflow extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Workflows';

    protected static ?string $slug = 'workflows';

    protected static ?string $title = 'Workflows';

    protected ?string $heading = 'Workflows';

    protected static string $settings = WorkflowSettings::class;

    protected static ?string $cluster = Settings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('SLA Policy'))
                    ->description(__('Test'))
                    ->schema([
                        Repeater::make('sla_policies')
                            ->label('')
                            ->schema([
                                Select::make('priority')
                                    ->label(__('Priority'))
                                    ->options(TicketPriority::class),
                                Grid::make()
                                    ->schema([
                                        TextInput::make('first_response_time')
                                            ->label(__('First Response Time (minutes)'))
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('every_response_time')
                                            ->label(__('Every Response Time (minutes)'))
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('resolution_time')
                                            ->label(__('Resolution Time (minutes)'))
                                            ->numeric()
                                            ->required(),
                                    ])->columns(3),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),
            ]);
    }
}
