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
        if ($article->status !== ArticleStatus::PUBLISHED) {
            abort(404);
        }

        return view('articles.show', [
            'locale' => $locale,
            'article' => $article,
        ]);
    }
}
