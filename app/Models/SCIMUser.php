<?php

namespace App\Models;

class SCIMUser extends User
{
    protected $table = 'users';

    protected $throwValidationExceptions = true; // we want model-level validation to fully THROW, not just return false

    public function __construct(array $attributes = [])
    {
        $attributes['password'] = $this->noPassword();
        parent::__construct($attributes);
    }

    // Have to re-define this here because Eloquent will try to 'guess' a foreign key of s_c_i_m_user_id
    // from SCIMUser
    public function groups()
    {
        return $this->belongsToMany(\App\Models\Group::class, 'users_groups', 'user_id', 'group_id');
    }

}