<?php

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Models\Client;
use App\Models\Group;
use App\Models\HelpCenter\Form;
use App\Models\HelpCenter\Section;
use App\Models\Permission;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function accessTestForm(array $attributes = [], ?Group $group = null): Form
{
    $form = Form::factory()->for(Section::factory())->create([
        'is_public' => true,
        'is_active' => true,
        'is_embeddable' => false,
        'default_group_id' => Group::factory(),
        'default_ticket_priority' => TicketPriority::NORMAL,
        'default_ticket_type' => TicketType::QUESTION,
        'settings' => [],
        ...$attributes,
    ]);

    if ($group) {
        $form->groups()->attach($group);
    }

    return $form;
}

function agentWithFormPermission(): User
{
    $agent = User::factory()->create();
    $agent->permissions()->attach(Permission::create([
        'name' => 'hc-forms',
        'display_name' => 'Help Center Forms',
        'description' => 'Full management of help center forms',
    ]));

    return $agent->load('permissions');
}

describe('form page', function () {
    it('returns 404 for deactivated forms, even for agents and allowed clients', function (?string $viewer) {
        $group = Group::factory()->create();
        $form = accessTestForm(['is_active' => false], $group);

        if ($viewer === 'agent') {
            $this->actingAs(agentWithFormPermission());
        }

        if ($viewer === 'client') {
            $client = Client::factory()->create();
            $client->groups()->attach($group);
            $this->actingAs($client, 'client');
        }

        $this->get(route('forms.show', ['locale' => 'en', 'form' => $form]))
            ->assertNotFound();
    })->with(['guest' => [null], 'agent' => ['agent'], 'client in allowed group' => ['client']]);

    it('redirects guests and clients outside the allowed groups away from restricted forms', function (bool $asClient) {
        $form = accessTestForm(group: Group::factory()->create());

        if ($asClient) {
            $this->actingAs(Client::factory()->create(['locale' => 'en']), 'client');
        }

        $this->get(route('forms.show', ['locale' => 'en', 'form' => $form]))
            ->assertRedirect(route('index', ['locale' => 'en']));
    })->with(['guest' => [false], 'client outside groups' => [true]]);

    it('shows restricted forms to clients in an allowed group and to agents', function (string $viewer) {
        $group = Group::factory()->create();
        $form = accessTestForm(group: $group);

        if ($viewer === 'agent') {
            $this->actingAs(User::factory()->create());
        } else {
            $client = Client::factory()->create();
            $client->groups()->attach($group);
            $this->actingAs($client, 'client');
        }

        $this->get(route('forms.show', ['locale' => 'en', 'form' => $form]))
            ->assertOk()
            ->assertSee($form->name);
    })->with(['agent' => ['agent'], 'client in allowed group' => ['client']]);

    it('shows active unrestricted forms to guests', function () {
        $form = accessTestForm();

        $this->get(route('forms.show', ['locale' => 'en', 'form' => $form]))
            ->assertOk()
            ->assertSee($form->name);
    });
});

describe('form submission', function () {
    it('rejects submissions to deactivated forms', function () {
        $form = accessTestForm(['is_active' => false]);

        $this->post(route('forms.submit', ['locale' => 'en']), ['form_id' => $form->id])
            ->assertNotFound();

        expect(Ticket::withoutGlobalScopes()->count())->toBe(0);
    });

    it('rejects submissions to restricted forms from guests and clients outside the allowed groups', function (bool $asClient) {
        $form = accessTestForm(group: Group::factory()->create());

        if ($asClient) {
            $this->actingAs(Client::factory()->create(), 'client');
        }

        $this->post(route('forms.submit', ['locale' => 'en']), ['form_id' => $form->id])
            ->assertNotFound();

        expect(Ticket::withoutGlobalScopes()->count())->toBe(0);
    })->with(['guest' => [false], 'client outside groups' => [true]]);

    it('accepts submissions to restricted forms from clients in an allowed group', function () {
        $group = Group::factory()->create();
        $form = accessTestForm(group: $group);

        $client = Client::factory()->create();
        $client->groups()->attach($group);

        $this->actingAs($client, 'client')
            ->post(route('forms.submit', ['locale' => 'en']), ['form_id' => $form->id])
            ->assertRedirect();

        expect(Ticket::withoutGlobalScopes()->count())->toBe(1);
    });

    it('accepts submissions to active unrestricted forms from guests', function () {
        $form = accessTestForm();

        $this->post(route('forms.submit', ['locale' => 'en']), ['form_id' => $form->id])
            ->assertRedirect();

        expect(Ticket::withoutGlobalScopes()->count())->toBe(1);
    });
});

