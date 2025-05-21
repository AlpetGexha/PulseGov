<?php

namespace App\Enum;

use App\Traits\EnumHelper;

enum UrgencyLevel: string
{
    use EnumHelper;

    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    /**
     * Get a friendly, displayable name for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::HIGH => 'High',
            self::MEDIUM => 'Medium',
            self::LOW => 'Low',
        };
    }

    /**
     * Get color for the urgency level that can be used in UI.
     *
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::HIGH => 'red',
            self::MEDIUM => 'yellow',
            self::LOW => 'green',
        };
    }

    /**
     * Get priority value for sorting.
     *
     * @return int
     */
    public function priority(): int
    {
        return match($this) {
            self::HIGH => 1,
            self::MEDIUM => 2,
            self::LOW => 3,
        };
    }
}
