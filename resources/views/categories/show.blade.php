@use('App\Models\HelpCenter\Article')

<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    {{ Breadcrumbs::render('category', $category) }}

    <section class="py-14 sm:py-16">
        <x-container class="max-w-7xl">
            <div class="flex flex-col gap-1">
                <h2 class="text-balance text-2xl font-semibold tracking-tight text-gray-900">{{ $category->name }}</h2>
                @if(!empty($category->description))
                    <p class="max-w-2xl text-pretty text-sm text-gray-500">{{ $category->description }}</p>
                @endif
            </div>

            @if(!$sections->isEmpty())
                <div class="mt-8 grid grid-cols-1 items-start gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($sections as $section)
                        <x-card>
                            <x-slot name="header">
                                <h3 class="text-base font-semibold tracking-tight text-gray-900">{{ $section->name }}</h3>
                            </x-slot>

                            <ul class="-mx-2 -my-2 flex flex-col" role="list">
                                @foreach($section->articles()
                                                 ->published()
                                                 ->public()
                                                 ->get()
                                                 ->merge($section->forms->where('is_public', true))
                                                 ->sortBy('sort') as $item)
                                    <x-nav-link
                                        :href="route($item instanceof Article ? 'articles.show' : 'forms.show', $item)">
                                        <p class="text-sm">{{ $item->label }}</p>
                                        <x-heroicon-s-chevron-right class="size-4 text-gray-400 transition group-hover:text-gray-600"/>
                                    </x-nav-link>
                                @endforeach
                            </ul>
                        </x-card>
                    @endforeach
                </div>
            @else
                <div class="mt-10 flex flex-col items-center gap-6 text-center">
                    <img src="{{ asset('img/no_results.svg') }}" alt="No sections" class="mx-auto h-24">
                    <p class="text-gray-500">{{ __('No sections found.') }}</p>
                </div>
            @endif
        </x-container>
    </section>
</x-app-layout>
