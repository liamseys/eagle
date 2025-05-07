<?php

namespace App\Casts;

use App\Enums\HelpCenter\Forms\FormFieldType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class StringOrArray implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $model->relationLoaded('formField')) {
            $model->load('formField');
        }

        if ($model->formField->type === FormFieldType::CHECKBOX) {
            return json_decode($value, true);
        }

        return (string) $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $model->relationLoaded('formField')) {
            $model->load('formField');
        }

        if ($model->formField->type === FormFieldType::CHECKBOX) {
            return json_encode((array) $value);
        }

        return $value;
    }
}
