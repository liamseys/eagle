@use('App\Enums\HelpCenter\Forms\FormFieldType')
@use('App\Settings\GeneralSettings')

<x-app-layout>
    <x-hero :title="__('Help Center')"/>

    <section class="py-12">
        <x-container class="max-w-7xl">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-4 sm:gap-4">
                <div class="col-span-1">
                    <x-card>
                        <x-slot name="header">
                            <h3 class="font-semibold">{{ __('Other categories') }}</h3>
                        </x-slot>

                        <ul class="-mx-4 flex flex-col space-y-2 -my-4">
                            @foreach($categories as $category)
                                <x-nav-link :href="route('categories.show', $category)">
                                    <p class="text-sm">{{ $category->name }}</p>
                                    <x-heroicon-s-chevron-right class="h-4 w-4"/>
                                </x-nav-link>
                            @endforeach
                        </ul>
                    </x-card>
                </div>
                <div class="col-span-2 flex flex-col space-y-4">
                    @if(!$form->is_active)
                        <x-alert icon="information-circle">
                            {{ __('This form is inactive. You can see it because you\'re logged in as an agent.') }}
                        </x-alert>
                    @endif

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
                                    <div class="w-full sm:w-3/5 flex flex-col gap-1">
                                        <x-label for="{{ $formField->name }}">
                                            {{ $formField->label }}
                                            @if($formField->is_required)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </x-label>
                                        @switch($formField->type)
                                            @case(FormFieldType::TEXTAREA)
                                                <x-textarea name="{{ $formField->name }}"
                                                            id="{{ $formField->name }}"
                                                            :required="$formField->is_required"></x-textarea>
                                                @break

                                            @case(FormFieldType::CHECKBOX)
                                                <fieldset class="flex flex-col space-y-1">
                                                    @foreach($formField->options as $value => $label)
                                                        <div class="flex flex-row items-center gap-2">
                                                            <x-checkbox name="{{ $formField->name }}[]"
                                                                        value="{{ $value }}"
                                                                        id="{{ $formField->name . '_' . $value }}"/>
                                                            <x-label
                                                                for="{{ $formField->name . '_' . $value }}">{{ $label }}</x-label>
                                                        </div>
                                                    @endforeach
                                                </fieldset>
                                                @break

                                            @case(FormFieldType::RADIO)
                                                <fieldset class="flex flex-col space-y-1">
                                                    @foreach($formField->options as $value => $label)
                                                        <div class="flex flex-row items-center gap-2">
                                                            <input type="radio" name="{{ $formField->name }}"
                                                                   id="{{ $formField->name . '_' . $value }}"
                                                                   value="{{ $value }}"
                                                                {{ $formField->is_required ? 'required' : '' }}>
                                                            <x-label
                                                                for="{{ $formField->name . '_' . $value }}">{{ $label }}</x-label>
                                                        </div>
                                                    @endforeach
                                                </fieldset>
                                                @break

                                            @case(FormFieldType::SELECT)
                                                <x-select name="{{ $formField->name }}"
                                                          id="{{ $formField->name }}"
                                                          :required="$formField->is_required">
                                                    <option value="">{{ __('Select an option') }}</option>
                                                    @foreach($formField->options as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </x-select>
                                                @break

                                            @case(FormFieldType::EMAIL)
                                                <x-input type="email" name="{{ $formField->name }}"
                                                         id="{{ $formField->name }}"
                                                         :required="$formField->is_required"/>
                                                @break

                                            @case(FormFieldType::DATE)
                                                <x-input type="date" name="{{ $formField->name }}"
                                                         id="{{ $formField->name }}"
                                                         :required="$formField->is_required"/>
                                                @break

                                            @case(FormFieldType::DATETIME_LOCAL)
                                                <x-input type="datetime-local" name="{{ $formField->name }}"
                                                         id="{{ $formField->name }}"
                                                         :required="$formField->is_required"/>
                                                @break

                                            @default
                                                <x-input type="text" name="{{ $formField->name }}"
                                                         id="{{ $formField->name }}"
                                                         :required="$formField->is_required"/>
                                        @endswitch

                                        @isset($formField->description)
                                            <p class="break-words text-sm text-gray-500">{{ $formField->description }}</p>
                                        @endisset
                                    </div>
                                @endforeach

                                <p class="text-xs text-gray-500">{{ __('* This field is required') }}</p>
                                <p class="w-full sm:w-3/5 text-xs text-gray-500">
                                    @php
                                        $generalSettings = app(GeneralSettings::class);
                                    @endphp

                                    {!! __('Some system info is sent to :name. It helps improve support, fix issues, and make products better, in line with the <a href=":privacy_policy" class="underline">Privacy Policy</a> and <a href=":terms_of_service" class="underline">Terms of Service</a>.', [
                                        'name' => $generalSettings->app_name,
                                        'privacy_policy' => config('app.privacy_policy'),
                                        'terms_of_service' => config('app.terms_of_service'),
                                    ]) !!}
                                </p>

                                <div class="flex items-start">
                                    <x-button type="submit">{{ __('Submit') }}</x-button>
                                </div>
                            </div>
                        </form>
                    </x-card>

                    @include('forms.partials.actions')
                </div>
            </div>
        </x-container>
    </section>
</x-app-layout>
