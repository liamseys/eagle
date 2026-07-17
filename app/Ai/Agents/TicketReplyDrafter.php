<?php

namespace App\Ai\Agents;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Support\HtmlToText;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

#[Timeout(60)]
#[Model('gpt-5.4-mini')]
class TicketReplyDrafter implements Agent
{
    use Promptable;

    /**
     * Maximum number of previous replies used to learn the agent's style.
     */
    protected const STYLE_EXAMPLE_LIMIT = 5;

    /**
     * Maximum length of a single style example, in characters.
     */
    protected const STYLE_EXAMPLE_LENGTH = 600;

    public function __construct(
        public Ticket $ticket,
        public User $agent,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return <<<PROMPT
        You draft support replies on behalf of the help desk agent "{$this->agent->name}".

        {$this->styleSection()}

        Guidelines:
        - Draft the next reply from the agent to the customer in the conversation you receive.
        - Closely match the agent's tone, phrasing, greeting, and sign-off from the examples above.
        - Write in the same language the customer used in the conversation.
        - Only use facts present in the ticket details and conversation. Never invent order numbers,
          prices, dates, policies, or commitments.
        - Never reveal or quote internal notes; they are context for you only.
        - If information needed to resolve the ticket is missing, ask the customer a clarifying
          question instead of guessing.
        - Return only the reply body as simple HTML using <p>, <br>, <ul>, <ol>, <li>, <strong>,
          and <em> tags. No markdown, no subject line, and no commentary about the draft.
        PROMPT;
    }

    /**
     * The style portion of the instructions, based on the agent's previous replies.
     */
    protected function styleSection(): string
    {
        $examples = $this->styleExamples();

        if ($examples->isEmpty()) {
            return 'No previous replies from this agent are available, so use a friendly, professional support tone.';
        }

        return "These are previous replies written by the agent. Learn their tone, phrasing, greeting, and sign-off from them:\n\n"
            .$examples
                ->map(fn (string $example, int $index): string => 'Example '.($index + 1).":\n\"\"\"\n{$example}\n\"\"\"")
                ->implode("\n\n");
    }

    /**
     * Recent public replies by this agent on other tickets, as plain text.
     *
     * @return Collection<int, string>
     */
    public function styleExamples(): Collection
    {
        return TicketComment::query()
            ->where('authorable_type', User::class)
            ->where('authorable_id', $this->agent->id)
            ->where('is_public', true)
            ->where('ticket_id', '!=', $this->ticket->id)
            ->latest()
            ->limit(self::STYLE_EXAMPLE_LIMIT)
            ->pluck('body')
            ->map(fn (?string $body): string => Str::limit(HtmlToText::convert($body), self::STYLE_EXAMPLE_LENGTH))
            ->filter()
            ->values();
    }
}
