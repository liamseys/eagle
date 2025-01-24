<?php

namespace App\Http\Controllers;

use App\Models\HelpCenter\Category;

class CategoryController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($locale, Category $category)
    {
        return view('categories.show', [
            'locale' => $locale,
            'category' => $category,
        ]);
    }
}
