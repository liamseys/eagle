<?php

use App\Models\Client;
use App\Models\Group;
use App\Models\HelpCenter\Category;
use App\Models\HelpCenter\Form;
use App\Models\HelpCenter\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function categoryWithForms(): array
{
    $category = Category::factory()->create();
    $section = Section::factory()->for($category)->create(['name' => 'Account Section']);

    Form::factory()->for($section)->create([
        'name' => 'Public Contact Form',
        'is_public' => true,
        'is_active' => true,
    ]);

    $restrictedForm = Form::factory()->for($section)->create([
        'name' => 'Restricted Partner Form',
        'is_public' => true,
        'is_active' => true,
    ]);

    $group = Group::factory()->create();
    $restrictedForm->groups()->attach($group);

    return [$category, $group];
}

it('hides group-restricted forms from guests on the category page', function () {
    [$category] = categoryWithForms();

    $this->get(route('categories.show', ['locale' => 'en', 'category' => $category]))
        ->assertOk()
        ->assertSee('Public Contact Form')
        ->assertDontSee('Restricted Partner Form');
});

it('hides group-restricted forms from clients outside the allowed groups', function () {
    [$category] = categoryWithForms();

    $client = Client::factory()->create();

    $this->actingAs($client, 'client')
        ->get(route('categories.show', ['locale' => 'en', 'category' => $category]))
        ->assertOk()
        ->assertSee('Public Contact Form')
        ->assertDontSee('Restricted Partner Form');
});

it('shows group-restricted forms to clients that belong to an allowed group', function () {
    [$category, $group] = categoryWithForms();

    $client = Client::factory()->create();
    $client->groups()->attach($group);

    $this->actingAs($client, 'client')
        ->get(route('categories.show', ['locale' => 'en', 'category' => $category]))
        ->assertOk()
        ->assertSee('Public Contact Form')
        ->assertSee('Restricted Partner Form');
});

it('shows group-restricted forms to agents', function () {
    [$category] = categoryWithForms();

    $this->actingAs(User::factory()->create())
        ->get(route('categories.show', ['locale' => 'en', 'category' => $category]))
        ->assertOk()
        ->assertSee('Public Contact Form')
        ->assertSee('Restricted Partner Form');
});

it('hides sections whose only content is a group-restricted form from guests', function () {
    $category = Category::factory()->create();
    $section = Section::factory()->for($category)->create(['name' => 'Partners Only Section']);

    $restrictedForm = Form::factory()->for($section)->create([
        'name' => 'Restricted Partner Form',
        'is_public' => true,
        'is_active' => true,
    ]);

    $restrictedForm->groups()->attach(Group::factory()->create());

    $this->get(route('categories.show', ['locale' => 'en', 'category' => $category]))
        ->assertOk()
        ->assertDontSee('Partners Only Section')
        ->assertDontSee('Restricted Partner Form');
});
