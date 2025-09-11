<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\Builder;

class PredefinedFilterPermission extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;

    protected $casts = [
        'can_view'    => 'boolean',
        'can_create'  => 'boolean',
        'can_edit'    => 'boolean',
        'can_delete'  => 'boolean',
    ];

    protected $fillable = [
        'predefined_filter_id',
        'permission_group_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'created_by',
    ];

    protected $rules = [
        'created_by'                => ['required', 'integer', 'exists:users,id'],
        'predefined_filter_id'      => ['required', 'integer', 'exists:predefined_filters,id'],
        'permission_group_id'       => ['required', 'integer', 'exists:permission_groups,id'],
    
        'can_view'                  => 'boolean',
        'can_create'                => 'boolean',
        'can_edit'                  => 'boolean',
        'can_delete'                => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function filter()
    {
        return $this->belongsTo(PredefinedFilter::class, 'predefined_filter_id');
    }
}