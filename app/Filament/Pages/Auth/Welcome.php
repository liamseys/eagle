<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;

class Welcome extends SimplePage implements HasForms
{
    use InteractsWithForms;

    public User $user;

    public ?array $data = [];

    protected ?string $heading = 'Welcome to Eagle';

    protected ?string $subheading = 'We’ve set up your account! Please create a secure password to complete your setup and get started.';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.auth.welcome';

    public function mount(User $user): void
    {
        $this->user = $user;

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('email')
                    ->default($this->user->email)
                    ->required(),
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->confirmed()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->label(__('Confirm password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(255)
                    ->dehydrated(false),
            ])
            ->statePath('data');
    }

    public function savePassword()
    {
        $data = $this->form->getState();

        $this->user->update([
            'password' => bcrypt($data['password']),
            'welcome_valid_until' => null,
        ]);

        auth()->login($this->user);

        Notification::make()
            ->title(__('Success'))
            ->body(__('Welcome! You are now logged in!'))
            ->success()
            ->send();

        return redirect()->route('filament.app.pages.dashboard');
    }
}
