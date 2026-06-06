@use(App\Enums\Tickets\TicketPriority)
@use(App\Enums\Tickets\TicketType)
@use(App\Enums\Tickets\TicketStatus)
@use(App\Enums\Tickets\TicketActivityColumn)

@php
    $enumMap = [
        TicketActivityColumn::STATUS->value => TicketStatus::class,
        TicketActivityColumn::PRIORITY->value => TicketPriority::class,
        TicketActivityColumn::TYPE->value => TicketType::class,
    ];

    $columnIcons = [
        TicketActivityColumn::STATUS->value => 'heroicon-m-arrow-path-rounded-square',
        TicketActivityColumn::PRIORITY->value => 'heroicon-m-flag',
        TicketActivityColumn::TYPE->value => 'heroicon-m-tag',
    ];

    $activities = $getRecord()->activity()
        ->with('authorable')
        ->whereIn('column', array_keys($enumMap))
        ->oldest()
        ->get();
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @if ($activities->isNotEmpty())
        <ul role="list" class="space-y-6">
            @foreach ($activities as $ticketActivity)
                @php
                    $column = $ticketActivity->column->value;
                    $enumInstance = $enumMap[$column]::from($ticketActivity->value);
                    $color = method_exists($enumInstance, 'getColor') ? $enumInstance->getColor() : 'gray';
                    $valueIcon = method_exists($enumInstance, 'getIcon') ? $enumInstance->getIcon() : null;
                    $causer = $ticketActivity->authorable?->name ?? __('System');
                @endphp

                <li class="relative flex gap-x-3">
                    {{-- Timeline connector (masked behind the node; hidden after the last entry) --}}
                    <div @class([
                        'absolute left-0 top-0 flex w-8 justify-center',
                        '-bottom-6' => ! $loop->last,
                        'h-6' => $loop->last,
                    ])>
                        <div class="w-px bg-gray-200 dark:bg-white/10"></div>
                    </div>

                    {{-- Icon node --}}
                    <div class="relative flex size-8 flex-none items-center justify-center rounded-full bg-white ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-white/10">
                        <x-filament::icon
                            :icon="$columnIcons[$column]"
                            class="size-4 text-gray-400 dark:text-gray-500"
                        />
                    </div>

                    {{-- Activity details --}}
                    <div class="min-w-0 flex-1 pt-0.5">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1.5">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-medium text-gray-950 dark:text-white">{{ $causer }}</span>
                                {{ __('changed the :column to', ['column' => $column]) }}
                            </span>

                            <x-filament::badge :color="$color" :icon="$valueIcon">
                                {{ $enumInstance->getLabel() }}
                            </x-filament::badge>
                        </div>

                        <time
                            datetime="{{ $ticketActivity->created_at->toIso8601String() }}"
                            title="{{ $ticketActivity->created_at->translatedFormat('M j, Y · g:i A') }}"
                            class="mt-1 block text-xs text-gray-400 dark:text-gray-500"
                        >
                            {{ $ticketActivity->created_at->diffForHumans() }}
                        </time>

                        @if (filled($ticketActivity->reason))
                            <p class="mt-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-600 ring-1 ring-gray-950/5 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10">
                                {{ $ticketActivity->reason }}
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <div class="flex flex-col items-center justify-center gap-y-3 py-12 text-center">
            <div class="flex size-12 items-center justify-center rounded-full bg-gray-50 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <x-filament::icon icon="heroicon-o-clock" class="size-6 text-gray-400 dark:text-gray-500" />
            </div>

            <div class="space-y-0.5">
                <p class="text-sm font-medium text-gray-950 dark:text-white">{{ __('No activity yet') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Changes to status, priority, and type will appear here.') }}</p>
            </div>
        </div>
    @endif
</x-dynamic-component>
