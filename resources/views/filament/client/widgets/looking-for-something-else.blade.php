<x-filament-widgets::widget>
    <div class="flex flex-row items-center gap-2">
        <x-heroicon-m-information-circle class="h-8 w-8"/>
        <div>
            <p class="text-sm font-bold">{{ __('Are you looking for something else?') }}</p>
            <p class="text-sm text-gray-500">
                {!! __('Explore our <a href=":url" class="hover:underline">other help pages</a> for more information.', ['url' => route('index', ['locale' => config('app.locale')])]) !!}
            </p>
        </div>
    </div>
</x-filament-widgets::widget>
