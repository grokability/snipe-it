<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use App\Presenters\GroupPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

class Group extends SnipeModel
{
    use HasFactory;
    use Presentable;

    protected $table = 'permission_groups';

    public $rules = [
        'name' => 'required|max:255|unique',
    ];

    protected $fillable = [
        'name',
        'permissions',
        'notes',
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    protected $presenter = GroupPresenter::class;

    use Searchable;
    use ValidatingTrait;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'created_at',
        'notes',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'adminuser' => ['first_name', 'last_name', 'display_name'],
    ];

    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && (($this->users_count ?? $this->users()->count()) === 0);
    }

    /**
     * Establishes the groups -> users relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_groups');
    }

    /* this is just a shim for SCIM to work */
    public function members()
    {
        return $this->users();
    }

    /**
     * Decode JSON permissions into array
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return array | \stdClass
     */
    public function decodePermissions()
    {
        // If the permissions are an array, convert it to JSON
        if (is_array($this->permissions)) {
            $this->permissions = json_encode($this->permissions);
        }

        $permissions = json_decode($this->permissions ?? '{}', JSON_OBJECT_AS_ARRAY);

        // Otherwise, loop through the permissions and cast the values as integers
        if ((is_array($permissions)) && ($permissions)) {
            foreach ($permissions as $permission => $value) {

                if (! is_int($permission)) {
                    $permissions[$permission] = (int) $value;
                } else {
                    \Log::info('Weird data here - skipping it');
                    unset($permissions[$permission]);
                }
            }

            return $permissions ?: new \stdClass;
        }

        return new \stdClass;

    }

    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/
    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'permission_groups.created_by', '=', 'admin_sort.id')->select('permission_groups.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
