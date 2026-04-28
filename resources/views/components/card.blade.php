@props([
    'header' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'divide-y divide-gray-950/5 overflow-hidden rounded-2xl bg-white border border-gray-950/5 shadow-xs']) }}>
    @if($header)
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            {{ $header }}
        </div>
    @endif

    <div class="px-5 py-5 sm:p-6">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-5 py-4 sm:px-6">
            {{ $footer }}
        </div>
    @endif
</div>
