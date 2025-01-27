<?php

namespace App\Http\Controllers;

use App\Enums\HelpCenter\ArticleStatus;
use App\Models\HelpCenter\Category;

class CategoryController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($locale, Category $category)
    {
        $sections = $category->sections()->whereHas('articles', function ($query) {
            $query->where('status', ArticleStatus::PUBLISHED)
                ->where('is_public', '=', true);
        })->get();

        return view('categories.show', [
            'locale' => $locale,
            'category' => $category,
            'sections' => $sections,
        ]);
    }
}
