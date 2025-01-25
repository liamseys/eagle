<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    <section class="py-12">
        <x-container>
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
            </div>
        </x-container>
    </section>
</x-app-layout>
