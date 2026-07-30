<?php

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\Client;
use App\Models\HelpCenter\Article;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function agentWithArticlePermission(): User
{
    $agent = User::factory()->create();
    $agent->permissions()->attach(Permission::create([
        'name' => 'hc-articles',
        'display_name' => 'Help Center Articles',
        'description' => 'Full management of help center articles',
    ]));

    return $agent->load('permissions');
}

describe('article page', function () {
    it('returns 404 for draft articles to guests and clients', function (bool $asClient) {
        $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

        if ($asClient) {
            $this->actingAs(Client::factory()->create(), 'client');
        }

        $this->get(route('articles.show', ['locale' => 'en', 'article' => $article]))
            ->assertNotFound();
    })->with(['guest' => [false], 'client' => [true]]);

    it('shows draft articles to agents', function () {
        $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

        $this->actingAs(User::factory()->create())
            ->get(route('articles.show', ['locale' => 'en', 'article' => $article]))
            ->assertOk()
            ->assertSee($article->title);
    });

    it('shows published articles to guests', function () {
        $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

        $this->get(route('articles.show', ['locale' => 'en', 'article' => $article]))
            ->assertOk()
            ->assertSee($article->title);
    });
});

describe('article publication', function () {
    it('forbids guests and clients from publishing or unpublishing articles', function (string $routeName, bool $asClient) {
        $status = $routeName === 'articles.unpublish' ? ArticleStatus::PUBLISHED : ArticleStatus::DRAFT;
        $article = Article::factory()->create(['status' => $status]);

        if ($asClient) {
            $this->actingAs(Client::factory()->create(), 'client');
        }

        $this->get(route($routeName, ['locale' => 'en', 'article' => $article]))
            ->assertForbidden();

        expect($article->refresh()->status)->toBe($status);
    })->with([
        'guest cannot publish' => ['articles.publish', false],
        'guest cannot unpublish' => ['articles.unpublish', false],
        'client cannot publish' => ['articles.publish', true],
        'client cannot unpublish' => ['articles.unpublish', true],
    ]);

    it('forbids agents without the hc-articles permission from publishing articles', function () {
        $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

        $this->actingAs(User::factory()->create())
            ->get(route('articles.publish', ['locale' => 'en', 'article' => $article]))
            ->assertForbidden();

        expect($article->refresh()->status)->toBe(ArticleStatus::DRAFT);
    });

    it('lets agents with the hc-articles permission publish an article', function () {
        $article = Article::factory()->create(['status' => ArticleStatus::DRAFT]);

        $this->actingAs(agentWithArticlePermission())
            ->get(route('articles.publish', ['locale' => 'en', 'article' => $article]))
            ->assertRedirect();

        expect($article->refresh()->status)->toBe(ArticleStatus::PUBLISHED);
    });

    it('lets agents with the hc-articles permission unpublish an article', function () {
        $article = Article::factory()->create(['status' => ArticleStatus::PUBLISHED]);

        $this->actingAs(agentWithArticlePermission())
            ->get(route('articles.unpublish', ['locale' => 'en', 'article' => $article]))
            ->assertRedirect();

        expect($article->refresh()->status)->toBe(ArticleStatus::DRAFT);
    });
});
