<?php

namespace App\Http\Traits;

trait TwoColumnUniqueUndeletedTrait
{
    /**
     * Prepare a unique_ids rule, adding a model identifier if required.
     *
     * @param  array  $parameters
     * @return string
     */
    protected function prepareTwoColumnUniqueUndeletedRule($parameters)
    {
        $column = $parameters[0];
        $value = $this->{$parameters[0]};

        // Non-scalar values (arrays / objects from a malformed API
        // payload) blow up the string concat below with "Array to
        // string conversion". Coerce to empty so the rule falls
        // through cleanly and the pipe-level `string` rule surfaces
        // the type mismatch as a 422 instead of an uncaught 500.
        if (! is_scalar($value)) {
            $value = '';
        }

        // This is an existing model we're updating so ignore the current ID ($this->getKey())
        if ($this->exists) {
            return 'two_column_unique_undeleted:'.$this->table.','.$this->getKey().','.$column.','.$value;
        }

        // This is a new record, so we can ignore the current ID
        return 'two_column_unique_undeleted:'.$this->table.',0,'.$column.','.$value;
    }
}
