<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class EnumRule implements ValidationRule
{
    /**
     * @var string The enum class to validate against
     */
    protected string $enumClass;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $enumClass)
    {
        $this->enumClass = $enumClass;
    }

    /**
     * Create a new rule instance for the given enum.
     */
    public static function for(string $enumClass): self
    {
        return new self($enumClass);
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! in_array($value, $this->enumClass::values())) {
            $enumClassName = class_basename($this->enumClass);
            $values = implode(', ', $this->enumClass::values());

            $fail("The {$attribute} must be one of the following values: {$values}");
        }
    }
}
