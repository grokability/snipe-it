<?php

namespace App\Http\Traits;

trait UniqueUndeletedTrait
{
    /**
     * Prepare a unique_ids rule, adding a model identifier if required.
     *
     * @param  array  $parameters
     * @param  string  $field
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

    /**
     * Prepare a `unique_undeleted_in_scope` rule.
     *
     * Model declares:  `'name' => 'unique_undeleted_in_scope:parent_id,company_id'`
     * We rewrite to:   `'name' => 'unique_undeleted_in_scope:locations,42,parent_id,company_id'`
     *
     * The rule closure in ValidationServiceProvider reads the current
     * request/model value of each scope column via `$validator->getData()`,
     * so a new location named "Rack 1" under DC1 is compared only against
     * other locations whose parent_id (and company_id) match, not
     * globally as `unique_undeleted` does.
     *
     * ValidatingTrait calls this via `call_user_func_array` with
     * `[$parameters, $field]`, but we do not use the field name here so
     * only $parameters is declared. PHP silently discards the extra arg.
     *
     * @param  array  $parameters  the scope column names declared on the model
     */
    protected function prepareUniqueUndeletedInScopeRule($parameters)
    {
        $id = $this->exists ? $this->getKey() : 0;

        return 'unique_undeleted_in_scope:'.$this->table.','.$id.','.implode(',', $parameters);
    }
}
