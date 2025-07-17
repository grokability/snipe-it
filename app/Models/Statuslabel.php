<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class Statuslabel extends SnipeModel
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;
    use UniqueUndeletedTrait;

    protected $injectUniqueIdentifier = true;

    protected $table = 'status_labels';
    protected $hidden = ['user_id', 'deleted_at'];

    protected $rules = [
        'name'  => 'required|string|unique_undeleted',
        'notes'   => 'string|nullable',
        'deployable' => 'required',
        'pending' => 'required',
        'archived' => 'required',
    ];

    protected $fillable = [
        'archived',
        'deployable',
        'name',
        'notes',
        'pending',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'notes'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

    /**
     * Establishes the status label -> assets relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assets()
    {
        return $this->hasMany(\App\Models\Asset::class, 'status_id');
    }

    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Gets the status label type
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return string
     */
    public function getStatuslabelType()
    {
        if (($this->pending == '1') && ($this->archived == '0') && ($this->deployable == '0')) {
            return 'pending';
        } elseif (($this->pending == '0') && ($this->archived == '1') && ($this->deployable == '0')) {
            return 'archived';
        } elseif (($this->pending == '0') && ($this->archived == '0') && ($this->deployable == '0')) {
            return 'undeployable';
        }

        return 'deployable';
    }

    /**
     * Query builder scope to for pending status types
     *
     * @return \Illuminate\Database\Query\Builder Modified query builder
     */
    public function scopePending()
    {
        return $this->where('pending', '=', 1)
            ->where('archived', '=', 0)
            ->where('deployable', '=', 0);
    }

    /**
     * Query builder scope for archived status types
     *
     * @return \Illuminate\Database\Query\Builder Modified query builder
     */
    public function scopeArchived()
    {
        return $this->where('pending', '=', 0)
            ->where('archived', '=', 1)
            ->where('deployable', '=', 0);
    }

    /**
     * Query builder scope for deployable status types
     *
     * @return \Illuminate\Database\Query\Builder Modified query builder
     */
    public function scopeDeployable()
    {
        return $this->where('pending', '=', 0)
            ->where('archived', '=', 0)
            ->where('deployable', '=', 1);
    }

    /**
     * Query builder scope for undeployable status types
     *
     * @return \Illuminate\Database\Query\Builder Modified query builder
     */
    public function scopeUndeployable()
    {
        return $this->where('pending', '=', 0)
            ->where('archived', '=', 0)
            ->where('deployable', '=', 0);
    }

    /**
     * Helper function to determine type attributes
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since  [v1.0]
     * @return string
     */
    public static function getStatuslabelTypesForDB($type)
    {
        $statustype['pending'] = 0;
        $statustype['deployable'] = 0;
        $statustype['archived'] = 0;

        if ($type == 'pending') {
            $statustype['pending'] = 1;
            $statustype['deployable'] = 0;
            $statustype['archived'] = 0;
        } elseif ($type == 'deployable') {
            $statustype['pending'] = 0;
            $statustype['deployable'] = 1;
            $statustype['archived'] = 0;
        } elseif ($type == 'archived') {
            $statustype['pending'] = 0;
            $statustype['deployable'] = 0;
            $statustype['archived'] = 1;
        }

        return $statustype;
    }

    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'status_labels.created_by', '=', 'admin_sort.id')->select('status_labels.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }
}
