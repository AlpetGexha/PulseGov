<?php

declare(strict_types=1);

namespace App\Enum;

use App\Traits\EnumHelper;

enum FeedbackStatus: string
{
    use EnumHelper;

    case UNDER_REVIEW = 'under_review';
    case RESOLVED = 'resolved';
    case IMPLEMENTED = 'implemented';

    /**
     * Get a friendly, displayable name for the enum value.
     */
    public function label(): string
    {
        return match ($this) {
            self::UNDER_REVIEW => 'Under Review',
            self::RESOLVED => 'Resolved',
            self::IMPLEMENTED => 'Implemented',
        };
    }

    /**
     * Get color for the status that can be used in UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::UNDER_REVIEW => 'yellow',
            self::RESOLVED => 'blue',
            self::IMPLEMENTED => 'green',
        };
    }

    /**
     * Get icon name for the status that can be used in UI.
     */
    public function icon(): string
    {
        return match ($this) {
            self::UNDER_REVIEW => 'clock',
            self::RESOLVED => 'check-circle',
            self::IMPLEMENTED => 'rocket-launch',
        };
    }
}
