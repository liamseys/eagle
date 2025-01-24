<x-app-layout>
    <x-hero :title="__('Help Center')"
            :description="__('Here you\'ll find answers to many common questions about our services, policies, and features. If you need further assistance, our team is always ready to help you find the information or support you need.')"/>

    <section class="py-12">
        <x-container>
            <div class="grid gap-4 grid-cols-1 sm:grid-cols-3">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category) }}">
                        <div class="flex flex-col p-4 space-y-2 border rounded-lg hover:bg-gray-100 hover:cursor-pointer">
                            <x-dynamic-component :component="$category->icon" class="h-6 w-6"/>

                            <p class="font-bold text-[#F8CB09]">{{ $category->name }}</p>

                            @if($category->description)
                                <p class="text-sm text-gray-500">{{ $category->description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </x-container>
    </section>
</x-app-layout>
