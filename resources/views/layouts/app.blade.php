<x-master-layout class="bg-white">
    <header class="bg-black">
        <x-container>
            <div class="relative flex h-16 items-center justify-between">
                <a href="{{ route('index') }}" class="flex items-center gap-2">
                    <img src="{{ asset('img/logo/logo-white.svg') }}"
                         alt="{{ config('app.name') }}"
                         class="h-8">
                    <span class="px-2 py-1 text-xs font-bold bg-white rounded-lg">
                        {{ __('Help Center') }}
                    </span>
                </a>
            </div>
        </x-container>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer>
        //
    </footer>
</x-master-layout>
