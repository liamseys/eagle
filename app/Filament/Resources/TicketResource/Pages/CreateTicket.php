<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Support\TicketMergeTags;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->submit(null)
            ->action('create');
    }

    protected function getFormActions(): array
    {
        return [];
    }

    /**
     * @return array<Action>
     */
    public function getInlineFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function afterCreate(): void
    {
        $user = auth()->user();

        $this->record->refresh();

        $body = RichContentRenderer::make($this->data['comment'])
            ->mergeTags(TicketMergeTags::valuesFor($this->record))
            ->toHtml();

        $this->record->comments()->create([
            'authorable_type' => $user::class,
            'authorable_id' => $user->id,
            'body' => $body,
            'is_public' => true,
        ]);
    }
}
