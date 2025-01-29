@unless ($breadcrumbs->isEmpty())
    <nav>
        <ol class="p-4 bg-gray-100 text-sm text-gray-800">
            <x-container class="max-w-7xl flex flex-wrap">
                @foreach ($breadcrumbs as $breadcrumb)

                    @if ($breadcrumb->url && !$loop->last)
                        <li>
                            <a href="{{ $breadcrumb->url }}"
                               class="text-blue-600 hover:text-blue-900 hover:underline focus:text-blue-900 focus:underline">
                                {{ $breadcrumb->title }}
                            </a>
                        </li>
                    @else
                        <li>
                            {{ $breadcrumb->title }}
                        </li>
                    @endif

                    @unless($loop->last)
                        <li class="text-gray-500 px-2">
                            /
                        </li>
                    @endif

                @endforeach
            </x-container>
        </ol>
    </nav>
@endunless
