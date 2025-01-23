<?php

namespace App\Http\Controllers;

use App\Models\HelpCenter\Category;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $categories = Category::all();

        return view('index', [
            'categories' => $categories,
        ]);
    }
}
