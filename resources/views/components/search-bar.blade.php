@props([
    'value' => '',
])

<form action="{{ route('search') }}" method="GET" role="search">
    <label for="hc-search" class="sr-only">{{ __('Search the Help Center') }}</label>

    <div class="relative">
        <x-heroicon-s-magnifying-glass class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gray-400"/>

        <input
            id="hc-search"
            type="search"
            name="q"
            value="{{ $value }}"
            maxlength="100"
            placeholder="{{ __('Search for articles and forms...') }}"
            class="w-full rounded-xl border-0 bg-white py-3 pl-12 pr-4 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-950/10 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-950/20"
        />
    </div>
</form>
