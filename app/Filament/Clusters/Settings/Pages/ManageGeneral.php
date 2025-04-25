<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Support\HtmlString;

class ManageGeneral extends SettingsPage
{
    protected static ?int $navigationSort = 1;

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
                                TextInput::make('app_name')
                                    ->label(__('Name'))
                                    ->maxLength(255)
                                    ->required()
                                    ->helperText(__('This name will appear on public pages and in emails.')),
                                TextInput::make('app_path')
                                    ->label(__('Path'))
                                    ->prefix(config('app.url').'/')
                                    ->maxLength(255)
                                    ->required()
                                    ->helperText(__('The path where the application is accessible.')),
                            ]),
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
                                Grid::make()
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                FileUpload::make('branding_favicon')
                                                    ->image()
                                                    ->directory('branding/favicon')
                                                    ->label(__('Favicon')),
                                            ])
                                            ->columns(3),
                                        Grid::make()
                                            ->schema([
                                                FileUpload::make('branding_logo_black')
                                                    ->image()
                                                    ->directory('branding/logo')
                                                    ->label(__('Logo black')),

                                                FileUpload::make('branding_logo_white')
                                                    ->image()
                                                    ->directory('branding/logo')
                                                    ->label(__('Logo white')),
                                            ]),
                                    ]),
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

    public function getRedirectUrl(): ?string
    {
        $generalSettings = app(GeneralSettings::class);

        return config('app.url').'/'.trim($generalSettings->app_path, '/').'/settings/general';
    }
}
