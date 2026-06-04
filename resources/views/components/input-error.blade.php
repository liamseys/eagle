@props(['messages' => []])

@if (filled($messages))
    <ul {{ $attributes->merge(['class' => 'flex flex-col gap-0.5 text-sm text-red-600']) }} role="alert">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
