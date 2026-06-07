<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Enums\Tickets\TicketPriority;
use App\Filament\Clusters\Settings;
use App\Settings\GeneralSettings;
use App\Settings\WorkflowSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ManageWorkflows extends SettingsPage
{
    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Service level agreements'))
                    ->description(__('Track first response and resolution targets against your configured business hours.'))
                    ->schema([
                        Toggle::make('sla_enabled')
                            ->label(__('Enable SLA tracking'))
                            ->helperText(__('When enabled, new tickets receive first response and resolution deadlines based on their priority.'))
                            ->inline(false)
                            ->live(),
                        TextInput::make('sla_at_risk_threshold_percent')
                            ->label(__('At-risk threshold'))
                            ->helperText(__('Flag a target as "at risk" once this percentage of its time remains.'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn (Get $get): bool => (bool) $get('sla_enabled')),
                    ]),
                Section::make(__('Targets'))
                    ->description(__('First response and resolution targets per priority, counted in business hours.'))
                    ->visible(fn (Get $get): bool => (bool) $get('sla_enabled'))
                    ->columns(1)
                    ->schema(
                        collect(TicketPriority::cases())
                            ->map(fn (TicketPriority $priority): Fieldset => Fieldset::make($priority->getLabel())
                                ->columns(2)
                                ->schema([
                                    TextInput::make("sla_targets.{$priority->value}.first_response_hours")
                                        ->label(__('First response'))
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->step(0.25)
                                        ->suffix(__('business hours')),
                                    TextInput::make("sla_targets.{$priority->value}.resolution_hours")
                                        ->label(__('Resolution'))
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->step(0.25)
                                        ->suffix(__('business hours')),
                                ]))
                            ->toArray()
                    ),
            ]);
    }

    /**
     * Present the stored business-minute targets as business hours in the form.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (TicketPriority::cases() as $priority) {
            $target = $data['sla_targets'][$priority->value] ?? [];

            $data['sla_targets'][$priority->value] = [
                'first_response_hours' => round(($target['first_response_minutes'] ?? 0) / 60, 2),
                'resolution_hours' => round(($target['resolution_minutes'] ?? 0) / 60, 2),
            ];
        }

        return $data;
    }

    /**
     * Persist the form's business-hour targets back as business minutes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach (TicketPriority::cases() as $priority) {
            $target = $data['sla_targets'][$priority->value] ?? [];

            $data['sla_targets'][$priority->value] = [
                'first_response_minutes' => (int) round(((float) ($target['first_response_hours'] ?? 0)) * 60),
                'resolution_minutes' => (int) round(((float) ($target['resolution_hours'] ?? 0)) * 60),
            ];
        }

        return $data;
    }

    public function getRedirectUrl(): ?string
    {
        $generalSettings = app(GeneralSettings::class);

        return config('app.url').'/'.trim($generalSettings->app_path, '/').'/settings/workflows';
    }
}
