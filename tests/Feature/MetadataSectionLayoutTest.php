<?php

use App\Filament\Clusters\HelpCenter\Resources\ArticleResource\Pages\EditArticle;
use App\Filament\Clusters\HelpCenter\Resources\FormResource\Pages\EditForm;
use App\Filament\Clusters\Settings\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\ClientResource\Pages\EditClient;
use App\Filament\Resources\TicketResource\Pages\EditTicket;
use App\Models\Client;
use App\Models\HelpCenter\Article;
use App\Models\HelpCenter\Form;
use App\Models\Permission;
use App\Models\Ticket;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

dataset('metadata sections', [
    'ticket' => [EditTicket::class, Ticket::class, 'tickets', false],
    'client' => [EditClient::class, Client::class, 'clients', false],
    'user' => [EditUser::class, User::class, 'settings', false],
    'article' => [EditArticle::class, Article::class, 'hc-articles', true],
    'form' => [EditForm::class, Form::class, 'hc-forms', true],
]);

it('shows created at and updated at side by side in the metadata section', function (
    string $page,
    string $model,
    string $permission,
    bool $hasCreatedBy,
) {
    $agent = User::factory()->create();
    $agent->permissions()->attach(Permission::create([
        'name' => $permission,
        'display_name' => $permission,
        'description' => $permission,
    ]));
    $this->actingAs($agent->load('permissions'));

    $record = $model::factory()->create();

    $component = Livewire::test($page, ['record' => $record->getRouteKey()])
        ->assertSuccessful();

    $schema = $component->instance()->getSchema('form');

    $section = $schema->getComponent(
        fn ($component): bool => $component instanceof Section && $component->getHeading() === 'Metadata',
    );

    expect($section)->not->toBeNull()
        ->and($section->getColumns('lg'))->toBe(2);

    $placeholder = fn (string $name) => $section->getChildSchema()->getComponent(
        fn ($component): bool => $component instanceof Placeholder && $component->getName() === $name,
    );

    // Both timestamps keep their default single-column span, so with the
    // section's two-column grid they share one row.
    expect($placeholder('created_at'))->not->toBeNull()
        ->and($placeholder('created_at')->getColumnSpan('default'))->not->toBe('full')
        ->and($placeholder('updated_at'))->not->toBeNull()
        ->and($placeholder('updated_at')->getColumnSpan('default'))->not->toBe('full');

    if ($hasCreatedBy) {
        expect($placeholder('created_by')->getColumnSpan('default'))->toBe('full');
    }
})->with('metadata sections');
