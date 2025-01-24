<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    <section class="py-12">
        <x-container>
            <h2 class="text-xl font-semibold">{{ $category->name }}</h2>

            <div class="grid gap-4 grid-cols-1 sm:grid-cols-3">
                @foreach($category->sections as $section)
                    <x-card>
                        {{ $section->name }}
                    </x-card>
                @endforeach
            </div>
        </x-container>
    </section>
</x-app-layout>
