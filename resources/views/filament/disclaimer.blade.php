@php
    $linkClass = 'font-medium text-gray-700 transition hover:text-primary-600 hover:underline dark:text-gray-200 dark:hover:text-primary-400';
@endphp

<div class="p-4 antialiased">
    <div class="flex flex-col gap-2 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
        <div class="flex items-center gap-1.5">
            <x-filament::icon
                icon="heroicon-m-code-bracket"
                aria-hidden="true"
                class="size-4 shrink-0 text-gray-400 dark:text-gray-500"
            />
            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                Open source
            </p>
        </div>

        <p class="text-pretty text-xs/5 text-gray-500 dark:text-gray-400">
            This project, Eagle, is free and open source, built by
            <a href="https://github.com/liamseys" rel="nofollow noreferrer noopener" target="_blank" class="{{ $linkClass }}">Liam Seys</a>.
            Consider <a href="https://github.com/liamseys/eagle" rel="nofollow noreferrer noopener" target="_blank" class="{{ $linkClass }}">contributing</a>
            or <a href="https://github.com/sponsors/liamseys" rel="nofollow noreferrer noopener" target="_blank" class="{{ $linkClass }}">sponsoring</a>
            to support its development.
        </p>
    </div>
</div>
