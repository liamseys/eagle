@use('App\Models\HelpCenter\Article')

<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    {{ Breadcrumbs::render('category', $category) }}

    <section class="py-12">
        <x-container class="max-w-7xl">
            <h2 class="text-xl font-semibold">{{ $category->name }}</h2>

            @if(!$sections->isEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 items-start">
                    @foreach($sections as $section)
                        <x-card>
                            <x-slot name="header">
                                <h3 class="font-semibold">{{ $section->name }}</h3>
                            </x-slot>

                            <ul class="-mx-4 flex flex-col space-y-2 -my-4">
                                @foreach($section->articles()
                                                 ->published()
                                                 ->public()
                                                 ->get()
                                                 ->merge($section->forms->where('is_public', true))
                                                 ->sortBy('sort') as $item)
                                    <x-nav-link
                                        :href="route($item instanceof Article ? 'articles.show' : 'forms.show', $item)">
                                        <p class="text-sm">{{ $item->label }}</p>
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
        </x-container>
    </section>
</x-app-layout>
