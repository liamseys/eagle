@props(['icon' => null])

<div class="flex items-center gap-2.5 rounded-xl border border-gray-950/5 bg-gray-50 p-4 text-sm text-gray-800" role="alert">
    @if(isset($icon))
        <x-dynamic-component :component="'heroicon-m-'.$icon" class="size-5 shrink-0 text-gray-500"/>
    @endif
    <span class="sr-only">{{ __('Info') }}</span>
    <div>
        {{ $slot }}
    </div>
</div>
