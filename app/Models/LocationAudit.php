<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/LocationAudit.php
class LocationAudit extends Model
{
    protected $fillable = ['location_id','user_id','notes'];
    public function location() { return $this->belongsTo(Location::class); }
    public function user()      { return $this->belongsTo(User::class); }
    public function assets()    { return $this->belongsToMany(Asset::class)
                                  ->withPivot('present'); }
}

public function audits() {
    return $this->belongsToMany(LocationAudit::class)
                ->withPivot('present');
}