<?php

namespace App\Enums\Tickets;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SlaState: string implements HasColor, HasIcon, HasLabel
{
    case OnTrack = 'on_track';
    case AtRisk = 'at_risk';
    case Breached = 'breached';
    case Met = 'met';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OnTrack => 'success',
            self::AtRisk => 'warning',
            self::Breached => 'danger',
            self::Met => 'gray',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OnTrack => 'On track',
            self::AtRisk => 'At risk',
            self::Breached => 'Breached',
            self::Met => 'Met',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::OnTrack => 'heroicon-o-shield-check',
            self::AtRisk => 'heroicon-o-exclamation-triangle',
            self::Breached => 'heroicon-o-fire',
            self::Met => 'heroicon-o-check-circle',
        };
    }

    /**
     * Relative severity, used to surface the worst state across multiple targets.
     */
    public function severity(): int
    {
        return match ($this) {
            self::Met => 0,
            self::OnTrack => 1,
            self::AtRisk => 2,
            self::Breached => 3,
        };
    }
}
