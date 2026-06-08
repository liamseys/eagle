@php
    $showRequester = $showRequester ?? true;
    $segments = [];

    if ($showRequester && $record->requester) {
        $segments[] = $record->requester->name;
    }

    $segments[] = $record->created_at->diffForHumans();

    if ($record->duplicateOf?->ticket_id) {
        $segments[] = __('Duplicate of #:id', ['id' => $record->duplicateOf->ticket_id]);
    }
@endphp

<span class="fi-ticket-meta">#{{ $record->ticket_id }}<x-copy-button
        :value="$record->ticket_id"
        label="ticket ID"
        class="ml-1"
    />@if (! empty($segments)) <span>· {{ implode(' · ', $segments) }}</span>@endif</span>
