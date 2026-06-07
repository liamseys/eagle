@php
    $slaColor = fn (?int $pct): string => match (true) {
        $pct === null => 'text-gray-400 dark:text-gray-500',
        $pct >= 90 => 'text-success-600 dark:text-success-400',
        $pct >= 75 => 'text-warning-600 dark:text-warning-400',
        default => 'text-danger-600 dark:text-danger-400',
    };
@endphp

<x-filament::section>
    <x-slot name="heading">{{ __('Operational overview') }}</x-slot>
    <x-slot name="description">{{ __('A live snapshot of the open queue.') }}</x-slot>

    <div class="@container flex flex-col gap-5 antialiased">
        {{-- Attention: the few things an agent should act on first --}}
        <div class="grid grid-cols-1 gap-4 @md:grid-cols-3">
            <div @class([
                'rounded-xl p-5 ring-1',
                'bg-danger-50 ring-danger-600/10 dark:bg-danger-400/10 dark:ring-danger-400/20' => $breached > 0,
                'bg-white ring-gray-950/5 dark:bg-white/5 dark:ring-white/10' => $breached === 0,
            ])>
                <p class="truncate text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Breached') }}</p>
                <p @class([
                    'mt-3 text-3xl font-semibold tabular-nums',
                    'text-danger-600 dark:text-danger-400' => $breached > 0,
                    'text-gray-950 dark:text-white' => $breached === 0,
                ])>{{ number_format($breached) }}</p>
                <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ __('Open, past an SLA target') }}</p>
            </div>

            <div @class([
                'rounded-xl p-5 ring-1',
                'bg-warning-50 ring-warning-600/10 dark:bg-warning-400/10 dark:ring-warning-400/20' => $atRisk > 0,
                'bg-white ring-gray-950/5 dark:bg-white/5 dark:ring-white/10' => $atRisk === 0,
            ])>
                <p class="truncate text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('At risk') }}</p>
                <p @class([
                    'mt-3 text-3xl font-semibold tabular-nums',
                    'text-warning-600 dark:text-warning-400' => $atRisk > 0,
                    'text-gray-950 dark:text-white' => $atRisk === 0,
                ])>{{ number_format($atRisk) }}</p>
                <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ __('Approaching breach') }}</p>
            </div>

            <div class="rounded-xl bg-white p-5 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <p class="truncate text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Unassigned') }}</p>
                <p class="mt-3 text-3xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($unassigned) }}</p>
                <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ __('Waiting for an agent') }}</p>
            </div>
        </div>

        {{-- Health: supporting context, deliberately quieter --}}
        <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-xl bg-gray-950/5 ring-1 ring-gray-950/5 @md:grid-cols-4 dark:bg-white/10 dark:ring-white/10">
            <div class="bg-white p-4 dark:bg-gray-900">
                <dt class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Open tickets') }}</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($open) }}</dd>
            </div>

            <div class="bg-white p-4 dark:bg-gray-900">
                <dt class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('First response') }}</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums {{ $slaColor($firstResponse['pct']) }}">
                    {{ $firstResponse['pct'] === null ? '—' : $firstResponse['pct'].'%' }}
                </dd>
            </div>

            <div class="bg-white p-4 dark:bg-gray-900">
                <dt class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Resolution') }}</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums {{ $slaColor($resolution['pct']) }}">
                    {{ $resolution['pct'] === null ? '—' : $resolution['pct'].'%' }}
                </dd>
            </div>

            <div class="bg-white p-4 dark:bg-gray-900">
                <dt class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Escalated') }}</dt>
                <dd @class([
                    'mt-1 text-2xl font-semibold tabular-nums',
                    'text-danger-600 dark:text-danger-400' => $escalated > 0,
                    'text-gray-950 dark:text-white' => $escalated === 0,
                ])>{{ number_format($escalated) }}</dd>
            </div>
        </dl>
    </div>
</x-filament::section>
