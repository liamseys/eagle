@props(['disabled' => false, 'required' => false])

<input type="file"
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {!! $attributes->merge(['class' => 'block w-full cursor-pointer text-sm text-gray-500 transition file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-black file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white file:transition hover:file:bg-gray-800 disabled:cursor-not-allowed disabled:text-gray-400 disabled:file:bg-gray-200 disabled:file:text-gray-400']) !!}>
