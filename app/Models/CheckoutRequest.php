<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckoutRequest extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['user_id'];
    protected $table = 'checkout_requests';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function requestingUser()
    {
        return $this->user()->withTrashed()->first();
    }

    public function requestedItem()
    {
        return $this->morphTo('requestable');
    }

    public function itemRequested() // Workaround for laravel polymorphic issue that's not being solved :(
    {
        return $this->requestedItem()->first();
    }

    public function itemType()
    {
        return snake_case(class_basename($this->requestable_type));
    }

    public function location()
    {
        return $this->itemRequested()->location;
    }

    public function name()
    {
        if ($this->itemType() == 'asset') {
            return $this->itemRequested()->display_name;
        }

        return $this->itemRequested()->name;
    }
}
