<?php

declare(strict_types=1);

namespace App\Traits;

use App\Rules\EnumRule;

trait FormRequestHelper
{
    /**
     * Create a validation rule for an enum field.
     */
    protected function enumRule(string $enumClass, bool $nullable = false): array
    {
        $rules = [new EnumRule($enumClass)];

        if ($nullable) {
            array_unshift($rules, 'nullable');
        } else {
            array_unshift($rules, 'required');
        }

        return $rules;
    }

    /**
     * Get an array of standard rules for a database string field.
     */
    protected function stringRule(int $maxLength = 255, bool $nullable = false): array
    {
        $rules = ['string', "max:{$maxLength}"];

        if ($nullable) {
            array_unshift($rules, 'nullable');
        } else {
            array_unshift($rules, 'required');
        }

        return $rules;
    }

    /**
     * Get an array of standard rules for an integer field.
     */
    protected function integerRule(bool $nullable = false, ?int $min = null, ?int $max = null): array
    {
        $rules = ['integer'];

        if ($min !== null) {
            $rules[] = "min:{$min}";
        }

        if ($max !== null) {
            $rules[] = "max:{$max}";
        }

        if ($nullable) {
            array_unshift($rules, 'nullable');
        } else {
            array_unshift($rules, 'required');
        }

        return $rules;
    }
}
