<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Settings\AdvancedSettings;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ManageAdvanced extends SettingsPage
{
    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationLabel = 'Advanced';

    protected static ?string $slug = 'advanced';

    protected static ?string $title = 'Advanced Settings';

    protected ?string $heading = 'Advanced Settings';

    protected static string $settings = AdvancedSettings::class;

    protected static ?string $cluster = Settings::class;

    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo('settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Advanced'))
                    ->description(__('Manage your advanced settings.'))
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('ticket_id_start')
                                    ->label(__('Ticket IDs'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(fn (Get $get) => $get('ticket_id_start') ?? 1)
                                    ->maxValue(999999999)
                                    ->rules([
                                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) {
                                            $advancedSettings = app(AdvancedSettings::class);
                                            $currentMaxTicketId = $advancedSettings->ticket_id_start ?? 1;

                                            if ($value < $currentMaxTicketId) {
                                                $fail(__('The Ticket ID must be greater than or equal to the current value of :max.', ['max' => $currentMaxTicketId]));
                                            }

                                            if ($value > 999999999) {
                                                $fail(__('The Ticket ID must not exceed 9 digits.'));
                                            }
                                        },
                                    ])
                                    ->helperText(fn () => __('Set the Ticket ID counter to start tickets at a chosen number. This applies to future tickets only and must be greater than or equal to the current value (e.g., ≥ :current). Limit to nine digits.', [
                                        'current' => app(AdvancedSettings::class)->ticket_id_start ?? 1,
                                    ]))
                                    ->columnSpan(2),
                            ])->columns(3),
                    ]),
            ]);
    }
}
