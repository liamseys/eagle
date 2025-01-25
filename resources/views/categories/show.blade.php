<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    <section class="py-12">
        <x-container>
            <h2 class="text-xl font-semibold">{{ $category->name }}</h2>

            <div class="grid gap-4 grid-cols-1 sm:grid-cols-3">
                @foreach($category->sections as $section)
                    <x-card>
                        <x-slot name="header">
                            <h3 class="font-semibold">{{ $section->name }}</h3>
                        </x-slot>

                        <ul class="-mx-4">
                            @foreach($section->articles as $article)
                                <li class="flex items-center justify-between p-4 rounded-lg hover:bg-gray-100">
                                    {{ $article->title }}
                                    <x-heroicon-s-chevron-right class="h-4 w-4"/>
                                </li>
                            @endforeach
                        </ul>
                    </x-card>
                @endforeach
            </div>
        </x-container>
    </section>
</x-app-layout>
