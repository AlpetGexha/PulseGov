<?php

namespace App\Enum;

use App\Traits\EnumHelper;

enum VoteType: string
{
    use EnumHelper;

    case UPVOTE = 'upvote';
    case DOWNVOTE = 'downvote';

    /**
     * Get a friendly, displayable name for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::UPVOTE => 'Upvote',
            self::DOWNVOTE => 'Downvote',
        };
    }

    /**
     * Get numeric value for the vote (for calculations).
     *
     * @return int
     */
    public function value(): int
    {
        return match($this) {
            self::UPVOTE => 1,
            self::DOWNVOTE => -1,
        };
    }

    /**
     * Get icon name for the vote type that can be used in UI.
     *
     * @return string
     */
    public function icon(): string
    {
        return match($this) {
            self::UPVOTE => 'thumb-up',
            self::DOWNVOTE => 'thumb-down',
        };
    }
}
