@unless ($breadcrumbs->isEmpty())
    <nav class="border-b border-gray-950/5 bg-white">
        <x-container class="max-w-7xl">
            <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 py-3 text-sm text-gray-500" role="list">
                @foreach ($breadcrumbs as $breadcrumb)
                    @if ($breadcrumb->url && !$loop->last)
                        <li>
                            <a href="{{ $breadcrumb->url }}"
                               class="text-gray-500 transition hover:text-gray-900">
                                {{ $breadcrumb->title }}
                            </a>
                        </li>
                    @else
                        <li class="font-medium text-gray-900" aria-current="page">
                            {{ $breadcrumb->title }}
                        </li>
                    @endif

                    @unless($loop->last)
                        <li class="text-gray-300" aria-hidden="true">/</li>
                    @endunless
                @endforeach
            </ol>
        </x-container>
    </nav>
@endunless
