<div class="flex flex-col space-y-6 pb-6">
    @foreach($comments as $comment)
        @php
            $bgColor = $comment->is_public
                       ? ($comment->isRequester() ? 'bg-gray-100' : 'bg-blue-100')
                       : 'bg-yellow-100';
        @endphp

        <div
            class="{{ $comment->isRequester() ? 'flex items-start gap-2' : 'flex flex-row-reverse items-start gap-2' }}">
            <img src="https://gravatar.com/avatar/{{ md5($comment->authorable->email) }}?d=mp"
                 alt="{{ $comment->authorable->name }}"
                 class="h-10 w-10 rounded-full"/>

            <div class="w-full sm:w-3/4 p-4 rounded-lg {{ $bgColor }}">
                <div class="flex flex-col space-y-1">
                    <p class="font-semibold">{{ $comment->authorable->name }}</p>
                    <div class="ticket-comment-body">{!! $comment->body !!}</div>
                    <p class="text-xs text-gray-500">{{ $comment->created_at->format('jS F Y, H:i') }}</p>

                    @if(!$comment->is_public)
                        <p class="text-xs text-yellow-600">
                            {{ __('Internal only') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
