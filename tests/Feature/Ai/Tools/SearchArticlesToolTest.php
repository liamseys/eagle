<?php

use App\Ai\Tools\SearchArticlesTool;
use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\HelpCenter\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;

uses(RefreshDatabase::class);

it('returns published public articles matching the query', function () {
    Article::factory()->create([
        'title' => 'How to reset your password',
        'description' => 'Steps to reset your account password.',
        'body' => 'Follow these steps to reset your password safely.',
        'status' => ArticleStatus::PUBLISHED,
        'is_public' => true,
    ]);

    $tool = new SearchArticlesTool;
    $request = new Request(['query' => 'reset password']);
    $result = $tool->handle($request);

    expect($result)
        ->toContain('How to reset your password')
        ->toContain('Relevant Help Center articles found');
});

it('excludes draft articles from search results', function () {
    Article::factory()->create([
        'title' => 'Draft article about billing',
        'description' => 'Billing information.',
        'body' => 'This is about billing.',
        'status' => ArticleStatus::DRAFT,
        'is_public' => true,
    ]);

    $tool = new SearchArticlesTool;
    $request = new Request(['query' => 'billing']);
    $result = $tool->handle($request);

    expect($result)->toBe('No relevant articles found for this query.');
});

it('excludes private articles from search results', function () {
    Article::factory()->create([
        'title' => 'Private internal guide',
        'description' => 'Internal use only.',
        'body' => 'This is an internal guide.',
        'status' => ArticleStatus::PUBLISHED,
        'is_public' => false,
    ]);

    $tool = new SearchArticlesTool;
    $request = new Request(['query' => 'internal guide']);
    $result = $tool->handle($request);

    expect($result)->toBe('No relevant articles found for this query.');
});

it('returns no results message when no articles match', function () {
    $tool = new SearchArticlesTool;
    $request = new Request(['query' => 'nonexistent topic xyz']);
    $result = $tool->handle($request);

    expect($result)->toBe('No relevant articles found for this query.');
});
