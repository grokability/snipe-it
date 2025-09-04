<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Watson\Validating\ValidatingTrait;

class DefaultValuesForCustomFields extends Model
{
    use HasFactory, ValidatingTrait;

    protected $rules = [
        'type' => 'required'
    ];

    public $timestamps = false;

    public function field() {
        return $this->belongsTo('custom_fields');
    }

    // There is, effectively, another 'relation' here, but it's weirdly polymorphic
    // and impossible to represent in Laravel.
    // we have a 'type', and we have an 'item_pivot_id' -
    // For example, in Assets the 'type' would be App\Models\Asset, and the 'item_pivot_id' would be a model_id
    // I can't come up with any way to represent this in Laravel/Eloquent

    // TODO: might be getting overly-fancy here; maybe just want to do an ID? Instead of an Eloquent Model?
    public function scopeForPivot(Builder $query, Model $item, string $class) {
        return $query->where('item_pivot_id', $item->id)->where('type', $class);
    }
}
