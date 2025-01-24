<?php

namespace App\Observers\HelpCenter;

use App\Models\HelpCenter\Article;
use Illuminate\Support\Str;

class ArticleObserver
{
    /**
     * Handle the Article "creating" event.
     */
    public function creating(Article $article): void
    {
        if (auth()->check()) {
            $article->author_id = auth()->id();
        }

        if (isset($article->title)) {
            // Generate the base slug from the name
            $baseSlug = Str::slug($article->title);

            // Prepend an unique ID to ensure the slug is unique
            $uniqueSlug = uniqid().'-'.$baseSlug;

            // Assign the generated unique slug to the article
            $article->slug = $uniqueSlug;
        }

        $article->sort = Article::where('section_id', $article->section_id)->max('sort') + 1;
    }

    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "force deleted" event.
     */
    public function forceDeleted(Article $article): void
    {
        //
    }
}
