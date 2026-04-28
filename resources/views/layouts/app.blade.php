<x-master-layout class="bg-gray-50/50">
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

    <main class="min-h-dvh">
        {{ $slot }}
    </main>

    <footer class="border-t border-gray-950/5 bg-white py-6">
        <x-container>
            <div class="flex flex-col items-center justify-between gap-3 sm:flex-row sm:gap-0">
                <p class="text-sm text-gray-500">
                    {{ __('© :year :name.', ['year' => date('Y'), 'name' => !empty($generalSettings->app_name) ? $generalSettings->app_name : config('app.name')]) }}
                </p>

                <ul class="flex items-center gap-x-4" role="list">
                    <li>
                        <a href="{{ route('filament.app.auth.login') }}"
                           class="text-sm text-gray-500 transition hover:text-gray-900">
                            {{ __('Agent login') }}
                        </a>
                    </li>
                </ul>
            </div>
        </x-container>
    </footer>
</x-master-layout>
