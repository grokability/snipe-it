<?php

namespace App\Http\Traits;

trait UniqueUndeletedTrait
{
    /**
     * Prepare a unique_ids rule, adding a model identifier if required.
     *
     * @param  array  $parameters
     * @param  string $field
     * @return string
     */
    protected function prepareUniqueUndeletedRule($parameters, $field)
    {
        // Only perform a replacement if the model has been persisted.
        if ($this->exists) {
            return 'unique_undeleted:'.$this->table.','.$this->getKey();
        }

        return 'unique_undeleted:'.$this->table.',0';
    }
}
