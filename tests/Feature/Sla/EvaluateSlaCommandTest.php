<?php

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaApproachingBreach;
use App\Notifications\SlaBreached;
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
function assignedTicketWithSla(User $assignee, array $sla): Ticket
{
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create([
        'assignee_id' => $assignee->id,
        'priority' => TicketPriority::NORMAL,
    ]);

    $ticket->forceFill($sla)->saveQuietly();

    return $ticket;
}

it('notifies the assignee once when a target is breached', function () {
    $assignee = User::factory()->create();
    assignedTicketWithSla($assignee, [
        'first_response_due_at' => CarbonImmutable::parse('2026-06-08 09:00'),
        'resolution_due_at' => CarbonImmutable::parse('2026-06-08 16:00'),
        'sla_alerts' => null,
    ]);

    $this->artisan('sla:evaluate')->assertSuccessful();
    $this->artisan('sla:evaluate')->assertSuccessful();

    Notification::assertSentTo($assignee, SlaBreached::class);
    Notification::assertSentTimes(SlaBreached::class, 1);
});

it('notifies the assignee when a target is approaching breach', function () {
    $assignee = User::factory()->create();
    assignedTicketWithSla($assignee, [
        'first_response_due_at' => CarbonImmutable::parse('2026-06-08 10:30'),
        'resolution_due_at' => CarbonImmutable::parse('2026-06-08 16:00'),
        'sla_alerts' => null,
    ]);

    $this->artisan('sla:evaluate')->assertSuccessful();

    Notification::assertSentTo($assignee, SlaApproachingBreach::class);
});

it('does nothing when SLA tracking is disabled', function () {
    $settings = app(WorkflowSettings::class);
    $settings->sla_enabled = false;
    $settings->save();

    $assignee = User::factory()->create();
    assignedTicketWithSla($assignee, [
        'first_response_due_at' => CarbonImmutable::parse('2026-06-08 09:00'),
        'sla_alerts' => null,
    ]);

    $this->artisan('sla:evaluate')->assertSuccessful();

    Notification::assertNotSentTo($assignee, SlaBreached::class);
    Notification::assertNotSentTo($assignee, SlaApproachingBreach::class);
});
