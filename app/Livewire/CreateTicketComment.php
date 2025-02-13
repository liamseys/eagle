<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Notifications\TicketCommentByAgent;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class CreateTicketComment extends Component implements HasForms
{
    use InteractsWithForms;

    public Ticket $ticket;

    public ?array $data = [];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('comment')
                    ->label(__('Comment'))
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
                    ->maxLength(2500)
                    ->required(),
                Toggle::make('is_public')
                    ->label(__('Public'))
                    ->default(true)
                    ->required()
                    ->helperText(__('Non-public comments are only visible internally.')),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $user = auth()->user();
        $formData = $this->form->getState();

        $ticketComment = $this->ticket->comments()->create([
            'authorable_type' => get_class($user),
            'authorable_id' => $user->id,
            'body' => $formData['comment'],
            'is_public' => $formData['is_public'],
        ]);

        $this->ticket->update(['assignee_id' => $user->id]);

        if ($formData['is_public'] && $this->ticket->requester) {
            $this->ticket->requester->notify(new TicketCommentByAgent($ticketComment));
        }

        $this->reset('data');
        $this->form->fill();

        $this->dispatch('comment-created');

        Notification::make()
            ->title(__('Success'))
            ->body(__('Your comment has been added to the ticket. The requester will be notified.'))
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.create-ticket-comment');
    }
}
