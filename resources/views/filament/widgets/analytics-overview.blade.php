@php
    // Literal classes so Tailwind can detect them; matches the badge colours used elsewhere.
    $barColors = [
        'danger' => 'bg-danger-500',
        'warning' => 'bg-warning-500',
        'success' => 'bg-success-500',
        'info' => 'bg-info-500',
        'primary' => 'bg-primary-500',
        'gray' => 'bg-gray-400',
    ];
@endphp

<x-filament::section>
    <x-slot name="heading">{{ __('Ticket analytics') }}</x-slot>
    <x-slot name="description">{{ __('Volume and distribution over the selected period.') }}</x-slot>

    <div class="@container flex flex-col gap-6">
        {{-- Volume --}}
        <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-xl bg-gray-950/5 ring-1 ring-gray-950/5 @lg:grid-cols-4 dark:bg-white/10 dark:ring-white/10">
            <div class="bg-white p-4 dark:bg-gray-900">
                <dt class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Created') }}</dt>
                <dd class="mt-1 flex items-baseline gap-2">
                    <p class="text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($created) }}</p>

                    @if ($createdTrend !== null)
                        <p @class([
                            'text-xs font-medium tabular-nums',
                            'text-success-600 dark:text-success-400' => $createdTrend > 0,
                            'text-danger-600 dark:text-danger-400' => $createdTrend < 0,
                            'text-gray-400 dark:text-gray-500' => $createdTrend === 0,
                        ])>{{ $createdTrend > 0 ? '+' : '' }}{{ $createdTrend }}%</p>
                    @endif
                </dd>
                <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">
                    {{ $createdTrend !== null ? __('vs previous 30 days') : __('In the selected period') }}
                </p>
            </div>

            <div class="bg-white p-4 dark:bg-gray-900">
                <dt class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Solved') }}</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($solved) }}</dd>
            </div>

            <div class="bg-white p-4 dark:bg-gray-900">
                <dt class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Unsolved') }}</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($unsolved) }}</dd>
            </div>

            <div class="bg-white p-4 dark:bg-gray-900">
                <dt class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Solve rate') }}</dt>
                <dd class="mt-1 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">
                    {{ $solveRate === null ? '—' : $solveRate.'%' }}
                </dd>
            </div>
        </dl>

        {{-- Distribution --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-6 @lg:grid-cols-2">
            @foreach ($distributions as $distribution)
                <div>
                    <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $distribution['title'] }}</p>

                    <dl class="mt-3 flex flex-col gap-3">
                        @foreach ($distribution['rows'] as $row)
                            <div class="flex items-center gap-3">
                                <dt class="w-16 shrink-0 truncate text-sm text-gray-600 dark:text-gray-300">{{ $row['label'] }}</dt>

                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div
                                        class="h-full w-(--bar) rounded-full {{ $barColors[$row['color']] ?? 'bg-gray-400' }}"
                                        style="--bar: {{ $row['pct'] }}%"
                                    ></div>
                                </div>

                                <dd class="w-16 shrink-0 text-right text-sm tabular-nums text-gray-950 dark:text-white">
                                    {{ number_format($row['count']) }}
                                    <span class="text-gray-400 dark:text-gray-500">{{ $row['pct'] }}%</span>
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    </div>
</x-filament::section>
