<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SamlNonce extends Model
{
    use HasFactory;

    protected $fillable = ['nonce','not_on_or_after'];

    public $timestamps = false;
}