describe('form embed', function () {
    it('returns 404 for deactivated embeddable forms, even for agents', function (bool $asAgent) {
        $form = accessTestForm(['is_embeddable' => true, 'is_active' => false]);

        if ($asAgent) {
            $this->actingAs(agentWithFormPermission());
        }

        $this->get(route('forms.embed', ['locale' => 'en', 'form' => $form]))
            ->assertNotFound();
    })->with(['guest' => [false], 'agent' => [true]]);

    it('returns 404 for restricted embeddable forms unless the viewer is allowed', function () {
        $group = Group::factory()->create();
        $form = accessTestForm(['is_embeddable' => true], $group);

        $this->get(route('forms.embed', ['locale' => 'en', 'form' => $form]))
            ->assertNotFound();

        $client = Client::factory()->create();
        $client->groups()->attach($group);

        $this->actingAs($client, 'client')
            ->get(route('forms.embed', ['locale' => 'en', 'form' => $form]))
            ->assertOk();
    });

    it('returns 404 for non-embeddable forms and 200 for embeddable active forms', function () {
        $embeddable = accessTestForm(['is_embeddable' => true]);
        $notEmbeddable = accessTestForm();

        $this->get(route('forms.embed', ['locale' => 'en', 'form' => $embeddable]))
            ->assertOk();

        $this->get(route('forms.embed', ['locale' => 'en', 'form' => $notEmbeddable]))
            ->assertNotFound();
    });
});

describe('form activation', function () {
    it('forbids guests and clients from toggling forms', function (string $routeName, bool $asClient) {
        $form = accessTestForm(['is_active' => $routeName === 'forms.deactivate']);

        if ($asClient) {
            $this->actingAs(Client::factory()->create(), 'client');
        }

        $this->get(route($routeName, ['locale' => 'en', 'form' => $form]))
            ->assertForbidden();

        expect($form->refresh()->is_active)->toBe($routeName === 'forms.deactivate');
    })->with([
        'guest cannot activate' => ['forms.activate', false],
        'guest cannot deactivate' => ['forms.deactivate', false],
        'client cannot activate' => ['forms.activate', true],
        'client cannot deactivate' => ['forms.deactivate', true],
    ]);

    it('forbids agents without the hc-forms permission from toggling forms', function () {
        $form = accessTestForm();

        $this->actingAs(User::factory()->create())
            ->get(route('forms.deactivate', ['locale' => 'en', 'form' => $form]))
            ->assertForbidden();

        expect($form->refresh()->is_active)->toBeTrue();
    });

    it('lets agents with the hc-forms permission deactivate a form', function () {
        $form = accessTestForm();

        $this->actingAs(agentWithFormPermission())
            ->get(route('forms.deactivate', ['locale' => 'en', 'form' => $form]))
            ->assertRedirect(route('index', ['locale' => 'en']));

        expect($form->refresh()->is_active)->toBeFalse();
    });

    it('lets agents with the hc-forms permission activate a form', function () {
        $form = accessTestForm(['is_active' => false]);

        $this->actingAs(agentWithFormPermission())
            ->get(route('forms.activate', ['locale' => 'en', 'form' => $form]))
            ->assertRedirect(route('forms.show', ['locale' => 'en', 'form' => $form]));

        expect($form->refresh()->is_active)->toBeTrue();
    });
});
