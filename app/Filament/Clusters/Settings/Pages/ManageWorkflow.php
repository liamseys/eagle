<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Enums\Tickets\TicketPriority;
use App\Filament\Clusters\Settings;
use App\Settings\WorkflowSettings;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageWorkflow extends SettingsPage
{
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Workflows';

    protected static ?string $slug = 'workflows';

    protected static ?string $title = 'Workflows';

    protected ?string $heading = 'Workflows';

    protected static string $settings = WorkflowSettings::class;

    protected static ?string $cluster = Settings::class;

    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo('settings');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('SLA Policy'))
                    ->description(__('Define and manage Service Level Agreement (SLA) policies for each ticket priority. Specify the time limits for first response, subsequent responses, and resolution to ensure consistent and efficient client support.'))
                    ->schema([
                        Repeater::make('sla_policies')
                            ->label('')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make('priority')
                                            ->label(__('Priority'))
                                            ->options(TicketPriority::class)
                                            ->disabled(),
                                        Hidden::make('priority'),
                                    ]),
                                Grid::make()
                                    ->schema([
                                        TextInput::make('first_response_time')
                                            ->label(__('First response time (minutes)'))
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('every_response_time')
                                            ->label(__('Every response time (minutes)'))
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('resolution_time')
                                            ->label(__('Resolution time (minutes)'))
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
