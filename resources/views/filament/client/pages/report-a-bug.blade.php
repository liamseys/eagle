<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-3">
        <x-filament::section class="col-span-2">
            <x-slot name="description">
                {{ __('Notice something not working as it should? Let us know by filling out the form below. The more details you provide, the quicker we can investigate and fix the issue. Thanks for helping us improve!') }}
            </x-slot>

            <form wire:submit="create">
                {{ $this->form }}

                <div class="pt-6">
                    <x-filament::button type="submit">
                        {{ __('Submit') }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>

    <x-filament-actions::modals/>
</x-filament-panels::page>
