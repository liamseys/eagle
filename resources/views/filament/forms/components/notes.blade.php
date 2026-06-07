@use(App\Filament\AvatarProviders\GravatarProvider)

@php
    $notes = $getRecord()->notes()->with('user')->latest()->get();
@endphp

<div {{ $attributes }}>
    <x-filament::section>
        <x-slot name="heading">
            <span class="inline-flex items-center gap-2">
                {{ __('Notes') }}

                @if ($notes->isNotEmpty())
                    <x-filament::badge>{{ $notes->count() }}</x-filament::badge>
                @endif
            </span>
        </x-slot>

        <x-slot name="description">
            <span class="inline-flex items-center gap-1.5">
                <x-filament::icon icon="heroicon-m-lock-closed" class="size-3.5" />
                {{ __('Only visible to agents') }}
            </span>
        </x-slot>

        @if ($notes->isNotEmpty())
            <ul role="list" class="space-y-6">
                @foreach ($notes as $note)
                    <li class="relative flex gap-x-3">
                        {{-- Timeline connector (masked behind the avatar; trimmed after the last note) --}}
                        <div @class([
                            'absolute left-0 top-0 flex w-8 justify-center',
                            '-bottom-6' => ! $loop->last,
                            'h-8' => $loop->last,
                        ])>
                            <div class="w-px bg-gray-200 dark:bg-white/10"></div>
                        </div>

                        {{-- Author avatar acts as the timeline node --}}
                        <img
                            src="{{ GravatarProvider::generateGravatarUrl($note->user?->email ?? '') }}"
                            alt="{{ $note->user?->name }}"
                            class="relative size-8 flex-none rounded-full bg-gray-50 object-cover outline-1 -outline-offset-1 outline-black/5 dark:bg-white/10 dark:outline-white/10"
                        />

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-2 gap-y-0.5">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-medium text-gray-950 dark:text-white">{{ $note->user?->name ?? __('Unknown agent') }}</span>
                                </p>

                                <time
                                    datetime="{{ $note->created_at->toIso8601String() }}"
                                    title="{{ $note->created_at->translatedFormat('M j, Y · g:i A') }}"
                                    class="text-xs text-gray-400 dark:text-gray-500"
                                >{{ $note->created_at->diffForHumans() }}</time>
                            </div>

                            <div class="mt-1.5 whitespace-pre-line rounded-lg bg-amber-50 px-3 py-2 text-sm text-gray-700 ring-1 ring-amber-950/5 dark:bg-amber-400/10 dark:text-amber-50 dark:ring-amber-400/15">
                                {{ $note->body }}
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="flex flex-col items-center justify-center gap-y-3 py-8 text-center">
                <div class="flex size-12 items-center justify-center rounded-full bg-gray-50 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                    <x-filament::icon icon="heroicon-o-pencil-square" class="size-6 text-gray-400 dark:text-gray-500" />
                </div>

                <div class="space-y-0.5">
                    <p class="text-sm font-medium text-gray-950 dark:text-white">{{ __('No notes yet') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Internal notes you add will appear here.') }}</p>
                </div>
            </div>
        @endif
    </x-filament::section>
</div>
