<x-master-layout class="bg-white">
    <header class="bg-black">
        <x-container>
            <div class="relative flex h-16 items-center justify-between">
                <a href="{{ route('index') }}" class="flex items-center gap-2">
                    <img src="{{ !empty($generalSettings->branding_logo_white) ? Storage::url($generalSettings->branding_logo_white) : asset('img/logo/logo-white.svg') }}"
                         alt="{{ !empty($generalSettings->app_name) ? $generalSettings->app_name : config('app.name') }}"
                         class="h-8">
                    <span class="px-2 py-1 text-xs font-bold bg-white rounded-lg">
                        {{ __('Help Center') }}
                    </span>
                </a>
            </div>
        </x-container>
    </header>

    <main class="min-h-screen">
        {{ $slot }}
    </main>

    <footer class="relative bottom-0 w-full bg-gray-100 py-4">
        <x-container>
            <div class="flex flex-col items-center justify-between sm:flex-row">
                <p class="text-sm text-gray-500">
                    {{ __('© :year :name.', ['year' => date('Y'), 'name' => !empty($generalSettings->app_name) ? $generalSettings->app_name : config('app.name')]) }}
                </p>

                <ul class="mt-2 flex space-x-4 sm:mt-0">
                    <li>
                        <a href="{{ route('filament.app.auth.login') }}"
                           class="text-sm text-gray-500 transition hover:underline hover:text-gray-700">
                            {{ __('Agent login') }}
                        </a>
                    </li>
                </ul>
            </div>
        </x-container>
    </footer>
</x-master-layout>
