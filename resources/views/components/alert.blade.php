@props(['icon' => null])

<div class="flex items-center p-4 text-sm text-gray-800 border border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600" role="alert">
    @if(isset($icon))
        <x-dynamic-component :component="'heroicon-m-'.$icon" class="shrink-0 inline w-5 h-5 me-2"/>
    @endif
    <span class="sr-only">Info</span>
    <div>{{ $slot }}</div>
</div>
