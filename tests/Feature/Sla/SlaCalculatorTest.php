<?php

use App\Enums\Tickets\TicketPriority;
use App\Models\Ticket;
use App\Services\Sla\SlaCalculator;
use App\Settings\WorkflowSettings;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    // Interpret business hours in UTC so fixtures line up with how Eloquent stores
    // timestamps; the timezone itself is exercised in the BusinessHours unit test.
    config(['app.timezone_display' => 'UTC']);
});

/**
 * Build a ticket created at a fixed Monday 09:00 for the given priority.
 */
function ticketCreatedMondayMorning(TicketPriority $priority): Ticket
{
    return Ticket::factory()->create([
        'priority' => $priority,
        'created_at' => CarbonImmutable::parse('2026-06-08 09:00'),
    ]);
}

it('computes the first response deadline in business hours per priority', function (TicketPriority $priority, string $expected) {
    $due = app(SlaCalculator::class)->firstResponseDueAt(ticketCreatedMondayMorning($priority));

    expect($due->format('Y-m-d H:i'))->toBe($expected);
})->with([
    'urgent (60m)' => [TicketPriority::URGENT, '2026-06-08 10:00'],
    'high (120m)' => [TicketPriority::HIGH, '2026-06-08 11:00'],
    'normal (240m)' => [TicketPriority::NORMAL, '2026-06-08 13:00'],
]);

it('computes the resolution deadline across multiple business days', function () {
    // Normal resolution target is 1440 business minutes = 3 working days of 8h.
    $due = app(SlaCalculator::class)->resolutionDueAt(ticketCreatedMondayMorning(TicketPriority::NORMAL));

    expect($due->format('Y-m-d H:i'))->toBe('2026-06-10 17:00');
});

it('returns no deadlines when SLA tracking is disabled', function () {
    $settings = app(WorkflowSettings::class);
    $settings->sla_enabled = false;
    $settings->save();

    $ticket = ticketCreatedMondayMorning(TicketPriority::NORMAL);

    expect(app(SlaCalculator::class)->firstResponseDueAt($ticket))->toBeNull()
        ->and(app(SlaCalculator::class)->resolutionDueAt($ticket))->toBeNull();
});
