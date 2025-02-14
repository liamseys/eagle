@props(['disabled' => false, 'required' => false])

<input type="checkbox"
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {!! $attributes->merge(['class' => 'rounded border-gray-300 shadow-sm text-black focus:ring-black disabled:cursor-not-allowed disabled:bg-gray-50 disabled:border-gray-200']) !!}>
