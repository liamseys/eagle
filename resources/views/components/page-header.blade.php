@props([
    'title',
    'as' => 'h2',
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2']) }}>
    <{{ $as }} class="text-balance text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
        {{ $title }}
    </{{ $as }}>

    @if(filled(trim($slot)))
        <div class="max-w-2xl text-pretty text-sm text-gray-500 sm:text-base">
            {{ $slot }}
        </div>
    @endif
</div>
