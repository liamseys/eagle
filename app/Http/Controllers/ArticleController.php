<?php

namespace App\Http\Controllers;

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\HelpCenter\Article;

class ArticleController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($locale, Article $article)
    {
        if (! auth()->check() && $article->status !== ArticleStatus::PUBLISHED) {
            abort(404);
        }

        return view('articles.show', [
            'locale' => $locale,
            'article' => $article,
        ]);
    }

    public function publish($locale, Article $article)
    {
        $article->update(['status' => ArticleStatus::PUBLISHED]);

        return redirect()->back()->with('status', 'Article has been published!');
    }

    public function unpublish($locale, Article $article)
    {
        $article->update(['status' => ArticleStatus::DRAFT]);

        return redirect()->back()->with('status', 'Article has been unpublished!');
    }
}
