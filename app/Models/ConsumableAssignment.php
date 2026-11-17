<?php

namespace App\Models;

use App\Models\Traits\CompanyableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

/**
 * @mixin IdeHelperConsumableAssignment
 */
class ConsumableAssignment extends Model
{
    use CompanyableTrait;
    use ValidatingTrait;

    protected $table = 'consumables_users';

    protected $fillable = [
        'consumed_at',
    ];

    protected $casts = [
        'consumed_at' => 'datetime',
    ];

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

    /**
     * Scope: rows the recipient has not yet reported as used up. These
     * still count against the parent consumable's outstanding total and
     * block archive/delete.
     */
    public function scopeOutstanding(Builder $query): void
    {
        $query->whereNull('consumables_users.consumed_at');
    }

    /**
     * Scope: rows that have been marked consumed. Preserved for audit
     * and historical reporting but no longer gate archive or delete.
     */
    public function scopeConsumed(Builder $query): void
    {
        $query->whereNotNull('consumables_users.consumed_at');
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }
}
