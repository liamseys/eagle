<?php

namespace App\Http\Controllers;

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\HelpCenter\Category;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $categories = Category::whereHas('articles', function ($query) {
            $query->where('status', ArticleStatus::PUBLISHED)
                ->where('is_public', '=', true);
        })->orderBy('sort')->get();

        return view('index', [
            'categories' => $categories,
        ]);
    }
}
