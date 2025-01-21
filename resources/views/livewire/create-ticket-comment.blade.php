<div>
    <x-filament::section>
        <form>
            {{ $this->form }}

            <x-slot name="footerActions">
                <x-filament::button wire:click="create">
                    <div class="flex items-center gap-1">
                        <x-heroicon-s-paper-airplane class="h-4 w-4"/>
                        {{ __('Send') }}
                    </div>
                </x-filament::button>
            </x-slot>
        </form>
    </x-filament::section>

    <x-filament-actions::modals />
</div>
