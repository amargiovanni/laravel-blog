<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotSelfRedirect implements ValidationRule
{
    public function __construct(protected ?string $sourceUrl = null) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->sourceUrl === null) {
            return;
        }

        $normalizedSource = '/'.ltrim(rtrim($this->sourceUrl, '/'), '/');
        $normalizedTarget = '/'.ltrim(rtrim($value, '/'), '/');

        if (strtolower($normalizedSource) === strtolower($normalizedTarget)) {
            $fail(__('The target URL cannot be the same as the source URL.'));
        }
    }
}
