<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateTicketTool;
use App\Ai\Tools\SearchArticlesTool;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Middleware\RememberConversation;
use Laravel\Ai\Promptable;

#[MaxSteps(5)]
#[Timeout(120)]
class EagleAgent implements Agent, Conversational, HasMiddleware, HasTools
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
           - List each article with its title as a clickable link using the format: [{title}]({$appUrl}/hc/en/articles/{slug})
           - Briefly explain how each article relates to the user's question.
           - Ask if any of the articles help or if they need further assistance.
        3. If no relevant articles are found:
           - Let the user know you couldn't find a matching article.
           - Offer to create a support ticket so the team can help them directly.
        4. If the user wants to create a support ticket:
           - You need their name, email address, and a description of their issue.
           - If any of this information was already provided in the conversation context (e.g. from eagleSettings), do NOT ask for it again. Use what you already know.
           - Generate a concise, descriptive subject line from their message.
           - Use the CreateTicketTool with all the collected information.
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

    /**
     * Get the agent's prompt middleware.
     */
    public function middleware(): array
    {
        return [
            new RememberConversation,
        ];
    }
}
