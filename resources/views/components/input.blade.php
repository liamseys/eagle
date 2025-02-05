@props(['disabled' => false, 'required' => false])

<input {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {!! $attributes->merge(['class' => 'border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 disabled:ring-gray-200']) !!}>
