<?php

namespace App\Models;

/**
 * @mixin IdeHelperSCIMGroup
 */
class SCIMGroup extends Group
{
    protected $table = 'permission_groups';

    /**
     * Override the default firstOrNew() to always return a fresh instance
     * when called with an empty conditions array.
     *
     * The upstream SCIM library's create path lives at
     * vendor/arietimmerman/laravel-scim-server/src/Http/Controllers/ResourceController.php,
     * and hardcodes a username-only lookup for the "does this resource
     * already exist" check:
     *
     *   $resourceObject = $class::firstOrNew(
     *       $request->has('userName') ? ['username' => $input['userName']] : []
     *   );
     *
     * For User POSTs that's fine: firstOrNew(['username' => ...]) resolves
     * to an existing user or a new one. For Group POSTs the ternary always
     * lands on the empty-array branch (groups have no userName in their
     * SCIM payload), and Laravel's Model::firstOrNew([]) issues a WHERE
     * with no conditions, which returns THE FIRST ROW OF THE TABLE. Every
     * subsequent group POST resolves to that same row and the attribute
     * mappers overwrite its name / externalId / members instead of
     * creating a new group. The observed symptom is that every synced
     * group collapses into whatever group happens to be id=1, and every
     * SCIM-provisioned user ends up as a member of that one group. If the
     * pre-existing id=1 group carried elevated permissions (e.g. an
     * "Admins" group created before SCIM sync was enabled), every synced
     * user auto-elevated to those permissions. See #19493.
     *
     * Overriding firstOrNew here is the least invasive fix: SCIM's Group
     * route creates go through this subclass (via SnipeSCIMConfig::getGroupClass),
     * so the create path always returns a new model. Any callers passing
     * real conditions get the default Eloquent behavior.
     */
    public static function firstOrNew(array $attributes = [], array $values = [])
    {
        if (empty($attributes)) {
            return new self;
        }

        return parent::firstOrNew($attributes, $values);
    }

    // Have to re-define these here because Eloquent will try to 'guess' a
    // foreign key of s_c_i_m_group_id from SCIMGroup. Mirrors the reason
    // SCIMUser re-defines groups() the same way.
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'users_groups', 'group_id', 'user_id');
    }

    public function members()
    {
        return $this->users();
    }
}
