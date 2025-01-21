<div>
    <form>
        {{ $this->form }}

        <x-filament::button wire:click="create">
            {{ __('Submit') }}
        </x-filament::button>
    </form>

    <x-filament-actions::modals />
</div>
