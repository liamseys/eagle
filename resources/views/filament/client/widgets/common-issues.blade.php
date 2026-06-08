<x-filament-widgets::widget>
    <div class="flex flex-col gap-6">
        <div class="flex items-start gap-4">
            <div class="flex size-11 flex-none items-center justify-center rounded-xl bg-gray-950/5 text-gray-700">
                <x-heroicon-o-question-mark-circle class="size-6"/>
            </div>

            <div class="space-y-1">
                <h2 class="text-xl font-bold tracking-tight text-gray-950">{{ __('Common issues') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Browse our most frequently used help forms and jump straight to the right one.') }}</p>
            </div>
        </div>

        @if(!$sections->isEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-start">
                @foreach($sections as $section)
                    <x-card>
                        <x-slot name="header">
                            <h3 class="font-semibold">{{ $section->name }}</h3>
                        </x-slot>

                        <ul class="-mx-4 flex flex-col space-y-2 -my-4">
                            @foreach($section->forms as $form)
                                <x-nav-link
                                    :href="route('forms.show', ['locale' => config('app.locale'), 'form' => $form])">
                                    <p class="text-sm">{{ $form->label }}</p>
                                    <x-heroicon-s-chevron-right class="h-4 w-4"/>
                                </x-nav-link>
                            @endforeach
                        </ul>
                    </x-card>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-4 py-10 text-center">
                <img src="{{ asset('img/no_results.svg') }}" alt="" class="h-24">

                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-950">{{ __('No common issues yet') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Featured help forms will appear here once they become available.') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
