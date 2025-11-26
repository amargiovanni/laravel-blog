<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Redirect;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoRedirectLoop implements ValidationRule
{
    public function __construct(
        protected ?string $sourceUrl = null,
        protected ?int $currentRedirectId = null
    ) {}

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

        $redirect = new Redirect([
            'source_url' => $this->sourceUrl,
            'target_url' => $value,
        ]);

        if ($this->currentRedirectId !== null) {
            $redirect->id = $this->currentRedirectId;
            $redirect->exists = true;
        }

        if ($redirect->wouldCreateLoop()) {
            $fail(__('This redirect would create a redirect loop.'));
        }
    }
}
