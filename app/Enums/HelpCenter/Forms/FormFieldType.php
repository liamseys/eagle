<?php

namespace App\Enums\HelpCenter\Forms;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FormFieldType: string implements HasColor, HasIcon, HasLabel
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EMAIL = 'email';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case SELECT = 'select';
    case DATE = 'date';
    case DATETIME_LOCAL = 'datetime-local';

    public function getIcon(): ?string
    {
        return match ($this) {
            self::TEXT => 'heroicon-o-pencil-square',
            self::TEXTAREA => 'heroicon-o-document-text',
            self::EMAIL => 'heroicon-o-envelope',
            self::CHECKBOX => 'heroicon-o-check-circle',
            self::RADIO => 'heroicon-o-check',
            self::SELECT => 'heroicon-o-list-bullet',
            self::DATE => 'heroicon-o-calendar',
            self::DATETIME_LOCAL => 'heroicon-o-clock',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::TEXT => 'primary',
            self::TEXTAREA => 'gray',
            self::EMAIL => 'info',
            self::CHECKBOX => 'success',
            self::RADIO => 'warning',
            self::SELECT => 'primary',
            self::DATE => 'danger',
            self::DATETIME_LOCAL => 'info',
        };
    }

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
