@props(['active'])

@php
    $classes = ($active ?? false)
                ? 'flex items-center justify-between p-4 rounded-lg bg-yellow-50 text-yellow-500'
                : 'flex items-center justify-between p-4 rounded-lg hover:bg-gray-100';
@endphp

<a {{ $attributes->merge(['class' => '']) }}>
    <li class="{{ $classes }}">
        {{ $slot }}
    </li>
</a>
