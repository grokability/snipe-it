<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Watson\Validating\ValidatingTrait;

class Group extends SnipeModel
{
    use HasFactory;

    protected $table = 'permission_groups';

    public $rules = [
        'name' => 'required|min:2|max:255|unique',
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
    use ValidatingTrait;
    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'created_at', 'notes'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

    /**
     * Establishes the groups -> users relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'users_groups');
    }

    /**
     * Get the user that created the group
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v6.3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Decode JSON permissions into array
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
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

                if (!is_integer($permission)) {
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
