<?php

declare(strict_types=1);

namespace App\Enum;

use App\Traits\EnumHelper;

enum UserRole: string
{
    use EnumHelper;

    case ADMIN = 'admin';
    case CITIZEN = 'citizen';

    /**
     * Get a friendly, displayable name for the enum value.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'admin',
            self::CITIZEN => 'citizen',
        };
    }
}
