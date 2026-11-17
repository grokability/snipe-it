<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Checkoutable items (components, accessories, consumables, licenses, …)
 * can be flagged `requestable` or `archived`, but never both at once:
 * requestable implies "available for people to ask for" while archived
 * implies "retired from active inventory." Place this rule on BOTH
 * fields in a model's $rules so whichever side gets toggled first
 * surfaces the collision.
 */
class RequestableArchivedMutex implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $other = $attribute === 'requestable' ? 'archived' : 'requestable';

        if (filter_var($this->data[$other] ?? null, FILTER_VALIDATE_BOOLEAN)) {
            $fail(trans('general.requestable_archived_conflict'));
        }
    }
}
