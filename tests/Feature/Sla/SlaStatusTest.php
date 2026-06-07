<?php

use App\Enums\Tickets\SlaState;
use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Settings\WorkflowSettings;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    config(['app.timezone_display' => 'UTC']);
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 10:00'));
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

/**
 * @param  array<string, mixed>  $sla
 */
function ticketWithSla(array $sla): Ticket
{
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'priority' => TicketPriority::NORMAL,
    ]);

    $ticket->forceFill($sla)->saveQuietly();

    return $ticket->fresh();
}

it('reports the first response state from its deadline', function (string $dueAt, ?string $achievedAt, SlaState $expected) {
    $ticket = ticketWithSla([
        'first_response_due_at' => CarbonImmutable::parse($dueAt),
        'first_responded_at' => $achievedAt ? CarbonImmutable::parse($achievedAt) : null,
    ]);

    expect($ticket->sla()->firstResponse->state)->toBe($expected);
})->with([
    'on track' => ['2026-06-08 13:00', null, SlaState::OnTrack],
    'at risk' => ['2026-06-08 10:30', null, SlaState::AtRisk],
    'breached' => ['2026-06-08 09:00', null, SlaState::Breached],
    'met on time' => ['2026-06-08 10:00', '2026-06-08 09:30', SlaState::Met],
    'met but late counts as breached' => ['2026-06-08 09:00', '2026-06-08 09:30', SlaState::Breached],
]);

it('surfaces the most severe state across targets', function () {
    $ticket = ticketWithSla([
        'first_response_due_at' => CarbonImmutable::parse('2026-06-08 10:00'),
        'first_responded_at' => CarbonImmutable::parse('2026-06-08 09:30'),
        'resolution_due_at' => CarbonImmutable::parse('2026-06-08 09:00'),
        'resolved_at' => null,
    ]);

    expect($ticket->sla()->firstResponse->state)->toBe(SlaState::Met)
        ->and($ticket->sla()->resolution->state)->toBe(SlaState::Breached)
        ->and($ticket->sla()->overallState())->toBe(SlaState::Breached);
});

it('is inactive when SLA tracking is disabled', function () {
    $settings = app(WorkflowSettings::class);
    $settings->sla_enabled = false;
    $settings->save();

    $ticket = ticketWithSla([
        'first_response_due_at' => CarbonImmutable::parse('2026-06-08 13:00'),
    ]);

    expect($ticket->sla()->isActive())->toBeFalse()
        ->and($ticket->sla()->overallState())->toBeNull();
});
