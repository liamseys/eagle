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

                        <form method="POST" action="{{ route('forms.submit') }}">
                            @csrf

                            <x-honeypot/>
                            <input type="hidden" name="form_id" value="{{ $form->id }}">

                            <div class="flex flex-col space-y-4">
                                @if (session('status'))
                                    <div>
                                        <p class="text-sm text-green-500">{{ session('status') }}</p>
                                    </div>
                                @endif

                                @foreach($form->fields()
                                              ->orderBy('sort')
                                              ->get() as $formField)
                                    <div class="flex flex-col gap-1">
                                        <x-label for="{{ $formField->name }}">{{ $formField->label }}</x-label>
                                        @switch($formField->type)
                                            @case(FormFieldType::TEXTAREA)
                                                <x-textarea name="{{ $formField->name }}"
                                                          id="{{ $formField->name }}" :required="$formField->is_required"></x-textarea>
                                                @break

                                            @case(FormFieldType::CHECKBOX)
                                                <input type="checkbox" name="{{ $formField->name }}"
                                                       id="{{ $formField->name }}" {{ $formField->is_required ? 'required' : '' }}>
                                                @break

                                            @case(FormFieldType::RADIO)
                                                <input type="radio" name="{{ $formField->name }}"
                                                       id="{{ $formField->name }}" {{ $formField->is_required ? 'required' : '' }}>
                                                @break

                                            @case(FormFieldType::SELECT)
                                                <x-select name="{{ $formField->name }}"
                                                          id="{{ $formField->name }}" :required="$formField->is_required">
                                                    <option value="">{{ __('Select an option') }}</option>
                                                    @foreach($formField->options as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </x-select>
                                                @break

                                            @case(FormFieldType::EMAIL)
                                                <x-input type="email" name="{{ $formField->name }}"
                                                         id="{{ $formField->name }}" :required="$formField->is_required"/>
                                                @break

                                            @case(FormFieldType::DATE)
                                                <x-input type="date" name="{{ $formField->name }}"
                                                         id="{{ $formField->name }}" :required="$formField->is_required"/>
                                                @break

                                            @case(FormFieldType::DATETIME_LOCAL)
                                                <x-input type="datetime-local" name="{{ $formField->name }}"
                                                         id="{{ $formField->name }}" :required="$formField->is_required"/>
                                                @break

                                            @default
                                                <x-input type="text" name="{{ $formField->name }}"
                                                         id="{{ $formField->name }}" :required="$formField->is_required"/>
                                        @endswitch

                                        @isset($formField->description)
                                            <p class="break-words text-sm text-gray-500">{{ $formField->description }}</p>
                                        @endisset
                                    </div>
                                @endforeach

                                <button type="submit">{{ __('Submit') }}</button>
                            </div>
                        </form>
                    </x-card>
                </div>
                <div class="col-span-1">
                    //
                </div>
            </div>
        </x-container>
    </section>
</x-app-layout>
