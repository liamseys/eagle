<div class="rounded-lg border border-gray-200 bg-white p-4">
    @if($submittedValue)
        <div class="flex items-center gap-2 text-sm text-gray-700">
            <x-heroicon-s-check-circle class="h-5 w-5 text-green-500"/>
            <span>{{ __('Thanks for your feedback!') }}</span>
        </div>
    @else
        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-medium text-gray-800">
                {{ __('Did this article help?') }}
            </p>

            <div class="flex items-center gap-2">
                @foreach($options as $option)
                    <button
                        type="button"
                        wire:click="submit('{{ $option->value }}')"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        class="flex items-center gap-1 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm text-gray-700 transition hover:border-gray-300 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50"
                        aria-label="{{ $option->getLabel() }}"
                    >
                        <span class="text-base leading-none">{{ $option->getEmoji() }}</span>
                        <span>{{ $option->getLabel() }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
