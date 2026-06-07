<?php

use App\Enums\Tickets\TicketStatus;
use App\Filament\Widgets\OpenTicketsByAgentChart;
use App\Filament\Widgets\SlaAttentionTable;
use App\Filament\Widgets\SlaPerformanceOverview;
use App\Filament\Widgets\WorkloadOverview;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    config(['app.timezone_display' => 'UTC']);
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 12:00'));
    Filament::setCurrentPanel(Filament::getPanel('app'));
    $this->actingAs(User::factory()->create());
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

/**
 * @param  array<string, mixed>  $attributes
 * @param  array<string, mixed>  $sla
 */
function openTicket(array $attributes = [], array $sla = []): Ticket
{
    $ticket = Ticket::factory()->withStatus(TicketStatus::OPEN)->create($attributes);

    if ($sla !== []) {
        $ticket->forceFill($sla)->saveQuietly();
    }

    return $ticket;
}

it('renders the SLA performance widget', function () {
    Livewire::test(SlaPerformanceOverview::class)
        ->assertSuccessful()
        ->assertSee('First response SLA')
        ->assertSee('Resolution SLA');
});

it('lists overdue tickets in the needs-attention table and excludes on-track ones', function () {
    $breached = openTicket(['subject' => 'Overdue ticket'], [
        'first_response_due_at' => CarbonImmutable::parse('2026-06-08 09:00'),
        'resolution_due_at' => CarbonImmutable::parse('2026-06-10 17:00'),
    ]);

    $onTrack = openTicket(['subject' => 'Future ticket'], [
        'first_response_due_at' => CarbonImmutable::parse('2026-06-30 09:00'),
        'resolution_due_at' => CarbonImmutable::parse('2026-07-15 17:00'),
    ]);

    Livewire::test(SlaAttentionTable::class)
        ->assertCanSeeTableRecords([$breached])
        ->assertCanNotSeeTableRecords([$onTrack]);
});

it('reports the open workload', function () {
    openTicket(['assignee_id' => null]);
    openTicket(['assignee_id' => User::factory()->create()->id]);

    Livewire::test(WorkloadOverview::class)
        ->assertSuccessful()
        ->assertSee('Open tickets')
        ->assertSee('Unassigned');
});

it('renders the open tickets by agent chart', function () {
    openTicket();

    Livewire::test(OpenTicketsByAgentChart::class)->assertSuccessful();
});

it('renders the full dashboard page with every widget', function () {
    openTicket(['first_response_due_at' => CarbonImmutable::parse('2026-06-08 09:00')]);

    Livewire::test(App\Filament\Pages\Dashboard::class)->assertSuccessful();
});
