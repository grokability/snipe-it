<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SnipeFlattenArray implements CastsAttributes
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
        return is_array($value) ? implode(', ', $value) : $value;
    }
}
