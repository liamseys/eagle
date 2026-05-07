<div>
    <x-alert icon="clock">
        {{ __('This ticket is scheduled to be closed on :date.', [
            'date' => $getRecord()->scheduled_close_at?->format('M j, Y g:i A'),
        ]) }}
    </x-alert>
</div>
