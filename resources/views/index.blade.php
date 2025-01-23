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
                    <p class="w-full lg:w-1/2 text-white">{{ __('Here you\'ll find answers to many common questions about our services, policies, and features. If you need further assistance, our team is always ready to help you find the information or support you need.') }}</p>
                </div>
            </x-container>
        </section>
    </main>

    <footer>
        //
    </footer>
</x-master-layout>
