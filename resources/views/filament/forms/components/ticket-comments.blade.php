<div {{ $attributes }}>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Comments') }}
        </x-slot>

        <livewire:ticket-comments :ticket="$getRecord()"/>

        <livewire:create-ticket-comment :ticket="$getRecord()"/>
    </x-filament::section>
</div>
