<?php

namespace App\Filament\Clusters\HelpCenter\Resources\ArticleResource\Pages;

use App\Filament\Clusters\HelpCenter\Resources\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
}
