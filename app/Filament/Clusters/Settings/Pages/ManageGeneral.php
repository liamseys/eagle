<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Settings\GeneralSettings;
use Closure;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\SettingsPage;
use Illuminate\Support\HtmlString;

class ManageGeneral extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'General';

    protected static ?string $slug = 'general';

    protected static ?string $title = 'General Settings';

    protected ?string $heading = 'General Settings';

    protected static string $settings = GeneralSettings::class;

    protected static ?string $cluster = Settings::class;

    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo('settings');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Basics'))
                    ->description(__('Manage your basic settings.'))
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
                                            $generalSettings = app(GeneralSettings::class);
                                            $currentMaxTicketId = $generalSettings->ticket_id_start ?? 1;

                                            if ($value < $currentMaxTicketId) {
                                                $fail(__('The Ticket ID must be greater than or equal to the current value of :max.', ['max' => $currentMaxTicketId]));
                                            }

                                            if ($value > 999999999) {
                                                $fail(__('The Ticket ID must not exceed 9 digits.'));
                                            }
                                        },
                                    ])
                                    ->helperText(fn () => __('Set the Ticket ID counter to start tickets at a chosen number. This applies to future tickets only and must be greater than or equal to the current value (e.g., ≥ :current). Limit to nine digits.', [
                                        'current' => app(GeneralSettings::class)->ticket_id_start ?? 1,
                                    ]))
                                    ->columnSpan(2),
                            ])->columns(3),
                    ]),
                Section::make(__('Support addresses'))
                    ->description(__('Emails to these addresses will create tickets.'))
                    ->schema([
                        Repeater::make('support_email_addresses')
                            ->label('')
                            ->addActionLabel(__('Add email'))
                            ->reorderable(false)
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('label')
                                            ->label(__('Label'))
                                            ->maxLength(255)
                                            ->required(),
                                        TextInput::make('email')
                                            ->label(__('Email'))
                                            ->email()
                                            ->suffixIcon('heroicon-m-envelope')
                                            ->maxLength(255)
                                            ->required(),
                                    ]),
                            ]),
                    ]),
                Section::make(__('Allowlisted domains'))
                    ->description(__('These domains are allowed to access Eagle.'))
                    ->schema([
                        Repeater::make('allowlisted_domains')
                            ->label('')
                            ->addActionLabel(__('Add domain'))
                            ->reorderable(false)
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('label')
                                            ->label(__('Label'))
                                            ->maxLength(255)
                                            ->required(),
                                        TextInput::make('domain')
                                            ->label(__('Domain'))
                                            ->suffixIcon('heroicon-m-globe-alt')
                                            ->maxLength(255)
                                            ->required(),
                                    ]),
                            ]),
                    ]),
                Section::make(__('Branding'))
                    ->description(__('Customize your branding settings.'))
                    ->schema([
                        Grid::make()
                            ->schema([
                                ColorPicker::make('branding_primary_color')
                                    ->label(__('Primary color'))
                                    ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/')
                                    ->required()
                                    ->helperText(__('This is the primary color that will be applied across the entire application.')),
                                TextInput::make('branding_primary_font')
                                    ->label(__('Primary font'))
                                    ->required()
                                    ->hint(new HtmlString('<a href="https://fonts.google.com/" target="_blank">➜ Google Fonts</a>'))
                                    ->helperText(__('This is the primary font that will be applied across the entire application. This must be a Google Font.')),
                            ]),
                        Section::make()
                            ->description(__('These colors are applied in the help center to create the gradient effect in the hero section.'))
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        ColorPicker::make('branding_gradient_from_color')
                                            ->label(__('Gradient from color'))
                                            ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/')
                                            ->required(),
                                        ColorPicker::make('branding_gradient_via_color')
                                            ->label(__('Gradient via color'))
                                            ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/')
                                            ->required(),
                                        ColorPicker::make('branding_gradient_to_color')
                                            ->label(__('Gradient to color'))
                                            ->regex('/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/')
                                            ->required(),
                                    ])->columns(3),
                            ]),
                    ]),
            ]);
    }
}
