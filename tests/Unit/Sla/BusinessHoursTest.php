<?php

use App\Support\Sla\BusinessHours;
use Carbon\CarbonImmutable;

/**
 * A standard Mon–Fri 09:00–17:00 week, with the weekend disabled.
 *
 * @param  bool  $weekend  Whether the weekend should be enabled.
 * @return array<string, array{enabled: bool, start: string, end: string}>
 */
function weekSchedule(bool $weekend = false): array
{
    $weekday = ['enabled' => true, 'start' => '09:00', 'end' => '17:00'];
    $off = ['enabled' => $weekend, 'start' => '09:00', 'end' => '17:00'];

    return [
        'monday' => $weekday,
        'tuesday' => $weekday,
        'wednesday' => $weekday,
        'thursday' => $weekday,
        'friday' => $weekday,
        'saturday' => $off,
        'sunday' => $off,
    ];
}

function businessHours(): BusinessHours
{
    return new BusinessHours(weekSchedule(), 'Europe/Brussels');
}

function brussels(string $time): CarbonImmutable
{
    return CarbonImmutable::parse($time, 'Europe/Brussels');
}

it('adds minutes within a single working day', function () {
    expect(businessHours()->addMinutes(brussels('2026-06-08 09:00'), 120)->format('Y-m-d H:i'))
        ->toBe('2026-06-08 11:00');
});

it('rolls over to the next working day when the window is exhausted', function () {
    // 16:00 Monday + 120 min = 1h left Monday, then 1h Tuesday morning.
    expect(businessHours()->addMinutes(brussels('2026-06-08 16:00'), 120)->format('Y-m-d H:i'))
        ->toBe('2026-06-09 10:00');
});

it('skips the weekend', function () {
    // Friday 16:00 + 120 min = 1h Friday, then Monday morning.
    expect(businessHours()->addMinutes(brussels('2026-06-12 16:00'), 120)->format('Y-m-d H:i'))
        ->toBe('2026-06-15 10:00');
});

it('clamps a start time before opening to the start of the working day', function () {
    expect(businessHours()->addMinutes(brussels('2026-06-08 07:00'), 60)->format('Y-m-d H:i'))
        ->toBe('2026-06-08 10:00');
});

it('clamps a weekend start time to the next working day', function () {
    expect(businessHours()->addMinutes(brussels('2026-06-13 12:00'), 60)->format('Y-m-d H:i'))
        ->toBe('2026-06-15 10:00');
});

it('returns the start unchanged when adding no minutes', function () {
    expect(businessHours()->addMinutes(brussels('2026-06-08 09:00'), 0)->format('Y-m-d H:i'))
        ->toBe('2026-06-08 09:00');
});

it('counts business minutes within a working day', function () {
    expect(businessHours()->diffInMinutes(brussels('2026-06-08 09:00'), brussels('2026-06-08 12:00')))
        ->toBe(180);
});

it('counts business minutes across the weekend', function () {
    // Friday 16:00 -> Monday 10:00 = 60 min Friday + 60 min Monday.
    expect(businessHours()->diffInMinutes(brussels('2026-06-12 16:00'), brussels('2026-06-15 10:00')))
        ->toBe(120);
});

it('returns zero business minutes when the end is not after the start', function () {
    expect(businessHours()->diffInMinutes(brussels('2026-06-08 12:00'), brussels('2026-06-08 09:00')))
        ->toBe(0);
});

it('falls back to linear time when no day is open', function () {
    $allClosed = collect(weekSchedule())
        ->map(fn (array $day): array => [...$day, 'enabled' => false])
        ->all();

    $bh = new BusinessHours($allClosed, 'Europe/Brussels');

    expect($bh->addMinutes(brussels('2026-06-13 09:00'), 120)->format('Y-m-d H:i'))->toBe('2026-06-13 11:00')
        ->and($bh->diffInMinutes(brussels('2026-06-13 09:00'), brussels('2026-06-13 12:00')))->toBe(180);
});
