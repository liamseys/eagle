@props(['active'])

@php
    $classes = ($active ?? false)
                ? 'flex items-center justify-between p-4 sm:rounded-lg bg-gray-100'
                : 'flex items-center justify-between p-4 sm:rounded-lg hover:bg-gray-100';
@endphp

<a {{ $attributes->merge(['class' => '']) }}>
    <li class="{{ $classes }}">
        {{ $slot }}
    </li>
</a>
