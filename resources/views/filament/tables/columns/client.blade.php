@php
    $client = $getRecord();
@endphp

<div class="flex items-center gap-3">
    <img
        src="{{ $client->avatar }}"
        alt="{{ $client->name }}"
        class="size-9 shrink-0 rounded-full bg-gray-100 object-cover outline-1 -outline-offset-1 outline-black/5 dark:bg-white/10 dark:outline-white/10"
    />

    <div class="flex min-w-0 flex-col">
        <p class="truncate text-sm font-medium text-gray-950 dark:text-white">
            {{ $client->name }}
        </p>

        <div class="flex min-w-0 items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
            <span class="truncate">{{ $client->email }}</span>
            <x-copy-button :value="$client->email" label="email" />
        </div>
    </div>
</div>
