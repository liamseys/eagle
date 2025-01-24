<x-master-layout class="bg-white">
    <header class="bg-black">
        <x-container>
            <div class="relative flex h-16 items-center justify-between">
                <img src="{{ asset('img/logo/logo-white.svg') }}"
                     alt="{{ config('app.name') }}"
                     class="h-8">
            </div>
        </x-container>
    </header>

    <main>
        <section class="py-12 bg-gradient-to-r from-[#F8CB09] via-[#EB2622] to-[#7506BF]">
            <x-container>
                <div class="flex flex-col gap-2">
                    <h1 class="text-3xl font-bold text-white">{{ __('Help Center') }}</h1>
                    <p class="w-full lg:w-1/2 text-sm text-white">
                        {{ __('Here you\'ll find answers to many common questions about our services, policies, and features. If you need further assistance, our team is always ready to help you find the information or support you need.') }}
                    </p>
                </div>
            </x-container>
        </section>

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
    </main>

    <footer>
        //
    </footer>
</x-master-layout>
