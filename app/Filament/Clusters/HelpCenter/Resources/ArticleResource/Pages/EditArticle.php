<?php

namespace App\Filament\Clusters\HelpCenter\Resources\ArticleResource\Pages;

use App\Filament\Clusters\HelpCenter\Resources\ArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewArticle')
                ->color('gray')
                ->url(fn($record) => route('articles.show', [
                    'locale' => config('app.locale'),
                    'article' => $record->slug
                ]), true),
            Actions\DeleteAction::make(),
        ];
    }
}
