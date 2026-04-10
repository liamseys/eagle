<?php

namespace App\Ai\Tools;

use App\Models\HelpCenter\Article;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class SearchArticlesTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Search through Help Center articles to find relevant content that matches the user\'s question. Returns article titles, descriptions, body content, and slugs for linking.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        $query = $request->string('query');

        $articles = Article::published()
            ->public()
            ->where(function ($q) use ($query) {
                $terms = explode(' ', $query);

                foreach ($terms as $term) {
                    $q->where(function ($inner) use ($term) {
                        $inner->where('title', 'like', "%{$term}%")
                            ->orWhere('description', 'like', "%{$term}%")
                            ->orWhere('body', 'like', "%{$term}%");
                    });
                }
            })
            ->select(['title', 'slug', 'description', 'body'])
            ->limit(5)
            ->get();

        if ($articles->isEmpty()) {
            return 'No relevant articles found for this query.';
        }

        return "Relevant Help Center articles found:\n\n".$articles->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema
                ->string()
                ->description('The search query to find relevant Help Center articles.')
                ->required(),
        ];
    }
}
