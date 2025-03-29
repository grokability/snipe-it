<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationType extends Model
{
    use HasFactory;

    // Ako koristite drugačije ime tabele (kao što je plural 'location_types')
    protected $table = 'location_types';

    // Dodajte dozvoljena polja koja mogu biti masovno dodeljena
    protected $fillable = ['name'];
}
