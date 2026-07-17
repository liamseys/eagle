<?php

namespace App\Actions\Forms;

use App\Ai\Agents\FormFieldGenerator;
use App\Enums\HelpCenter\Forms\FormFieldType;
use Illuminate\Support\Str;

final class GenerateFormFields
{
    /**
     * Generate form field definitions from a natural language description of the form.
     *
     * @return list<array{type: string, label: string, description: ?string, is_required: bool, is_visible: bool, options: ?array<string, string>}>
     */
    public function handle(string $description): array
    {
        $response = (new FormFieldGenerator)->prompt($description);

        return collect($response['fields'] ?? [])
            ->map(fn (array $field) => $this->normalizeField($field))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Normalize a generated field into FormField attributes, discarding invalid fields.
     *
     * @param  array<string, mixed>  $field
     * @return array{type: string, label: string, description: ?string, is_required: bool, is_visible: bool, options: ?array<string, string>}|null
     */
    private function normalizeField(array $field): ?array
    {
        $type = FormFieldType::tryFrom($field['type'] ?? '');
        $label = trim($field['label'] ?? '');

        if ($type === null || $label === '') {
            return null;
        }

        $label = Str::limit($label, 255, '');
        $description = trim($field['description'] ?? '');

        return [
            'type' => $type->value,
            'label' => $label,
            'description' => $description === '' ? null : Str::limit($description, 255, ''),
            'is_required' => (bool) ($field['is_required'] ?? false),
            'is_visible' => true,
            'options' => $this->normalizeOptions($type, $field['options'] ?? [], $label),
        ];
    }

    /**
     * Build the value => label option map for choice fields.
     *
     * @param  array<int, mixed>  $options
     * @return array<string, string>|null
     */
    private function normalizeOptions(FormFieldType $type, array $options, string $label): ?array
    {
        if (! in_array($type, [FormFieldType::CHECKBOX, FormFieldType::RADIO, FormFieldType::SELECT], true)) {
            return null;
        }

        $options = collect($options)
            ->map(fn ($option): string => trim((string) $option))
            ->filter()
            ->mapWithKeys(fn (string $option) => [Str::slug($option, '_') => $option])
            ->all();

        // Choice fields cannot render without options, so fall back to a single
        // option based on the label (e.g. a lone confirmation checkbox).
        return $options === [] ? [Str::slug($label, '_') => $label] : $options;
    }
}
