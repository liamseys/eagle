<?php

namespace Database\Seeders;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Sla\BusinessHours;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Background volume with assorted (mostly older) timestamps.
        Ticket::factory()->count(15)->create();

        $this->seedSlaShowcase();
    }

    /**
     * Seed a deterministic spread of tickets so every SLA state is visible:
     * on track, approaching breach, breached, and met.
     */
    private function seedSlaShowcase(): void
    {
        $agent = User::query()->where('email', 'test@example.com')->first()
            ?? User::factory()->create();

        $groupId = $agent->groups()->value('id');

        $businessHours = app(BusinessHours::class);

        // A future deadline N business minutes from now, in the storage timezone.
        $in = fn (int $minutes) => $businessHours->addMinutes(now(), $minutes)->setTimezone(config('app.timezone'));

        // 1. Both targets comfortably on track.
        $this->make($agent, $groupId, 'Unable to reset my password', TicketPriority::NORMAL, TicketStatus::OPEN, now(), [
            'first_response_due_at' => $in(240),
            'resolution_due_at' => $in(1440),
        ]);

        // 2. First response approaching breach, resolution still on track.
        $this->make($agent, $groupId, 'Checkout fails with a 500 error', TicketPriority::HIGH, TicketStatus::OPEN, now()->subHour(), [
            'first_response_due_at' => $in(15),
            'resolution_due_at' => $in(420),
        ]);

        // 3. First response already breached (unanswered), resolution approaching.
        $this->make($agent, $groupId, 'Production API is completely down', TicketPriority::URGENT, TicketStatus::NEW, now()->subHours(3), [
            'first_response_due_at' => now()->subMinutes(90),
            'resolution_due_at' => $in(30),
        ]);

        // 4. Responded on time, resolution on track.
        $this->make($agent, $groupId, 'Question about my latest invoice', TicketPriority::NORMAL, TicketStatus::PENDING, now()->subDay(), [
            'first_responded_at' => now()->subHours(2),
            'first_response_due_at' => now()->subHour(),
            'resolution_due_at' => $in(1440),
        ]);

        // 5. Responded late (first response breached), resolution approaching breach.
        $this->make($agent, $groupId, 'Export button does nothing', TicketPriority::HIGH, TicketStatus::PENDING, now()->subDays(4), [
            'first_responded_at' => now()->subMinutes(30),
            'first_response_due_at' => now()->subHours(2),
            'resolution_due_at' => $in(60),
        ]);

        // 6. Resolution breached (first response met).
        $this->make($agent, $groupId, 'Refund has still not arrived', TicketPriority::NORMAL, TicketStatus::PENDING, now()->subDays(4), [
            'first_responded_at' => now()->subDays(2),
            'first_response_due_at' => now()->subDay(),
            'resolution_due_at' => now()->subHours(3),
        ]);

        // 7. Fully met: solved within both targets.
        $this->make($agent, $groupId, 'Thanks, my issue is resolved', TicketPriority::LOW, TicketStatus::SOLVED, now()->subDays(4), [
            'first_responded_at' => now()->subDays(3),
            'first_response_due_at' => now()->subDays(2),
            'resolved_at' => now()->subDay(),
            'resolution_due_at' => now()->subHours(2),
        ]);
    }

    /**
     * Create a showcase ticket and stamp its SLA tracking columns directly.
     *
     * @param  array<string, mixed>  $sla
     */
    private function make(User $agent, ?string $groupId, string $subject, TicketPriority $priority, TicketStatus $status, \DateTimeInterface $createdAt, array $sla): void
    {
        $ticket = Ticket::factory()->create([
            'subject' => $subject,
            'priority' => $priority,
            'status' => $status,
            'assignee_id' => $agent->id,
            'group_id' => $groupId,
            'created_at' => $createdAt,
        ]);

        $ticket->forceFill($sla)->saveQuietly();
    }
}
