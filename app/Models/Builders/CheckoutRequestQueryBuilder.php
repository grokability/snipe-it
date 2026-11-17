<?php

namespace App\Models\Builders;

use App\Enums\CheckoutRequestState;
use App\Models\CheckoutRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of CheckoutRequest
 *
 * @extends Builder<TModel>
 */
class CheckoutRequestQueryBuilder extends Builder
{
    public function open(): self
    {
        return $this->where('state', CheckoutRequestState::Pending->value);
    }

    public function pending(): self
    {
        return $this->where('state', CheckoutRequestState::Pending->value);
    }

    public function fulfilled(): self
    {
        return $this->where('state', CheckoutRequestState::Fulfilled->value);
    }

    public function canceled(): self
    {
        return $this->where('state', CheckoutRequestState::Canceled->value);
    }
}
