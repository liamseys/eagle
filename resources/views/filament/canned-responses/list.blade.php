@php
    $statePath = $schemaComponent->getContainer()->getStatePath();
    $selectedId = $get('canned_response_id');
@endphp

<div class="flex flex-col gap-2">
    @if ($responses->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-950/10 px-6 py-14 text-center dark:border-white/10">
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::OutlinedChatBubbleLeftRight"
                class="mb-3 size-7 text-gray-400 dark:text-gray-500"
            />
            <p class="text-sm font-medium text-gray-900 dark:text-white">
                {{ __('No canned responses match your filters.') }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Try clearing your search, or create a new response.') }}
            </p>
        </div>
    @else
        <ul role="listbox" class="flex max-h-[28rem] flex-col overflow-y-auto overflow-x-hidden rounded-xl bg-white outline outline-gray-950/5 dark:bg-white/[0.02] dark:outline-white/10">
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
                    class="group relative flex cursor-pointer items-start gap-3 px-4 py-3.5 outline-none not-last:border-b not-last:border-gray-950/5 focus-visible:bg-gray-950/[0.03] dark:not-last:border-white/5 dark:focus-visible:bg-white/5
                        {{ $isSelected
                            ? 'bg-primary-500/[0.08] dark:bg-primary-500/[0.12]'
                            : 'hover:bg-gray-950/[0.02] dark:hover:bg-white/[0.03]' }}"
                >
                    <span aria-hidden="true" class="mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-full
                        {{ $isSelected
                            ? 'bg-primary-600 dark:bg-primary-500'
                            : 'bg-white outline outline-gray-950/15 dark:bg-white/5 dark:outline-white/20' }}">
                        @if ($isSelected)
                            <x-filament::icon
                                :icon="\Filament\Support\Icons\Heroicon::Check"
                                class="size-3 text-white"
                            />
                        @endif
                    </span>

                    <div class="flex min-w-0 flex-1 flex-col gap-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <p class="truncate text-sm font-medium text-gray-950 dark:text-white">
                                {{ $response['title'] }}
                            </p>
                            @if (! empty($response['category']))
                                <span class="inline-flex items-center rounded-md bg-gray-950/[0.04] px-1.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                    {{ $response['category'] }}
                                </span>
                            @endif
                            @if (! empty($response['is_shared']))
                                <span
                                    class="inline-flex items-center text-gray-400 dark:text-gray-500"
                                    title="{{ __('Shared with all agents') }}"
                                    aria-label="{{ __('Shared with all agents') }}"
                                >
                                    <x-filament::icon
                                        :icon="\Filament\Support\Icons\Heroicon::Users"
                                        class="size-3.5 shrink-0"
                                    />
                                </span>
                            @endif
                        </div>
                        @if (! empty($response['preview']))
                            <p class="line-clamp-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ $response['preview'] }}
                            </p>
                        @endif
                    </div>

                    @if (! empty($response['can_manage']))
                        <div
                            x-on:click.stop
                            class="-my-1 -mr-1 flex shrink-0 items-center gap-0.5 opacity-0 transition-opacity group-hover:opacity-100 group-focus-within:opacity-100 {{ $isSelected ? 'opacity-100' : '' }}"
                        >
                            <button
                                type="button"
                                x-on:click.stop="$wire.mountAction('editCannedResponse', { record: @js($id) })"
                                title="{{ __('Edit') }}"
                                aria-label="{{ __('Edit canned response') }}"
                                class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 hover:bg-gray-950/5 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                            >
                                <x-filament::icon
                                    :icon="\Filament\Support\Icons\Heroicon::PencilSquare"
                                    class="size-4 shrink-0"
                                />
                            </button>
                            <button
                                type="button"
                                x-on:click.stop="$wire.mountAction('deleteCannedResponse', { record: @js($id) })"
                                title="{{ __('Delete') }}"
                                aria-label="{{ __('Delete canned response') }}"
                                class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 hover:bg-danger-500/10 hover:text-danger-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-danger-500 dark:text-gray-400 dark:hover:bg-danger-500/15 dark:hover:text-danger-400"
                            >
                                <x-filament::icon
                                    :icon="\Filament\Support\Icons\Heroicon::Trash"
                                    class="size-4 shrink-0"
                                />
                            </button>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($totalAvailable > $responses->count())
            <p class="px-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Showing :count of :total — refine your search to see more.', [
                    'count' => $responses->count(),
                    'total' => $totalAvailable,
                ]) }}
            </p>
        @endif
    @endif
</div>
