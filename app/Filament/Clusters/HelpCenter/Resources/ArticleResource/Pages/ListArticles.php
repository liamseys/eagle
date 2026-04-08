<?php

namespace App\Filament\Clusters\HelpCenter\Resources\ArticleResource\Pages;

use App\Filament\Clusters\HelpCenter\Resources\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus'),
        ];
    }
}
