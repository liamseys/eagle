@props(['disabled' => false, 'required' => false])

<input type="checkbox"
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {!! $attributes->merge(['class' => 'rounded border-gray-300 shadow-sm text-primary-600 focus:ring-primary-500 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:border-gray-200']) !!}>
