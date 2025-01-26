<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    <section class="py-12">
        <x-container class="max-w-7xl">
            <h2 class="text-xl font-semibold">{{ $category->name }}</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                @foreach($category->sections as $section)
                    <x-card>
                        <x-slot name="header">
                            <h3 class="font-semibold">{{ $section->name }}</h3>
                        </x-slot>

                        <ul class="-mx-4">
                            @foreach($section->articles()
                                             ->public()
                                             ->published()
                                             ->get() as $article)
                                <x-nav-link :href="route('articles.show', $article)">
                                    <p class="text-sm">{{ $article->title }}</p>
                                    <x-heroicon-s-chevron-right class="h-4 w-4"/>
                                </x-nav-link>
                            @endforeach
                        </ul>
                    </x-card>
                @endforeach
            </div>
        </x-container>
    </section>
</x-app-layout>
