<?php

namespace App\Http\Controllers;

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\HelpCenter\Category;

class CategoryController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($locale, Category $category)
    {
        $sections = $category->sections()
            ->with(['articles', 'forms'])
            ->where(function ($query) {
                $query->whereHas('articles', function ($q) {
                    $q->where('status', ArticleStatus::PUBLISHED)
                        ->where('is_public', true);
                })->orWhereHas('forms', function ($q) {
                    $q->where('is_public', true);
                });
            })
            ->get();

        return view('categories.show', [
            'locale' => $locale,
            'category' => $category,
            'sections' => $sections,
        ]);
    }
}
