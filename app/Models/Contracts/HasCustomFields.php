<?php

namespace App\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

interface HasCustomFields
{
    public function custom_fieldset(): Relation;

    /**
     * @param  array<string, mixed>|Request  $request
     * @return array<string, mixed>
     */
    public function custom_fill(array|Request $request, bool $with_defaults = false, ?string $filter_field_expression = null, bool $only_present = true): array;

    public function hasSkippedEncryptedCustomFields(): bool;
}
