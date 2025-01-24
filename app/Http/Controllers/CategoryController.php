<?php

namespace App\Http\Controllers;

use App\Models\HelpCenter\Category;

class CategoryController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return view('categories.show', [
            'category' => $category,
        ]);
    }
}
