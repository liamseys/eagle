@use(Symfony\Component\Intl\Locales)
@use(Symfony\Component\Intl\Timezones)

@php
    $requester = $getRecord()->requester;

    $timezone = $requester->timezone ?: 'UTC';
    $localeName = Locales::exists($requester->locale) ? Locales::getName($requester->locale) : __('Unknown');
    $timezoneName = Timezones::exists($timezone) ? Timezones::getName($timezone) : __('Unknown');
@endphp

<x-filament::section>
    <x-slot name="heading">{{ __('Requester') }}</x-slot>

    <div class="flex flex-col antialiased">
        {{-- Identity --}}
        <div class="flex items-center gap-4 pb-5">
            <img
                src="{{ $requester->avatar }}"
                alt="{{ $requester->name }}"
                class="size-14 shrink-0 rounded-full bg-gray-100 object-cover shadow-sm outline-1 -outline-offset-1 outline-black/5 dark:bg-white/10 dark:outline-white/10"
            />

            <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                <p class="truncate text-base font-semibold text-gray-950 dark:text-white">
                    {{ $requester->name }}
                </p>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <x-filament::badge :color="$requester->is_active ? 'success' : 'gray'">
                        {{ $requester->is_active ? __('Active') : __('Inactive') }}
                    </x-filament::badge>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Member since :date', ['date' => $requester->created_at->translatedFormat('M Y')]) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <dl class="flex flex-col gap-4 border-t border-gray-950/5 py-5 dark:border-white/10">
            <div class="flex min-w-0 flex-col gap-1">
                <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-m-envelope" aria-hidden="true" class="size-4 shrink-0" />
                    {{ __('Email') }}
                </dt>
                <dd class="truncate text-sm text-gray-950 dark:text-white">
                    <a
                        href="mailto:{{ $requester->email }}"
                        class="font-medium transition hover:text-primary-600 hover:underline dark:hover:text-primary-400"
                    >
                        {{ $requester->email }}
                    </a>
                </dd>
            </div>

            <div class="flex min-w-0 flex-col gap-1">
                <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-m-phone" aria-hidden="true" class="size-4 shrink-0" />
                    {{ __('Phone') }}
                </dt>
                <dd class="truncate text-sm text-gray-950 dark:text-white">
                    @if ($requester->phone)
                        <a
                            href="tel:{{ preg_replace('/[^0-9+]/', '', $requester->phone) }}"
                            class="font-medium transition hover:text-primary-600 hover:underline dark:hover:text-primary-400"
                        >
                            {{ $requester->phone }}
                        </a>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">{{ __('Not provided') }}</span>
                    @endif
                </dd>
            </div>
        </dl>

        {{-- Locale & local time --}}
        <dl class="grid grid-cols-2 divide-x divide-gray-950/5 overflow-hidden rounded-xl bg-gray-50 ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-white/5 dark:ring-white/10">
            <div class="flex min-w-0 flex-col gap-1 p-3">
                <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-m-globe-alt" aria-hidden="true" class="size-4 shrink-0" />
                    {{ __('Locale') }}
                </dt>
                <dd class="truncate text-sm font-medium text-gray-950 dark:text-white">
                    {{ $localeName }}
                </dd>
            </div>

            <div class="flex min-w-0 flex-col gap-1 p-3">
                <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-m-clock" aria-hidden="true" class="size-4 shrink-0" />
                    {{ __('Local time') }}
                </dt>
                <dd class="flex min-w-0 flex-col gap-0.5">
                    <p class="text-sm font-semibold text-gray-950 tabular-nums dark:text-white">
                        {{ now()->tz($timezone)->format('H:i') }}
                    </p>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                        {{ $timezoneName }}
                    </p>
                </dd>
            </div>
        </dl>
    </div>
</x-filament::section>
