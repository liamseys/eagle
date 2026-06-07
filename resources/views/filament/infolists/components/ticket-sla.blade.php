@php
    $sla = $getRecord()->sla();
@endphp

<x-filament::section>
    <x-slot name="heading">{{ __('SLA') }}</x-slot>

    <div class="flex flex-col space-y-4">
        @foreach ($sla->metrics() as $metric)
            <div class="flex items-start justify-between gap-3">
                <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-950 dark:text-white">
                        {{ $metric->label }}
                    </span>

                    <time
                        datetime="{{ ($metric->achievedAt ?? $metric->dueAt)->toIso8601String() }}"
                        title="{{ ($metric->achievedAt ?? $metric->dueAt)->translatedFormat('M j, Y · g:i A') }}"
                        class="text-xs text-gray-500 dark:text-gray-400"
                    >
                        @if ($metric->isAchieved())
                            {{ __('Completed :time', ['time' => $metric->achievedAt->diffForHumans()]) }}
                        @else
                            {{ __('Due :time', ['time' => $metric->dueAt->diffForHumans()]) }}
                        @endif
                    </time>
                </div>

                <x-filament::badge :color="$metric->state->getColor()" :icon="$metric->state->getIcon()">
                    {{ $metric->state->getLabel() }}
                </x-filament::badge>
            </div>
        @endforeach
    </div>
</x-filament::section>
