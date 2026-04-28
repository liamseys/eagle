@props(['active'])

@php
    $base = 'group flex items-center justify-between gap-3 px-3 py-2.5 sm:rounded-lg text-gray-700 transition';
    $state = ($active ?? false)
        ? 'bg-gray-950/5 text-gray-900 font-medium'
        : 'hover:bg-gray-950/[0.04] hover:text-gray-900';
@endphp

<a {{ $attributes->merge(['class' => 'block']) }}>
    <li class="{{ $base }} {{ $state }}">
        {{ $slot }}
    </li>
</a>
