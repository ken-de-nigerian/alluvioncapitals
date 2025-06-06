<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBankAccount implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove any whitespace or special characters
        $cleaned = preg_replace('/[^0-9]/', '', (string) $value);

        if (!$this->isValidLength($cleaned)) {
            $fail('The :attribute must be between 10-16 digits.');
        }
    }

    protected function isValidLength(string $accountNumber): bool
    {
        $length = strlen($accountNumber);
        return $length >= 10 && $length <= 16;
    }
}
