<?php

namespace App\Http\Controllers;

use App\Models\HelpCenter\Article;
use App\Models\HelpCenter\Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search published public articles and visible forms in the Help Center.
     */
    public function __invoke(Request $request, $locale)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim($validated['q'] ?? '');

        $articles = collect();
        $forms = collect();

        if ($query !== '') {
            $like = '%'.addcslashes($query, '\\%_').'%';

            $articles = Article::query()
                ->published()
                ->public()
                ->where(function (Builder $builder) use ($like) {
                    $builder->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('body', 'like', $like);
                })
                ->with('category')
                ->orderByRaw('(title like ?) desc', [$like])
                ->orderBy('title')
                ->limit(25)
                ->get();

            $forms = Form::query()
                ->public()
                ->visibleToViewer()
                ->where(function (Builder $builder) use ($like) {
                    $builder->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->with('section')
                ->orderBy('name')
                ->limit(10)
                ->get();
        }

        return view('search', [
            'locale' => $locale,
            'query' => $query,
            'articles' => $articles,
            'forms' => $forms,
        ]);
    }
}
