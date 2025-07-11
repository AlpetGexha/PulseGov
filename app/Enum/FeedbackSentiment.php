<?php

declare(strict_types=1);

namespace App\Enum;

use App\Traits\EnumHelper;

enum FeedbackSentiment: string
{
    use EnumHelper;

    case POSITIVE = 'positive';
    case NEGATIVE = 'negative';
    case NEUTRAL = 'neutral';

    /**
     * Get a friendly, displayable name for the enum value.
     */
    public function label(): string
    {
        return match ($this) {
            self::POSITIVE => 'Positive',
            self::NEGATIVE => 'Negative',
            self::NEUTRAL => 'Neutral',
        };
    }

    /**
     * Get color for the sentiment that can be used in UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::POSITIVE => 'green',
            self::NEGATIVE => 'red',
            self::NEUTRAL => 'gray',
        };
    }
}
