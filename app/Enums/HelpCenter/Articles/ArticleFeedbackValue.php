<?php

namespace App\Enums\HelpCenter\Articles;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ArticleFeedbackValue: string implements HasColor, HasIcon, HasLabel
{
    case Negative = 'negative';
    case Neutral = 'neutral';
    case Positive = 'positive';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Negative => 'danger',
            self::Neutral => 'warning',
            self::Positive => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Negative => 'heroicon-o-face-frown',
            self::Neutral => 'heroicon-o-minus-circle',
            self::Positive => 'heroicon-o-face-smile',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Negative => __('No'),
            self::Neutral => __('Neutral'),
            self::Positive => __('Yes'),
        };
    }

    public function getEmoji(): string
    {
        return match ($this) {
            self::Negative => '😞',
            self::Neutral => '😐',
            self::Positive => '😀',
        };
    }
}
