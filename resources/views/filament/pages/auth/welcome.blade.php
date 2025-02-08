<x-filament-panels::page.simple>
    <form wire:submit="savePassword">
        <div class="flex flex-col gap-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" class="w-full">
                {{ __('Save password and login') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page.simple>
