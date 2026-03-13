<?php

namespace App\Http\Traits;

trait TwoColumnUniqueUndeletedTrait
{
    /**
     * Prepare a unique_ids rule, adding a model identifier if required.
     *
     * @param  array  $parameters
     * @param  string $field
     * @return string
     */
    protected function prepareTwoColumnUniqueUndeletedRule($parameters)
    {
        $column = $parameters[0];
        $value = $this->{$parameters[0]};

        // This is an existing model we're updating so ignore the current ID ($this->getKey())
        if ($this->exists) {
            return 'two_column_unique_undeleted:'.$this->table.','.$this->getKey().','.$column.','.$value;
        }

        // This is a new record, so we can ignore the current ID
        return 'two_column_unique_undeleted:'.$this->table.',0,'.$column.','.$value;
    }
}
