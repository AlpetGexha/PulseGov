<?php

namespace App\Enum;

use App\Traits\EnumHelper;

enum UserRole: string
{
    use EnumHelper;

    case ADMIN = 'admin';
    case CITIZEN = 'citizen';

    /**
     * Get a friendly, displayable name for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::CITIZEN => 'Citizen',
        };
    }
}
