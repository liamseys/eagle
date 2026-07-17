@use(App\Enums\HelpCenter\Forms\FormFieldType)

<div class="space-y-2">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ trans_choice('{1} :count field will be added to the form.|[2,*] :count fields will be added to the form.', count($fields)) }}
    </p>

    <ul role="list" class="space-y-2">
        @foreach ($fields as $field)
            @php
                $type = FormFieldType::tryFrom($field['type'] ?? '');
            @endphp

            <li class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <div class="flex items-start justify-between gap-3">
                    <span class="text-sm font-medium text-gray-950 dark:text-white">
                        {{ $field['label'] ?? '' }}

                        @if ($field['is_required'] ?? false)
                            <sup class="font-medium text-danger-600 dark:text-danger-400">*</sup>
                        @endif
                    </span>

                    @if ($type)
                        <x-filament::badge :color="$type->getColor()" :icon="$type->getIcon()" class="shrink-0">
                            {{ $type->getLabel() }}
                        </x-filament::badge>
                    @endif
                </div>

                @if (filled($field['description'] ?? null))
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $field['description'] }}
                    </p>
                @endif

                @if (filled($field['options'] ?? null))
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($field['options'] as $option)
                            <x-filament::badge color="gray">
                                {{ $option }}
                            </x-filament::badge>
                        @endforeach
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
