<?php

use App\Ai\Tools\CreateTicketTool;
use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;

uses(RefreshDatabase::class);

it('creates a client, ticket, and comment', function () {
    $tool = new CreateTicketTool;
    $request = new Request([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Cannot access my account',
        'message' => 'I have been unable to log in since yesterday.',
    ]);

    $result = $tool->handle($request);

    $client = Client::where('email', 'jane@example.com')->first();

    expect($client)->not->toBeNull()
        ->and($client->name)->toBe('Jane Doe')
        ->and($client->tickets)->toHaveCount(1);

    $ticket = $client->tickets->first();

    expect($ticket->subject)->toBe('Cannot access my account')
        ->and($ticket->priority)->toBe(TicketPriority::NORMAL)
        ->and($ticket->type)->toBe(TicketType::QUESTION)
        ->and($ticket->comments)->toHaveCount(1);

    $comment = $ticket->comments->first();

    expect($comment->body)->toBe('I have been unable to log in since yesterday.')
        ->and($comment->authorable_type)->toBe(Client::class)
        ->and($comment->authorable_id)->toBe($client->id);

    expect($result)->toContain('created successfully');
});

it('reuses an existing client by email', function () {
    $existingClient = Client::factory()->create([
        'email' => 'existing@example.com',
        'name' => 'Existing User',
    ]);

    $tool = new CreateTicketTool;
    $request = new Request([
        'name' => 'Different Name',
        'email' => 'existing@example.com',
        'subject' => 'New question',
        'message' => 'I have a question.',
    ]);

    $tool->handle($request);

    expect(Client::where('email', 'existing@example.com')->count())->toBe(1);

    $client = Client::where('email', 'existing@example.com')->first();

    expect($client->name)->toBe('Existing User')
        ->and($client->tickets)->toHaveCount(1);
});
