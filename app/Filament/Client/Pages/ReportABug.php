<?php

namespace App\Filament\Client\Pages;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
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
                            ->helperText(__('Describe the issue in detail, including any relevant steps or error messages'))
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        dd($this->form->getState());
    }
}
