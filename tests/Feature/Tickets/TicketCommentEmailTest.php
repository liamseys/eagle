<?php

use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCommentByAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

it('renders the agent reply email with only the reply content', function () {
    $client = Client::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $client->id]);

    $comment = $ticket->comments()->create([
        'authorable_type' => User::class,
        'authorable_id' => User::factory()->create()->id,
        'body' => '<p>Here is the specific agent reply body.</p>',
        'is_public' => true,
    ]);
    $comment->setRelation('ticket', $ticket);

    $mail = (new TicketCommentByAgent($comment))->toMail($client);
    $html = (string) app(Markdown::class)->render($mail->markdown, $mail->data());

    expect($html)
        ->toContain('Here is the specific agent reply body.')
        ->not->toContain('Hello!')
        ->not->toContain('Regards');
});
