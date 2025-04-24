<?php

namespace App\Filament\Client\Pages;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ReportABug extends Page
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Report a bug';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.client.pages.report-a-bug';

    public function getTitle(): string|Htmlable
    {
        return __('Report a bug');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->placeholder(__('Provide a brief, clear summary of the issue or request'))
                            ->required(),
                        RichEditor::make('description')
                            ->label(__('Description'))
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->helperText(__('Describe the issue in detail, including any relevant steps or error messages'))
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $user = auth('client')->user();
        $formData = $this->form->getState();

        $ticket = $user->tickets()->create([
            'subject' => $formData['title'],
            'priority' => TicketPriority::NORMAL,
            'type' => TicketType::PROBLEM,
            'status' => TicketStatus::OPEN,
        ]);

        $ticket->comments()->create([
            'authorable_type' => get_class($user),
            'authorable_id' => $user->id,
            'body' => $formData['description'],
        ]);

        $this->resetForm();

        $this->sendSuccessNotification();
    }

    /**
     * Reset form data.
     */
    private function resetForm(): void
    {
        $this->reset('data');
        $this->form->fill();
    }

    /**
     * Send success notification.
     */
    private function sendSuccessNotification()
    {
        Notification::make()
            ->title(__('Success'))
            ->body(__('Your bug report has been submitted.'))
            ->success()
            ->send();
    }
}
