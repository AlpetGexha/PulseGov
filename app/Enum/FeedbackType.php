<?php

declare(strict_types=1);

namespace App\Enum;

use App\Traits\EnumHelper;

enum FeedbackType: string
{
    use EnumHelper;

    case SUGGESTION = 'suggestion';
    case PROBLEM = 'problem';
    case PRAISE = 'praise';

    /**
     * Get a friendly, displayable name for the enum value.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUGGESTION => 'Suggestion',
            self::PROBLEM => 'Problem',
            self::PRAISE => 'Praise',
        };
    }

    /**
     * Get color for the feedback type that can be used in UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::SUGGESTION => 'blue',
            self::PROBLEM => 'red',
            self::PRAISE => 'green',
        };
    }

    /**
     * Get icon name for the feedback type that can be used in UI.
     */
    public function icon(): string
    {
        return match ($this) {
            self::SUGGESTION => 'light-bulb',
            self::PROBLEM => 'exclamation-triangle',
            self::PRAISE => 'thumb-up',
        };
    }
}
