<x-app-layout>
    <x-hero :title="__('Help Center')"
            :description="__('Here you\'ll find answers to many common questions about our services, policies, and features. If you need further assistance, our team is always ready to help you find the information or support you need.')"/>

    {{ Breadcrumbs::render('index') }}

    <section class="py-12">
        <x-container class="max-w-7xl">
            @if(!$categories->isEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($categories as $category)
                        <a href="{{ route('categories.show', $category) }}"
                           class="flex items-start gap-4 rounded-xl border border-gray-200 bg-white/80 p-4 transition hover:border-gray-300 hover:bg-white hover:-translate-y-0.5 duration-200">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gray-50">
                                <x-dynamic-component :component="$category->icon" class="h-6 w-6 text-gray-700"/>
                            </div>

                            <div>
                                <p class="font-semibold text-gray-900">{{ $category->name }}</p>

                                @if(!empty($category->description))
                                    <p class="mt-1 text-sm text-gray-500 leading-snug">{{ $category->description }}</p>
                                @endif

                                <p class="mt-2 text-xs text-gray-400">
                                    {{ $category->articles_count }} {{ Str::plural('article', $category->articles_count) }}
                                </p>
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
