<x-filament-widgets::widget>
    <div class="flex items-start gap-3">
        <div class="flex size-9 flex-none items-center justify-center rounded-lg bg-gray-950/5 text-gray-700">
            <x-heroicon-o-information-circle class="size-5"/>
        </div>

        <div class="space-y-0.5">
            <p class="text-sm font-semibold text-gray-950">{{ __('Are you looking for something else?') }}</p>
            <p class="text-sm text-gray-500">
                {!! __('Explore our <a href=":url" class="font-medium text-gray-700 underline underline-offset-2 hover:text-gray-950">other help pages</a> for more information.', ['url' => route('index', ['locale' => config('app.locale')])]) !!}
            </p>
        </div>
    </div>
</x-filament-widgets::widget>
