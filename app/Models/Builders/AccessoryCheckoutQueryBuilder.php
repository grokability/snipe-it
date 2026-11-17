<?php

namespace App\Models\Builders;

use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of AccessoryCheckout
 *
 * @extends Builder<TModel>
 */
class AccessoryCheckoutQueryBuilder extends Builder
{
    public function userAssigned(): self
    {
        return $this->where('assigned_type', '=', User::class);
    }

    public function locationAssigned(): self
    {
        return $this->where('assigned_type', '=', Location::class);
    }

    public function assetsAssigned(): self
    {
        return $this->where('assigned_type', '=', Asset::class);
    }
}
