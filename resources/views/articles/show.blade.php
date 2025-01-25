<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    <section class="py-12">
        <x-container>
            <h2 class="text-xl font-semibold">{{ $article->title }}</h2>
        </x-container>
    </section>
</x-app-layout>
