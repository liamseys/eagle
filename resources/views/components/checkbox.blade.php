@props(['disabled' => false, 'required' => false])

<input type="checkbox"
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {!! $attributes->merge(['class' => 'size-4 rounded border-gray-950/20 text-gray-950 shadow-xs transition focus:ring-2 focus:ring-gray-950 focus:ring-offset-2 disabled:cursor-not-allowed disabled:border-gray-950/10 disabled:bg-gray-50']) !!}>
