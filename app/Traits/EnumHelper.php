<?php

declare(strict_types=1);

namespace App\Traits;

trait EnumHelper
{
    /**
     * Get all enum values as an array for dropdowns.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    /**
     * Get all enum values as an array for validation.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return collect(self::cases())
            ->map(fn ($case) => $case->value)
            ->toArray();
    }

    /**
     * Find enum case by value.
     */
    public static function tryFromValue(?string $value): ?static
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }

    /**
     * Get label for the enum value.
     */
    public function label(): string
    {
        return ucfirst(str_replace('_', ' ', $this->value));
    }
}
