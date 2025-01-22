<div>
    <form>
        {{ $this->form }}

        <div class="pt-6">
            <x-filament::button wire:click="create">
                <div class="flex items-center gap-1">
                    <x-heroicon-s-paper-airplane class="h-4 w-4"/>
                    {{ __('Send') }}
                </div>
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals/>
</div>
