@props(['disabled' => false, 'required' => false])

<select {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {!! $attributes->merge(['class' => 'block w-full rounded-lg border-gray-950/10 text-sm text-gray-900 shadow-xs transition focus:border-gray-950 focus:ring-1 focus:ring-gray-950 disabled:cursor-not-allowed disabled:border-gray-950/5 disabled:bg-gray-50 disabled:text-gray-500']) !!}>
    {{ $slot }}
</select>
