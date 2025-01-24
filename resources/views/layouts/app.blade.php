<x-master-layout class="bg-white">
    <header class="bg-black">
        <x-container>
            <div class="relative flex h-16 items-center justify-between">
                <a href="{{ route('index') }}">
                    <img src="{{ asset('img/logo/logo-white.svg') }}"
                         alt="{{ config('app.name') }}"
                         class="h-8">
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
