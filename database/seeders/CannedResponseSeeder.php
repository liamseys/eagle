<?php

namespace Database\Seeders;

use App\Models\CannedResponse;
use App\Models\CannedResponseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class CannedResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::query()->where('email', 'test@example.com')->first()
            ?? User::query()->orderBy('created_at')->first()
            ?? User::factory()->create();

        $categories = collect([
            'Greetings',
            'Billing',
            'Account & Access',
            'Troubleshooting',
            'Closing',
        ])->mapWithKeys(fn (string $name): array => [
            $name => CannedResponseCategory::query()->firstOrCreate(['name' => $name]),
        ]);

        foreach ($this->responses() as $row) {
            CannedResponse::query()->updateOrCreate(
                [
                    'user_id' => $owner->id,
                    'title' => $row['title'],
                ],
                [
                    'canned_response_category_id' => $categories[$row['category']]->id,
                    'content' => $row['content'],
                    'is_shared' => $row['is_shared'],
                ],
            );
        }
    }

    /**
     * @return array<int, array{title: string, category: string, is_shared: bool, content: string}>
     */
    private function responses(): array
    {
        $clientName = '<span data-type="mergeTag" data-id="client.name"></span>';
        $agentName = '<span data-type="mergeTag" data-id="agent.name"></span>';
        $ticketId = '<span data-type="mergeTag" data-id="ticket.id"></span>';
        $ticketSubject = '<span data-type="mergeTag" data-id="ticket.subject"></span>';
        $ticketStatus = '<span data-type="mergeTag" data-id="ticket.status"></span>';
        $today = '<span data-type="mergeTag" data-id="today"></span>';

        return [
            [
                'title' => 'Welcome — first contact',
                'category' => 'Greetings',
                'is_shared' => true,
                'content' => "<p>Hi {$clientName},</p><p>Thanks for reaching out about \"{$ticketSubject}\". I'll be looking after ticket #{$ticketId} for you and will get back with an update shortly.</p><p>Best,<br>{$agentName}</p>",
            ],
            [
                'title' => 'Acknowledge & set expectations',
                'category' => 'Greetings',
                'is_shared' => true,
                'content' => "<p>Hi {$clientName},</p><p>Just confirming we've received your message — current status is <strong>{$ticketStatus}</strong>. We aim to come back to you within one business day.</p><p>Talk soon,<br>{$agentName}</p>",
            ],
            [
                'title' => 'Refund — confirmation',
                'category' => 'Billing',
                'is_shared' => true,
                'content' => "<p>Hi {$clientName},</p><p>Good news — your refund has been processed today ({$today}). Depending on your bank it should land in your account within 3–5 working days.</p><p>Let us know if anything looks off,<br>{$agentName}</p>",
            ],
            [
                'title' => 'Invoice copy attached',
                'category' => 'Billing',
                'is_shared' => true,
                'content' => "<p>Hi {$clientName},</p><p>I've attached a fresh copy of your invoice. If you need it reissued in another currency or with a different billing address, just reply and let me know.</p><p>Thanks,<br>{$agentName}</p>",
            ],
            [
                'title' => 'Password reset instructions',
                'category' => 'Account & Access',
                'is_shared' => true,
                'content' => "<p>Hi {$clientName},</p><p>To reset your password:</p><ol><li>Go to the sign-in page and click <em>Forgot password?</em></li><li>Enter the email address on your account.</li><li>Open the email we send you and follow the link within 30 minutes.</li></ol><p>If the email doesn't arrive, check spam first, then reply here and I'll help.</p><p>Cheers,<br>{$agentName}</p>",
            ],
            [
                'title' => 'Two-factor reset',
                'category' => 'Account & Access',
                'is_shared' => true,
                'content' => "<p>Hi {$clientName},</p><p>I can reset your two-factor authentication for ticket #{$ticketId}, but I'll need to verify your identity first. Could you reply with the email address on your account and the last invoice number you received?</p><p>Thanks,<br>{$agentName}</p>",
            ],
            [
                'title' => 'Ask for reproduction steps',
                'category' => 'Troubleshooting',
                'is_shared' => true,
                'content' => "<p>Hi {$clientName},</p><p>To narrow this down, could you share:</p><ul><li>The exact steps that trigger the issue</li><li>The browser and device you're using</li><li>A screenshot or screen recording if possible</li></ul><p>That'll help us reproduce it on our side.</p><p>Thanks,<br>{$agentName}</p>",
            ],
            [
                'title' => 'Escalating to engineering',
                'category' => 'Troubleshooting',
                'is_shared' => false,
                'content' => "<p>Hi {$clientName},</p><p>Thanks for the detail. I've escalated this to our engineering team under ticket #{$ticketId} — current status is <strong>{$ticketStatus}</strong>. I'll come back to you as soon as I have an update.</p><p>{$agentName}</p>",
            ],
            [
                'title' => 'Resolved — ask to confirm',
                'category' => 'Closing',
                'is_shared' => true,
                'content' => "<p>Hi {$clientName},</p><p>I believe we've sorted out \"{$ticketSubject}\". Could you take a quick look on your side and let me know it's working as expected? I'll keep the ticket open for 48 hours just in case.</p><p>Cheers,<br>{$agentName}</p>",
            ],
            [
                'title' => 'Closing — no response',
                'category' => 'Closing',
                'is_shared' => true,
                'content' => "<p>Hi {$clientName},</p><p>I haven't heard back on ticket #{$ticketId} so I'll close it for now. If you'd still like a hand, just reply and we'll pick it back up.</p><p>All the best,<br>{$agentName}</p>",
            ],
        ];
    }
}
