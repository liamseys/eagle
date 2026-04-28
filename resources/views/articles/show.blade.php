@use('App\Enums\HelpCenter\Articles\ArticleStatus')

<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    {{ Breadcrumbs::render('article', $article) }}

    <section class="py-14 sm:py-16">
        <x-container class="max-w-7xl">
            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-3 sm:gap-6">
                <aside class="sm:col-span-1">
                    <div class="sm:sticky sm:top-6">
                        <x-card>
                            <x-slot name="header">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ $article->section->name }}
                                </h3>
                            </x-slot>

                            <ul class="-mx-2 -my-2 flex flex-col" role="list">
                                @foreach($article->section
                                                 ->articles()
                                                 ->public()
                                                 ->published()
                                                 ->get() as $navArticle)
                                    <x-nav-link :href="route('articles.show', $navArticle)"
                                                :active="request()->routeIs('articles.show') && request()->route('article')?->is($navArticle)">
                                        <p class="text-sm">{{ $navArticle->title }}</p>
                                        <x-heroicon-s-chevron-right class="size-4 text-gray-400 transition group-hover:text-gray-600"/>
                                    </x-nav-link>
                                @endforeach
                            </ul>
                        </x-card>
                    </div>
                </aside>

                <div class="flex flex-col gap-5 sm:col-span-2">
                    @if (session('status'))
                        <x-alert>
                            {{ __(session('status')) }}
                        </x-alert>
                    @endif

                    @if($article->status !== ArticleStatus::PUBLISHED)
                        <x-alert icon="information-circle">
                            {{ __('This article is in draft. You can see it because you\'re logged in as an agent.') }}
                        </x-alert>
                    @endif

                    <article class="overflow-hidden rounded-2xl border border-gray-950/5 bg-white shadow-xs">
                        <header class="border-b border-gray-950/5 px-6 py-6 sm:px-8 sm:py-7">
                            <h1 class="text-balance text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
                                {{ $article->title }}
                            </h1>
                            @if(!empty($article->description))
                                <p class="mt-2 max-w-2xl text-pretty text-sm text-gray-500 sm:text-base">
                                    {{ $article->description }}
                                </p>
                            @endif
                        </header>

                        <div class="article-content px-6 py-7 sm:px-8 sm:py-9">
                            {!! $article->body !!}
                        </div>
                    </article>

                    @if($article->status === ArticleStatus::PUBLISHED)
                        <livewire:article-feedback :article="$article"/>
                    @endif

                    @include('articles.partials.actions')
                </div>
            </div>
        </x-container>
    </section>
</x-app-layout>
