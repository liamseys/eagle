<div class="rounded-2xl border border-gray-950/5 bg-white p-5 shadow-xs sm:p-6">
    @if($submittedValue)
        <div class="flex items-center gap-2 text-sm text-gray-700">
            <x-heroicon-s-check-circle class="size-5 text-green-500"/>
            <span>{{ __('Thanks for your feedback!') }}</span>
        </div>
    @else
        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-medium text-gray-900">
                {{ __('Did this article help?') }}
            </p>

            <div class="flex flex-wrap items-center gap-2">
                @foreach($options as $option)
                    <button
                        type="button"
                        wire:click="submit('{{ $option->value }}')"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        class="inline-flex items-center gap-1.5 rounded-full border border-gray-950/5 bg-gray-50 px-3.5 py-1.5 text-sm text-gray-700 transition hover:border-gray-950/10 hover:bg-white hover:text-gray-900 hover:shadow-xs disabled:cursor-not-allowed disabled:opacity-50"
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
