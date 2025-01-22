<div>
    <form>
        {{ $this->form }}

        <x-filament::button wire:click="create" class="mt-6">
            <div class="flex items-center gap-1">
                <x-heroicon-s-paper-airplane class="h-4 w-4"/>
                {{ __('Send') }}
            </div>
        </x-filament::button>
    </form>

    <x-filament-actions::modals />
</div>
