<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    <section class="py-12">
        <x-container class="max-w-7xl">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="col-span-2">
                    <x-card>
                        <x-slot name="header">
                            <div class="flex flex-col gap-2">
                                <h2 class="text-xl font-semibold">{{ $article->title }}</h2>
                                <p class="text-sm text-gray-500">{{ $article->description }}</p>
                            </div>
                        </x-slot>

                        <div class="article-content">
                            {!! $article->body !!}
                        </div>
                    </x-card>
                </div>
                <div class="col-span-1">
                    <x-card>
                        <x-slot name="header">
                            <h3 class="font-semibold">{{ $article->section->name }}</h3>
                        </x-slot>

                        <ul class="-mx-4 flex flex-col space-y-2">
                            @foreach($article->section
                                                 ->articles()
                                                 ->public()
                                                 ->published()
                                                 ->get() as $article)
                                <x-nav-link :href="route('articles.show', $article)"
                                            :active="request()->routeIs('articles.show') && request()->route('article')?->is($article)">
                                    <p class="text-sm">{{ $article->title }}</p>
                                    <x-heroicon-s-chevron-right class="h-4 w-4"/>
                                </x-nav-link>
                            @endforeach
                        </ul>
                    </x-card>
                </div>
            </div>
        </x-container>
    </section>
</x-app-layout>
