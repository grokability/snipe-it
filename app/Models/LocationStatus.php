<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Location;

class LocationStatus extends Model
{
    use HasFactory;
    protected $table = 'location_statuses';
    protected $fillable = ['name'];
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
