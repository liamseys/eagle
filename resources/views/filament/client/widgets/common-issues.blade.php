<x-filament-widgets::widget>
    <div class="flex flex-col gap-6">
        <div>
            <h2 class="text-lg font-bold">{{ __('Common issues') }}</h2>
            <p>{{ __('Links to our most frequently used help forms.') }}</p>
        </div>

        @if(!$sections->isEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                @foreach($sections as $section)
                    <x-card>
                        <x-slot name="header">
                            <h3 class="font-semibold">{{ $section->name }}</h3>
                        </x-slot>

                        <ul class="-mx-4 flex flex-col space-y-2 -my-4">
                            @foreach($section->forms()
                                             ->public()
                                             ->get()
                                             ->sortBy('sort') as $form)
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
            <div class="text-center flex flex-col gap-6 mt-6">
                <img src="{{ asset('img/no_results.svg') }}" alt="No sections" class="h-24 mx-auto">
                <p class="text-gray-500">{{ __('No sections found.') }}</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
