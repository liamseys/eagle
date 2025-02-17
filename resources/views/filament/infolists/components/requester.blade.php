@use(Symfony\Component\Intl\Locales)
@use(Symfony\Component\Intl\Timezones)

<x-filament::section>
    <div class="flex flex-col space-y-4 text-gray-950 dark:text-white">
        <div class="flex items-center gap-3">
            <img
                src="{{ $getRecord()->requester->avatar }}"
                alt="{{ __('Avatar') }}"
                class="h-12 w-12 rounded-full"
            />

            <div class="flex flex-col">
                <span class="text-md font-bold leading-6 text-gray-950">
                    {{ $getRecord()->requester->name }}
                </span>
                <span class="text-xs text-gray-500">
                    {{ __('Created') }} {{ $getRecord()->requester->created_at->diffForHumans() }}
                </span>
            </div>
        </div>

        <div class="flex flex-col space-y-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <x-heroicon-s-envelope class="h-4 w-4"/>
                    <p class="text-sm font-medium leading-6 text-gray-950">{{ __('Email') }}</p>
                </div>
                <p class="text-sm">
                    {{ $getRecord()->requester->email }}
                </p>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <x-heroicon-s-phone class="h-4 w-4"/>
                    <p class="text-sm font-medium leading-6 text-gray-950">{{ __('Phone') }}</p>
                </div>
                <p class="text-sm">
                    {{ $getRecord()->requester->phone ?? '-' }}
                </p>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <x-heroicon-s-map-pin class="h-4 w-4"/>
                    <p class="text-sm font-medium leading-6 text-gray-950">{{ __('Locale') }}</p>
                </div>
                <p class="text-sm">
                    {{ Locales::getName($getRecord()->requester->locale) }}
                </p>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <x-heroicon-s-clock class="h-4 w-4"/>
                    <p class="text-sm font-medium leading-6 text-gray-950">{{ __('Time') }}</p>
                </div>
                <p class="text-sm">
                    {{ now()->tz($getRecord()->requester->timezone)->format('H:i') }}
                    @if($getRecord()->requester->timezone !== 'UTC')
                        <span>|</span> {{ Timezones::getName(timezone: $getRecord()->requester->timezone) }}
                    @endif  
                </p>
            </div>
        </div>
    </div>
</x-filament::section>
