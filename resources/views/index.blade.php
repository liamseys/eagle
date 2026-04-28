<x-app-layout>
    <x-hero :title="__('Help Center')"
            :description="__('Here you\'ll find answers to many common questions about our services, policies, and features. If you need further assistance, our team is always ready to help you find the information or support you need.')"/>

    {{ Breadcrumbs::render('index') }}

    <section class="py-14 sm:py-16">
        <x-container class="max-w-7xl">
            @if(!$categories->isEmpty())
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
                    @foreach($categories as $category)
                        <a href="{{ route('categories.show', $category) }}"
                           class="group flex items-start gap-4 rounded-2xl border border-gray-950/5 bg-white p-5 shadow-xs transition duration-200 hover:-translate-y-0.5 hover:border-gray-950/10 hover:shadow-sm">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-gray-50 ring-1 ring-inset ring-gray-950/5 transition group-hover:bg-gray-100">
                                <x-dynamic-component :component="$category->icon" class="size-5 text-gray-700"/>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-pretty text-base font-semibold tracking-tight text-gray-900">
                                    {{ $category->name }}
                                </p>

                                @if(!empty($category->description))
                                    <p class="mt-1 text-pretty text-sm leading-relaxed text-gray-500">
                                        {{ $category->description }}
                                    </p>
                                @endif

                                <p class="mt-3 text-xs font-medium tabular-nums text-gray-400">
                                    {{ $category->articles_count }} {{ Str::plural('article', $category->articles_count) }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="mt-6 flex flex-col items-center gap-6 text-center">
                    <img src="{{ asset('img/no_results.svg') }}" alt="No categories" class="mx-auto h-24">
                    <p class="text-gray-500">{{ __('No categories found.') }}</p>
                </div>
            @endif
        </x-container>
    </section>
</x-app-layout>
