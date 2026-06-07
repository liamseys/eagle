@php
    $segments = [];

    if ($record->requester) {
        $segments[] = $record->requester->name;
    }

    $segments[] = $record->created_at->diffForHumans();

    if ($record->duplicateOf?->ticket_id) {
        $segments[] = __('Duplicate of #:id', ['id' => $record->duplicateOf->ticket_id]);
    }
@endphp

<span class="fi-ticket-meta">#{{ $record->ticket_id }}<span
        role="button"
        tabindex="0"
        x-data="{
            copied: false,
            timer: null,
            copy() {
                const value = '{{ $record->ticket_id }}';

                if (window.navigator.clipboard && window.isSecureContext) {
                    window.navigator.clipboard.writeText(value);
                } else {
                    const area = document.createElement('textarea');
                    area.value = value;
                    area.style.position = 'fixed';
                    area.style.opacity = '0';
                    document.body.appendChild(area);
                    area.select();
                    try { document.execCommand('copy'); } catch (error) {}
                    area.remove();
                }

                this.copied = true;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.copied = false }, 1500);
            },
        }"
        x-on:click.stop.prevent="copy()"
        x-on:keydown.enter.stop.prevent="copy()"
        x-on:keydown.space.stop.prevent="copy()"
        x-bind:aria-label="copied ? @js(__('Ticket ID copied')) : @js(__('Copy ticket ID'))"
        x-bind:title="copied ? @js(__('Ticket ID copied')) : @js(__('Copy ticket ID'))"
        class="ml-1 inline-flex shrink-0 cursor-pointer items-center rounded align-middle text-gray-400 transition hover:text-gray-600 focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-primary-500 dark:text-gray-500 dark:hover:text-gray-300"
    ><span x-show="! copied" class="inline-flex"><x-filament::icon icon="heroicon-m-clipboard-document" class="size-3.5" /></span><span
            x-show="copied"
            x-cloak
            class="inline-flex text-success-600 dark:text-success-400"
        ><x-filament::icon icon="heroicon-m-check" class="size-3.5" /></span></span>@if (! empty($segments)) <span>· {{ implode(' · ', $segments) }}</span>@endif</span>
