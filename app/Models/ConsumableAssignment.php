<?php

namespace App\Models;

use App\Models\Traits\CompanyableTrait;
use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

class ConsumableAssignment extends Model
{
    use CompanyableTrait;
    use ValidatingTrait;

    protected $table = 'consumables_users';

    public $rules = [
        'assigned_to' => 'required|exists:users,id',
    ];

    public function consumable()
    {
        return $this->belongsTo(Consumable::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
