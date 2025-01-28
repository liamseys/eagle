@use('App\Enums\HelpCenter\Forms\FormFieldType')

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

                        @foreach($form->fields as $formField)
                            <div>
                                <label for="{{ $formField->name }}">{{ $formField->label }}</label>
                                @switch($formField->type)
                                    @case(FormFieldType::TEXTAREA)
                                        <textarea name="{{ $formField->name }}" id="{{ $formField->name }}"></textarea>
                                        @break

                                    @case(FormFieldType::CHECKBOX)
                                        <input type="checkbox" name="{{ $formField->name }}"
                                               id="{{ $formField->name }}">
                                        @break

                                    @case(FormFieldType::RADIO)
                                        <input type="radio" name="{{ $formField->name }}" id="{{ $formField->name }}">
                                        @break

                                    @case(FormFieldType::SELECT)
                                        <select name="{{ $formField->name }}" id="{{ $formField->name }}">
                                            @foreach($formField->options as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case(FormFieldType::EMAIL)
                                        <input type="email" name="{{ $formField->name }}" id="{{ $formField->name }}">
                                        @break

                                    @case(FormFieldType::DATE)
                                        <input type="date" name="{{ $formField->name }}" id="{{ $formField->name }}">
                                        @break

                                    @case(FormFieldType::DATETIME_LOCAL)
                                        <input type="datetime-local" name="{{ $formField->name }}"
                                               id="{{ $formField->name }}">
                                        @break

                                    @default
                                        <input type="text" name="{{ $formField->name }}" id="{{ $formField->name }}">
                                @endswitch
                            </div>
                        @endforeach
                    </x-card>
                </div>
                <div class="col-span-1">
                    //
                </div>
            </div>
        </x-container>
    </section>
</x-app-layout>
