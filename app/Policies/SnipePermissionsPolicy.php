<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * SnipePermissionsPolicy provides methods for handling the granular permissions used throughout Snipe-IT.
 * Each "area" of a permission (which is usually a model, like Assets, Departments, etc), has a setting
 * in config/permissions.php like view/create/edit/delete (and sometimes some extra stuff like
 * checkout/checkin, etc.)
 *
 * A Policy should exist for each of these models, however if they only use the standard view/create/edit/delete,
 * the policy can be pretty simple, for example with just one method setting the column name:
 *
 * protected function columnName()
 * {
 *    return 'manufacturers';
 * }
 */
abstract class SnipePermissionsPolicy
{
    /**
     * This should return the key of the model in the users json permission string.
     *
     * @return bool
     */

    //
    abstract protected function columnName();

    use HandlesAuthorization;

    public function before(User $user, $ability, $item)
    {
        /**
         * If an admin, they can do all item related tasks, but ARE constrained by FMCSA company access.
         * That scoping happens on the model level (except for the Users model) via the Companyable trait.
         *
         * This does lead to some inconsistencies in the responses, since attempting to edit assets,
         * accessories, etc (anything other than users) will result in a Forbidden error, whereas the users
         * area will redirect with "That user doesn't exist" since the scoping is handled directly on those queries.
         *
         * The *superuser* global permission gets handled in the AuthServiceProvider before() method.
         *
         * @see https://snipe-it.readme.io/docs/permissions
         */

        if ($user->hasAccess('admin')) {
            return true;
        }

        /**
         * If we got here by $this→authorize('something', $actualModel) then we can continue on, but if we got here
         * via $this→authorize('something', Model::class) then calling Company:: isCurrentUserHasAccess($item) gets weird.
         * Bail out here by returning "nothing" and allow the relevant method lower in this class to be called and handle authorization.
         */
        if (!$item instanceof Model){
            return;
        }


        /**
         * The Company::isCurrentUserHasAccess() method from the company model handles the check for FMCS already so we
         * don't have to do that here.
         */
        if (!Company::isCurrentUserHasAccess($item)) {
            return false;
        }

    }


    /**
     * These methods handle the generic view/create/edit/delete permissions for the model.
     *
     * @param User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->hasAccess($this->columnName().'.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function view(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.view');
    }

    public function files(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.files');
    }

    /**
     * Determine whether the user can create model.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasAccess($this->columnName().'.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function update(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.edit');
    }


    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function checkout(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.checkout');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function delete(User $user, $item = null)
    {
        $itemConditional = true;
        if ($item) {
            $itemConditional = empty($item->deleted_at);
        }

        return $itemConditional && $user->hasAccess($this->columnName().'.delete');
    }

    /**
     * Determine whether the user can manage the model.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function manage(User $user, $item = null)
    {
        return $user->hasAccess($this->columnName().'.edit');
    }
}
