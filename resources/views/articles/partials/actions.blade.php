@use('App\Enums\HelpCenter\Articles\ArticleStatus')

<div x-data="{ showShareModal: false }" class="flex items-center gap-4">
    <!-- Share Article -->
    <a
        href="#"
        @click.prevent="showShareModal = true"
        class="flex items-center gap-1 text-sm text-gray-700 hover:underline"
    >
        <x-heroicon-s-share class="w-4 h-4"/>
        {{ __('Share Article') }}
    </a>

    @if(auth()->check() && auth()->user()->hasPermissionTo('hc-articles'))
        <!-- Edit Article -->
        <a
            href="{{ route('filament.app.help-center.resources.articles.edit', $article) }}"
            target="_blank"
            class="flex items-center gap-1 text-sm text-gray-700 hover:underline"
        >
            <x-heroicon-s-pencil-square class="w-4 h-4"/>
            {{ __('Edit Article') }}
        </a>

        @if ($article->status === ArticleStatus::PUBLISHED)
            <!-- Unpublish Article -->
            <a
                href="{{ route('articles.unpublish', $article) }}"
                class="flex items-center gap-1 text-sm text-gray-700 hover:underline"
            >
                <x-heroicon-s-eye-slash class="w-4 h-4"/>
                {{ __('Unpublish Article') }}
            </a>
        @else
            <!-- Publish Article -->
            <a
                href="{{ route('articles.publish', $article) }}"
                class="flex items-center gap-1 text-sm text-gray-700 hover:underline"
            >
                <x-heroicon-s-eye class="w-4 h-4"/>
                {{ __('Publish Article') }}
            </a>
        @endif
    @endif

    <!-- Share Modal -->
    <div
        x-show="showShareModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div
            x-show="showShareModal"
            @click.away="showShareModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="scale-95 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="scale-100 opacity-100"
            x-transition:leave-end="scale-95 opacity-0"
            class="relative w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl"
        >
            <!-- Close Button -->
            <button
                @click="showShareModal = false"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition"
            >
                <x-heroicon-s-x-mark class="w-5 h-5"/>
            </button>

            <!-- Modal Content -->
            <h2 class="mb-4 text-lg font-semibold text-gray-800">{{ __('Share this article') }}</h2>

            <x-input
                type="text"
                readonly
                value="{{ route('articles.show', $article) }}"
                onclick="this.select()"
                class="w-full"
            />

            <p class="mt-2 text-sm text-gray-500">
                {{ __('Copy and share the link above.') }}
            </p>
        </div>
    </div>
</div>
