<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    <section class="py-12">
        <x-container class="max-w-7xl">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="col-span-2">
                    <x-card>
                        <x-slot name="header">
                            <div class="flex flex-col gap-2">
                                <h2 class="text-xl font-semibold">{{ $form->name }}</h2>
                                <div class="form-description">
                                    {!! $form->description !!}
                                </div>
                            </div>
                        </x-slot>
                    </x-card>
                </div>
                <div class="col-span-1">
                    //
                </div>
            </div>
        </x-container>
    </section>
</x-app-layout>
