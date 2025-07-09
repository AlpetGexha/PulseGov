<?php

namespace App\Enum;

use App\Traits\EnumHelper;

enum UrgencyLevel: string
{
    use EnumHelper;

    case CRITICAL = 'critical';
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
            self::CRITICAL => 'Critical',
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
            self::CRITICAL => 'red',
            self::HIGH => 'orange',
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
            self::CRITICAL => 1,
            self::HIGH => 2,
            self::MEDIUM => 3,
            self::LOW => 4,
        };
    }
}
