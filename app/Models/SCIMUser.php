<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperSCIMUser
 */
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
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Group::class, 'users_groups', 'user_id', 'group_id');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id');
    }

    /**
     * SCIMUser inherits HasCalendarEvents from User.
     * Without this override every SCIM provisioning save fires
     * the trait observer and writes a calendar_events row keyed to
     * source_type = SCIMUser::class, duplicating the canonical User
     * row and leaving orphans around after reseeds. SCIM syncs identity
     * data (name, email, groups, active), not employment dates, so an
     * empty definitions array short-circuits syncCalendarEvents() with
     * no behavior loss. The canonical User side still emits start_date
     * and end_date events as before.
     */
    public function calendarEventDefinitions(): array
    {
        return [];
    }
}
