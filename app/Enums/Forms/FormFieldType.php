<?php

namespace App\Enums\Forms;

enum FormFieldType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EMAIL = 'email';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case SELECT = 'select';
    case DATE = 'date';
    case DATETIME_LOCAL = 'datetime-local';
}
