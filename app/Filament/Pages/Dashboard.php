<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsOverview;
use App\Filament\Widgets\OpenTicketsByAgentChart;
use App\Filament\Widgets\OperationalOverview;
use App\Filament\Widgets\ResponseTimeMetrics;
use App\Filament\Widgets\SlaAttentionTable;
use App\Filament\Widgets\SlaPerformanceOverview;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                Section::make(__('Filters'))
                    ->icon('heroicon-m-funnel')
                    ->description(__('Refine every tab by date range, client, agent or group.'))
                    ->compact()
                    ->collapsible()
                    ->afterHeader([
                        Action::make('resetFilters')
                            ->label(__('Reset'))
                            ->icon('heroicon-m-arrow-path')
                            ->link()
                            ->color('gray')
                            ->visible(fn (Get $get): bool => filled($get('startDate'))
                                || filled($get('endDate'))
                                || filled($get('clientId'))
                                || filled($get('assigneeId'))
                                || filled($get('groupId')))
                            ->action(function (Set $set): void {
                                $set('startDate', null);
                                $set('endDate', null);
                                $set('clientId', null);
                                $set('assigneeId', null);
                                $set('groupId', null);
                            }),
                    ])
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('startDate')
                                    ->label(__('Start date'))
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->native(false)
                                    ->maxDate(fn (Get $get) => $get('endDate'))
                                    ->suffixAction(
                                        Action::make('clearStartDate')
                                            ->icon('heroicon-m-x-mark')
                                            ->action(fn (Set $set) => $set('startDate', null))
                                            ->visible(fn ($state) => filled($state))
                                    )
                                    ->live(),
                                DatePicker::make('endDate')
                                    ->label(__('End date'))
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->native(false)
                                    ->minDate(fn (Get $get) => $get('startDate'))
                                    ->suffixAction(
                                        Action::make('clearEndDate')
                                            ->icon('heroicon-m-x-mark')
                                            ->action(fn (Set $set) => $set('endDate', null))
                                            ->visible(fn ($state) => filled($state))
                                    )
                                    ->live(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('clientId')
                                    ->label(__('Client'))
                                    ->placeholder(__('All clients'))
                                    ->prefixIcon('heroicon-m-user')
                                    ->options(Client::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->live(),
                                Select::make('assigneeId')
                                    ->label(__('Agent'))
                                    ->placeholder(__('All agents'))
                                    ->prefixIcon('heroicon-m-lifebuoy')
                                    ->options(User::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->live(),
                                Select::make('groupId')
                                    ->label(__('Group'))
                                    ->placeholder(__('All groups'))
                                    ->prefixIcon('heroicon-m-user-group')
                                    ->options(Group::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->live(),
                            ]),
                    ]),
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
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        $this->widget(OperationalOverview::class, ['default' => 4, 'lg' => 3]),
                                        ...$this->getWidgetsSchemaComponents([SlaAttentionTable::class]),
                                    ]),
                            ]),
                        Tab::make(__('Analytics'))
                            ->icon('heroicon-m-chart-pie')
                            ->schema($this->tabWidgets([
                                AnalyticsOverview::class,
                            ], columns: 1)),
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
     * Render a list of widget classes in a responsive grid.
     *
     * @param  array<class-string>  $widgets
     * @param  int|array<string, int>|null  $columns
     * @return array<Component>
     */
    private function tabWidgets(array $widgets, int|array|null $columns = null): array
    {
        return [
            Grid::make($columns ?? $this->getColumns())
                ->schema($this->getWidgetsSchemaComponents($widgets)),
        ];
    }

    /**
     * Build a single widget component that participates in the surrounding grid at
     * a given column span (rather than being liberated from it).
     *
     * @param  class-string  $widgetClass
     * @param  int|string|array<string, int|string>  $columnSpan
     */
    private function widget(string $widgetClass, int|string|array $columnSpan): Component
    {
        return $this->getWidgetsSchemaComponents([$widgetClass])[0]
            ->liberatedFromContainerGrid(false)
            ->columnSpan($columnSpan);
    }

    public function persistsFiltersInSession(): bool
    {
        return false;
    }
}
