<?php

namespace App\Livewire;

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCommentByAgent;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
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
                    ->helperText(__('Non-public comments are only visible internally.'))
                    ->hidden(fn () => ! auth()->user() instanceof User),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        if ($this->ticket->status === TicketStatus::CLOSED) {
            return;
        }

        $user = auth()->user();
        $formData = $this->form->getState();

        $ticketComment = $this->ticket->comments()->create([
            'authorable_type' => get_class($user),
            'authorable_id' => $user->id,
            'body' => $formData['comment'],
            'is_public' => $formData['is_public'] ?? true,
        ]);

        if ($user instanceof User && is_null($this->ticket->assignee_id)) {
            $this->ticket->update(['assignee_id' => $user->id]);
        }

        // Update the ticket status
        app(UpdateTicketStatus::class)->handle($this->ticket, TicketStatus::PENDING);

        // Notify the requester if the comment is public
        if (isset($formData['is_public']) && $formData['is_public'] && $this->ticket->requester) {
            $this->ticket->requester->notify(new TicketCommentByAgent($ticketComment));
        }

        $this->resetForm();

        $this->dispatch('comment-created');

        $this->sendSuccessNotification($user);
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
    private function sendSuccessNotification($user): void
    {
        Notification::make()
            ->title(__('Success'))
            ->body(
                $user instanceof User
                    ? __('Your comment has been added to the ticket. The requester will be notified.')
                    : __('Your comment has been added to the ticket.')
            )
            ->success()
            ->send();
    }

    #[On('ticket-closed')]
    public function render()
    {
        return view('livewire.create-ticket-comment');
    }
}
