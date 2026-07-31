<?php

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\Client;
use App\Models\Group;
use App\Models\HelpCenter\Article;
use App\Models\HelpCenter\Form;
use App\Models\HelpCenter\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function searchHelpCenter(string $query)
{
    return test()->get(route('search', ['locale' => 'en', 'q' => $query]));
}

it('finds published public articles by title, description, or body', function () {
    Article::factory()->create([
        'title' => 'Resetting your password',
        'status' => ArticleStatus::PUBLISHED,
        'is_public' => true,
    ]);

    Article::factory()->create([
        'title' => 'Security overview',
        'body' => '<p>Includes a section about password rotation.</p>',
        'status' => ArticleStatus::PUBLISHED,
        'is_public' => true,
    ]);

    Article::factory()->create([
        'title' => 'Billing cycles explained',
        'status' => ArticleStatus::PUBLISHED,
        'is_public' => true,
    ]);

    searchHelpCenter('password')
        ->assertOk()
        ->assertSee('Resetting your password')
        ->assertSee('Security overview')
        ->assertDontSee('Billing cycles explained');
});

it('does not return draft or private articles', function () {
    Article::factory()->create([
        'title' => 'Draft password guide',
        'status' => ArticleStatus::DRAFT,
        'is_public' => true,
    ]);

    Article::factory()->create([
        'title' => 'Private password guide',
        'status' => ArticleStatus::PUBLISHED,
        'is_public' => false,
    ]);

    searchHelpCenter('password')
        ->assertOk()
        ->assertDontSee('Draft password guide')
        ->assertDontSee('Private password guide');
});

it('finds active public forms and respects form visibility rules', function () {
    $section = Section::factory()->create();

    Form::factory()->for($section)->create([
        'name' => 'Password reset request',
        'is_public' => true,
        'is_active' => true,
    ]);

    Form::factory()->for($section)->create([
        'name' => 'Password legacy form',
        'is_public' => true,
        'is_active' => false,
    ]);

    $restrictedForm = Form::factory()->for($section)->create([
        'name' => 'Password partner form',
        'is_public' => true,
        'is_active' => true,
    ]);

    $restrictedForm->groups()->attach(Group::factory()->create());

    searchHelpCenter('password')
        ->assertOk()
        ->assertSee('Password reset request')
        ->assertDontSee('Password legacy form')
        ->assertDontSee('Password partner form');
});

it('shows group-restricted forms in results to clients in an allowed group', function () {
    $section = Section::factory()->create();

    $restrictedForm = Form::factory()->for($section)->create([
        'name' => 'Password partner form',
        'is_public' => true,
        'is_active' => true,
    ]);

    $group = Group::factory()->create();
    $restrictedForm->groups()->attach($group);

    $client = Client::factory()->create();
    $client->groups()->attach($group);

    test()->actingAs($client, 'client');

    searchHelpCenter('password')
        ->assertOk()
        ->assertSee('Password partner form');
});

it('treats like wildcards in the query as literal characters', function () {
    Article::factory()->create([
        'title' => 'Alpha guide',
        'status' => ArticleStatus::PUBLISHED,
        'is_public' => true,
    ]);

    searchHelpCenter('%')
        ->assertOk()
        ->assertDontSee('Alpha guide');

    searchHelpCenter('_____')
        ->assertOk()
        ->assertDontSee('Alpha guide');
});

it('shows a prompt when no query is given', function () {
    $this->get(route('search', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('Type a search above to find articles and forms.');
});

it('shows an empty state when nothing matches', function () {
    searchHelpCenter('nonexistent topic')
        ->assertOk()
        ->assertSee('No results found for');
});
