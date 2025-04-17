<div>
    <x-alert>
        {!! __(
            'This ticket is marked as duplicate of :link.',
            [
                'link' => '<a
                    href="' . route('filament.app.resources.tickets.edit', $getRecord()->mainTicket) . '"
                    target="_blank"
                    class="hover:underline"
                >#' . $getRecord()->mainTicket->ticket_id . '</a>'
            ]
        ) !!}
    </x-alert>
</div>
