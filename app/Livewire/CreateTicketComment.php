<?php

namespace App\Livewire;

use App\Models\Ticket;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
        $this->ticket->comments()->create([
            'authorable_type' => auth()->user()::class,
            'authorable_id' => auth()->id(),
            'body' => $this->form->getState()['comment'],
            'is_public' => $this->form->getState()['is_public'],
        ]);
    }

    public function render()
    {
        return view('livewire.create-ticket-comment');
    }
}
