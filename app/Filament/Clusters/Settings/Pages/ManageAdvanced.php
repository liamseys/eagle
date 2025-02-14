<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Settings\AdvancedSettings;
use Closure;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\SettingsPage;

class ManageAdvanced extends SettingsPage
{
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
