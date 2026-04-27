@php
    $statePath = $schemaComponent->getContainer()->getStatePath();
    $selectedId = $get('canned_response_id');
@endphp

<div class="flex flex-col gap-2">
    @if ($responses->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 px-6 py-12 text-center dark:border-white/10">
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::OutlinedChatBubbleLeftRight"
                class="mb-3 size-8 text-gray-400 dark:text-gray-500"
            />
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('No canned responses match your filters.') }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Try clearing your search, or create a new response.') }}
            </p>
        </div>
    @else
        <ul role="listbox" class="flex max-h-[28rem] flex-col gap-1.5 overflow-y-auto pr-1">
            @foreach ($responses as $response)
                @php
                    $id = $response['id'];
                    $isSelected = (string) $selectedId === (string) $id;
                    $selectExpression = '$wire.set(' . \Illuminate\Support\Js::from($statePath . '.canned_response_id') . ', ' . \Illuminate\Support\Js::from($id) . ')';
                @endphp

                <li
                    wire:key="canned-response-{{ $id }}"
                    role="option"
                    aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                    x-on:click="{{ $selectExpression }}"
                    x-on:keydown.enter.prevent="{{ $selectExpression }}"
                    x-on:keydown.space.prevent="{{ $selectExpression }}"
                    tabindex="0"
                    class="group relative flex cursor-pointer items-start gap-3 rounded-lg border px-3.5 py-3 transition outline-none focus-visible:ring-2 focus-visible:ring-primary-500
                        {{ $isSelected
                            ? 'border-primary-500 bg-primary-50/60 shadow-sm ring-1 ring-primary-500/20 dark:border-primary-500/60 dark:bg-primary-500/10 dark:ring-primary-500/30'
                            : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-white/20 dark:hover:bg-white/5' }}"
                >
                    <span aria-hidden="true" class="mt-1 flex size-4 shrink-0 items-center justify-center rounded-full border-2 transition
                        {{ $isSelected
                            ? 'border-primary-600 bg-primary-600 dark:border-primary-500 dark:bg-primary-500'
                            : 'border-gray-300 dark:border-white/20' }}">
                        @if ($isSelected)
                            <span class="size-1.5 rounded-full bg-white"></span>
                        @endif
                    </span>

                    <div class="flex min-w-0 flex-1 flex-col gap-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="truncate text-sm font-medium text-gray-950 dark:text-white">
                                {{ $response['title'] }}
                            </span>
                            @if (! empty($response['category']))
                                <x-filament::badge size="xs" color="gray">
                                    {{ $response['category'] }}
                                </x-filament::badge>
                            @endif
                            @if (! empty($response['is_shared']))
                                <span
                                    class="inline-flex size-4 items-center justify-center text-gray-400 dark:text-gray-500"
                                    title="{{ __('Shared with all agents') }}"
                                    aria-label="{{ __('Shared with all agents') }}"
                                >
                                    <x-filament::icon
                                        :icon="\Filament\Support\Icons\Heroicon::Users"
                                        class="size-3.5"
                                    />
                                </span>
                            @endif
                        </div>
                        @if (! empty($response['preview']))
                            <p class="line-clamp-2 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                                {{ $response['preview'] }}
                            </p>
                        @endif
                    </div>

                    @if (! empty($response['can_manage']))
                        <div
                            x-on:click.stop
                            class="flex shrink-0 items-center gap-0.5 opacity-0 transition group-hover:opacity-100 group-focus-within:opacity-100 {{ $isSelected ? 'opacity-100' : '' }}"
                        >
                            <button
                                type="button"
                                x-on:click.stop="$wire.mountAction('editCannedResponse', { record: @js($id) })"
                                title="{{ __('Edit') }}"
                                aria-label="{{ __('Edit canned response') }}"
                                class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-200/60 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-gray-200"
                            >
                                <x-filament::icon
                                    :icon="\Filament\Support\Icons\Heroicon::PencilSquare"
                                    class="size-4"
                                />
                            </button>
                            <button
                                type="button"
                                x-on:click.stop="$wire.mountAction('deleteCannedResponse', { record: @js($id) })"
                                title="{{ __('Delete') }}"
                                aria-label="{{ __('Delete canned response') }}"
                                class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-danger-100 hover:text-danger-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-danger-500 dark:text-gray-400 dark:hover:bg-danger-500/15 dark:hover:text-danger-400"
                            >
                                <x-filament::icon
                                    :icon="\Filament\Support\Icons\Heroicon::Trash"
                                    class="size-4"
                                />
                            </button>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($totalAvailable > $responses->count())
            <p class="px-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Showing :count of :total — refine your search to see more.', [
                    'count' => $responses->count(),
                    'total' => $totalAvailable,
                ]) }}
            </p>
        @endif
    @endif
</div>
