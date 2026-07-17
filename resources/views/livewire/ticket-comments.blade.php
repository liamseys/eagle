@use(App\Enums\Tickets\TicketStatus)
@use(App\Filament\AvatarProviders\GravatarProvider)

<div class="flex flex-col {{ $ticket->status !== TicketStatus::CLOSED ? 'pb-6' : '' }}">
    @if (! $comments->isEmpty())
        @php $lastDate = null; @endphp

        <ol role="list" class="flex flex-col gap-5">
            @foreach ($comments as $comment)
                @php
                    $isInternal = ! $comment->is_public;
                    $isRequester = $comment->isRequester();
                    $date = $comment->created_at->toDateString();
                @endphp

                @if ($date !== $lastDate)
                    @php $lastDate = $date; @endphp

                    <li class="relative flex items-center justify-center py-1" aria-hidden="true">
                        <div class="absolute inset-x-0 h-px bg-gray-950/5 dark:bg-white/10"></div>
                        <span class="relative rounded-full bg-white px-3 py-0.5 text-xs font-medium text-gray-500 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-400 dark:ring-white/10">
                            {{ $comment->created_at->isToday() ? __('Today') : ($comment->created_at->isYesterday() ? __('Yesterday') : $comment->created_at->translatedFormat('F j, Y')) }}
                        </span>
                    </li>
                @endif

                <li id="comment-{{ $comment->id }}" class="flex scroll-mt-24 items-start gap-3">
                    <img
                        src="{{ GravatarProvider::generateGravatarUrl($comment->authorable?->email ?? '') }}"
                        alt="{{ $comment->authorable?->name }}"
                        class="size-10 flex-none rounded-full bg-gray-50 object-cover outline-1 -outline-offset-1 outline-black/5 dark:bg-white/10 dark:outline-white/10"
                    />

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <p class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $comment->authorable?->name ?? __('Unknown') }}
                            </p>

                            @if ($isInternal)
                                <x-filament::badge color="warning" icon="heroicon-m-lock-closed">{{ __('Internal note') }}</x-filament::badge>
                            @elseif ($isRequester)
                                <x-filament::badge color="info">{{ __('Requester') }}</x-filament::badge>
                            @else
                                <x-filament::badge color="gray">{{ __('Agent') }}</x-filament::badge>
                            @endif

                            <time
                                datetime="{{ $comment->created_at->toIso8601String() }}"
                                title="{{ $comment->created_at->translatedFormat('M j, Y · g:i A') }}"
                                class="text-xs text-gray-400 dark:text-gray-500"
                            >{{ $comment->created_at->diffForHumans() }}</time>
                        </div>

                        <div @class([
                            'mt-1.5 rounded-xl border-l-2 px-4 py-3 ring-1',
                            'border-l-amber-400 bg-amber-50 ring-amber-950/5 dark:border-l-amber-400/60 dark:bg-amber-400/10 dark:ring-amber-400/15' => $isInternal,
                            'border-l-blue-400 bg-white ring-gray-950/5 dark:border-l-blue-400/60 dark:bg-white/5 dark:ring-white/10' => ! $isInternal && $isRequester,
                            'border-l-gray-300 bg-white ring-gray-950/5 dark:border-l-white/20 dark:bg-white/5 dark:ring-white/10' => ! $isInternal && ! $isRequester,
                        ])>
                            <div class="ticket-comment-body">{!! $comment->body !!}</div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        <div class="flex flex-col items-center justify-center gap-4 py-10 text-center">
            <img src="{{ asset('img/no_messages.svg') }}" alt="" class="h-24">

            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-950 dark:text-white">{{ __('No messages yet') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('The conversation with the requester will appear here.') }}</p>
            </div>
        </div>
    @endif
</div>
