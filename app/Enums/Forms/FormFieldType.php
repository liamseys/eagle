<?php

namespace App\Enums\Forms;

use Filament\Support\Contracts\HasLabel;

enum FormFieldType: string implements HasLabel
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EMAIL = 'email';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case SELECT = 'select';
    case DATE = 'date';
    case DATETIME_LOCAL = 'datetime-local';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::TEXTAREA => 'Textarea',
            self::EMAIL => 'Email',
            self::CHECKBOX => 'Checkbox',
            self::RADIO => 'Radio',
            self::SELECT => 'Select',
            self::DATE => 'Date',
            self::DATETIME_LOCAL => 'Datetime Local',
        };
    }
}
