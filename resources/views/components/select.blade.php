@props(['disabled' => false, 'required' => false])

<select {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {!! $attributes->merge(['class' => 'text-sm rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 disabled:ring-gray-200']) !!}>
    {{ $slot }}
</select>
