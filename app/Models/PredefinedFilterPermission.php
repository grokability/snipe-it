<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

class PredefinedFilterPermission extends Model
{
    use HasFactory;
    use ValidatingTrait;

    protected $casts = [
    ];

    protected $fillable = [
        'predefined_filter_id',
        'permission_group_id',
        'created_by',
    ];

    protected $rules = [
        'created_by'                => ['required', 'integer', 'exists:users,id'],
        'predefined_filter_id'      => ['required', 'integer', 'exists:predefined_filters,id'],
        'permission_group_id'       => ['required', 'integer', 'exists:permission_groups,id'],
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
