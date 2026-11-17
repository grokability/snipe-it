<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SnipeBoolean implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * Unused parameters are required by Laravel's CastsAttributes contract.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // TODO: we _could_ coerce this back into a 'real' boolean, but I think a lot of our code
        // assumes 0 or 1 in a lot of places - so we don't do that yet
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * Unused parameters are required by Laravel's CastsAttributes contract.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
