<x-app-layout>
    <x-hero :title="__('Help Center')"
            :description="__('Here you\'ll find answers to many common questions about our services, policies, and features. If you need further assistance, our team is always ready to help you find the information or support you need.')"/>

    {{ Breadcrumbs::render('index') }}

    <section class="py-12">
        <x-container class="max-w-7xl">
            @if(!$categories->isEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($categories as $category)
                        <a href="{{ route('categories.show', $category) }}">
                            <div class="flex flex-col p-4 space-y-2 border rounded-lg hover:bg-gray-100 hover:cursor-pointer">
                                <x-dynamic-component :component="$category->icon" class="h-6 w-6"/>

                                <p class="font-bold text-black">{{ $category->name }}</p>

                                @if($category->description)
                                    <p class="text-sm text-gray-500">{{ $category->description }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center flex flex-col gap-6 mt-6">
                    <img src="{{ asset('img/no_results.svg') }}" alt="No categories" class="h-24 mx-auto">
                    <p class="text-gray-500">{{ __('No categories found.') }}</p>
                </div>
            @endif
        </x-container>
    </section>
</x-app-layout>
