<?php

namespace App\Http\Controllers;

use App\Models\HelpCenter\Article;

class ArticleController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($locale, Article $article)
    {
        return view('articles.show', [
            'locale' => $locale,
            'article' => $article,
        ]);
    }
}
