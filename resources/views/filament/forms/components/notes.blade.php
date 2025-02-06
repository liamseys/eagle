<div {{ $attributes }}>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Notes') }}
        </x-slot>

        <div class="flex flex-col space-y-4">
            @forelse($getRecord()->notes as $note)
                <div class="w-full p-4 text-sm bg-yellow-100 rounded-lg">
                    <p>{{ $note->body }}</p>
                    <span class="text-xs text-gray-500">{{ $note->created_at->format('jS F Y, H:i') }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No notes') }}</p>
            @endforelse
        </div>
    </x-filament::section>
</div>
