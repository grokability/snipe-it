<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PermissionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'permissions',
        'created_by',
        'notes',
    ];

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_groups', 'group_id', 'user_id');
    }

    public function predefinedFilterPermissions()
    {
        return $this->hasMany(PredefinedFilterPermission::class);
    }
}
