<x-app-layout>
    <x-hero :title="__('Search the Help Center')">
        <x-search-bar :value="$query"/>
    </x-hero>

    {{ Breadcrumbs::render('search') }}

    <section class="py-14 sm:py-16">
        <x-container class="max-w-3xl">
            @if($query === '')
                <div class="flex flex-col items-center gap-6 text-center">
                    <img src="{{ asset('img/no_results.svg') }}" alt="Search" class="mx-auto h-24">
                    <p class="text-gray-500">{{ __('Type a search above to find articles and forms.') }}</p>
                </div>
            @elseif($articles->isEmpty() && $forms->isEmpty())
                <div class="flex flex-col items-center gap-6 text-center">
                    <img src="{{ asset('img/no_results.svg') }}" alt="No results" class="mx-auto h-24">
                    <p class="text-gray-500">{{ __('No results found for ":query".', ['query' => $query]) }}</p>
                </div>
            @else
                <div class="flex flex-col gap-10">
                    @if($articles->isNotEmpty())
                        <div>
                            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                {{ __('Articles') }} ({{ $articles->count() }})
                            </h2>

                            <ul class="mt-3 flex flex-col divide-y divide-gray-950/5 overflow-hidden rounded-2xl border border-gray-950/5 bg-white shadow-xs" role="list">
                                @foreach($articles as $article)
                                    @php
                                        $plainBody = strip_tags($article->body ?? '');
                                        $excerpt = Str::excerpt($plainBody, $query, ['radius' => 90])
                                            ?? Str::excerpt($article->description ?? '', $query, ['radius' => 90])
                                            ?? Str::limit($article->description ?: $plainBody, 160);
                                    @endphp

                                    <li>
                                        <a href="{{ route('articles.show', $article) }}"
                                           class="block px-5 py-4 transition hover:bg-gray-950/[0.02]">
                                            <div class="flex items-baseline justify-between gap-3">
                                                <p class="text-sm font-semibold tracking-tight text-gray-900">
                                                    {{ $article->title }}
                                                </p>

                                                @if($article->category)
                                                    <span class="shrink-0 text-xs text-gray-400">
                                                        {{ $article->category->name }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($excerpt)
                                                <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-gray-500">
                                                    {{ $excerpt }}
                                                </p>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($forms->isNotEmpty())
                        <div>
                            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                {{ __('Forms') }} ({{ $forms->count() }})
                            </h2>

                            <ul class="mt-3 flex flex-col divide-y divide-gray-950/5 overflow-hidden rounded-2xl border border-gray-950/5 bg-white shadow-xs" role="list">
                                @foreach($forms as $form)
                                    <li>
                                        <a href="{{ route('forms.show', $form) }}"
                                           class="block px-5 py-4 transition hover:bg-gray-950/[0.02]">
                                            <div class="flex items-baseline justify-between gap-3">
                                                <p class="text-sm font-semibold tracking-tight text-gray-900">
                                                    {{ $form->name }}
                                                </p>

                                                @if($form->section)
                                                    <span class="shrink-0 text-xs text-gray-400">
                                                        {{ $form->section->name }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($form->description)
                                                <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-gray-500">
                                                    {{ Str::limit($form->description, 160) }}
                                                </p>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </x-container>
    </section>
</x-app-layout>
