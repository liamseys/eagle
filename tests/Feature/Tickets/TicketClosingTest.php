<?php

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\Tickets\TicketStatus;
use App\Filament\Client\Resources\TicketResource\Pages\ViewTicket;
use App\Filament\Resources\TicketResource\Pages\EditTicket;
use App\Models\Client;
use App\Models\Permission;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketClosed;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

it('offers agents no manual close action on a ticket', function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $agent = User::factory()->create();
    $agent->permissions()->attach(Permission::create([
        'name' => 'tickets',
        'display_name' => 'Tickets',
        'description' => 'Full management of tickets',
    ]));
    $agent->load('permissions');
    $this->actingAs($agent);

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create();

    Livewire::test(EditTicket::class, ['record' => $ticket->getRouteKey()])
        ->assertSuccessful()
        ->assertDontSee(__('Close now'))
        ->assertDontSee(__('Schedule for closing'));
});

it('notifies the requester that their solved ticket was closed because it was solved', function () {
    $ticket = Ticket::factory()->withStatus(TicketStatus::SOLVED)->create();

    app(UpdateTicketStatus::class)->handle($ticket, TicketStatus::CLOSED);

    Notification::assertSentTo($ticket->requester, TicketClosed::class, function (TicketClosed $notification) use ($ticket): bool {
        $introLines = $notification->toMail($ticket->requester)->introLines;

        return in_array(__('Your ticket was marked as solved and has now been automatically closed.'), $introLines, true)
            && ! in_array(__('We noticed that there was no response regarding your ticket, so it has been automatically closed.'), $introLines, true);
    });
});

it('notifies the requester that their unanswered ticket was closed due to no response', function (TicketStatus $status) {
    $ticket = Ticket::factory()->withStatus($status)->create();

    app(UpdateTicketStatus::class)->handle($ticket, TicketStatus::CLOSED);

    Notification::assertSentTo($ticket->requester, TicketClosed::class, function (TicketClosed $notification) use ($ticket): bool {
        $introLines = $notification->toMail($ticket->requester)->introLines;

        return in_array(__('We noticed that there was no response regarding your ticket, so it has been automatically closed.'), $introLines, true);
    });
})->with([
    'pending' => TicketStatus::PENDING,
    'open' => TicketStatus::OPEN,
]);

it('offers requesters no close action on their ticket', function () {
    Filament::setCurrentPanel(Filament::getPanel('client'));

    $client = Client::factory()->create();
    $this->actingAs($client, 'client');

    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'requester_id' => $client->id,
    ]);

    Livewire::test(ViewTicket::class, ['record' => $ticket->getRouteKey()])
        ->assertSuccessful()
        ->assertDontSee(__('Close ticket'));
});
