<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Import extends Model
{
    use HasFactory;

    protected $casts = [
        'header_row' => 'array',
        'first_row' => 'array',
        'field_map' => 'json',
    ];

    /**
     * Establishes the license -> admin user relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
