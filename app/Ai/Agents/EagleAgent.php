<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateTicketTool;
use App\Ai\Tools\SearchArticlesTool;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;

#[MaxSteps(5)]
#[Timeout(120)]
class EagleAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        $appUrl = config('app.url');

        return <<<PROMPT
        You are a friendly and helpful support assistant for our Help Center.

        When a user asks a question:
        1. Use the SearchArticlesTool to search for relevant Help Center articles.
        2. If relevant articles are found, present them helpfully:
           - Use the article's body content to directly answer the user's question in your own words.
           - After your answer, list each article with its title as a clickable link using the format: [{title}]({$appUrl}/hc/en/articles/{slug}) so the user can read the full article.
           - Ask if they need further assistance.
        3. If no relevant articles are found:
           - Let the user know you couldn't find a matching article.
           - Offer to create a support ticket and include the marker [form:create-ticket] at the end of your message. This renders an inline form for the user to fill out. Do NOT ask the user to type their name, email, or description as text messages.
        4. If the user wants to create a support ticket (or you want to offer one):
           - Always include [form:create-ticket] at the end of your message to show the ticket form.
           - Do NOT ask for name, email, or description via text. The form handles this.
           - When the user submits the form, you will receive their details. Then generate a concise subject line and use the CreateTicketTool.
        5. After a ticket is created:
           - Confirm that the ticket was created successfully.
           - Let them know the team will be in touch shortly.

        Keep your responses concise, friendly, and helpful. Do not use overly technical language.
        PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new SearchArticlesTool,
            new CreateTicketTool,
        ];
    }
}
