<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OpenTicketsByAgentChart;
use App\Filament\Widgets\OperationalOverview;
use App\Filament\Widgets\ResponseTimeMetrics;
use App\Filament\Widgets\SlaAttentionTable;
use App\Filament\Widgets\SlaPerformanceOverview;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TicketPriorityChart;
use App\Filament\Widgets\TicketTypeChart;
use App\Filament\Widgets\WorkloadOverview;
use App\Models\Client;
use App\Models\Group;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    #[Url]
    public ?array $filters = null;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
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
                        Select::make('clientId')
                            ->label('Client')
                            ->options(Client::query()->pluck('name', 'id'))
                            ->searchable()
                            ->live(),
                        Select::make('assigneeId')
                            ->label('Agent')
                            ->options(User::query()->pluck('name', 'id'))
                            ->searchable()
                            ->live(),
                        Select::make('groupId')
                            ->label('Group')
                            ->options(Group::query()->pluck('name', 'id'))
                            ->searchable()
                            ->live(),
                    ])
                    ->columns(3),
            ]);
    }

    /**
     * Group the widgets into focused tabs so each view stays scannable while every
     * metric remains available. Filters above the tabs apply across all of them.
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFiltersFormContentComponent(),
                Tabs::make()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make(__('Overview'))
                            ->icon('heroicon-m-bolt')
                            ->schema($this->tabWidgets([
                                OperationalOverview::class,
                                SlaAttentionTable::class,
                            ])),
                        Tab::make(__('Analytics'))
                            ->icon('heroicon-m-chart-pie')
                            ->schema($this->tabWidgets([
                                StatsOverview::class,
                                TicketPriorityChart::class,
                                TicketTypeChart::class,
                            ])),
                        Tab::make(__('Performance'))
                            ->icon('heroicon-m-trophy')
                            ->schema($this->tabWidgets([
                                SlaPerformanceOverview::class,
                                ResponseTimeMetrics::class,
                            ])),
                        Tab::make(__('Workload'))
                            ->icon('heroicon-m-users')
                            ->schema($this->tabWidgets([
                                WorkloadOverview::class,
                                OpenTicketsByAgentChart::class,
                            ])),
                    ]),
            ]);
    }

    /**
     * Render a list of widget classes in the dashboard's responsive grid.
     *
     * @param  array<class-string>  $widgets
     * @return array<Component>
     */
    private function tabWidgets(array $widgets): array
    {
        return [
            Grid::make($this->getColumns())
                ->schema($this->getWidgetsSchemaComponents($widgets)),
        ];
    }

    public function persistsFiltersInSession(): bool
    {
        return false;
    }
}
