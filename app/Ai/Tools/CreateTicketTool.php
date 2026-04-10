<?php

namespace App\Ai\Tools;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Models\Client;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class CreateTicketTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Create a support ticket for a user when no relevant Help Center articles are found. Requires the user\'s name, email, a concise subject, and their message.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        $client = Client::firstOrCreate(
            ['email' => $request['email']],
            [
                'name' => $request['name'],
                'password' => bcrypt(Str::random(32)),
            ]
        );

        $ticket = $client->tickets()->create([
            'subject' => $request['subject'],
            'priority' => TicketPriority::NORMAL,
            'type' => TicketType::QUESTION,
        ]);

        $ticket->comments()->create([
            'authorable_type' => Client::class,
            'authorable_id' => $client->id,
            'body' => $request['message'],
        ]);

        return "Support ticket #{$ticket->ticket_id} has been created successfully. The team will be in touch shortly.";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema
                ->string()
                ->description('The full name of the user.')
                ->required(),
            'email' => $schema
                ->string()
                ->description('The email address of the user.')
                ->required(),
            'subject' => $schema
                ->string()
                ->description('A concise subject line summarizing the user\'s issue, generated from their message.')
                ->required(),
            'message' => $schema
                ->string()
                ->description('The full message from the user describing their issue or question.')
                ->required(),
        ];
    }
}
