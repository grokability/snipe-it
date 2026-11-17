<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

/**
 * One line on an Order. Polymorphic to the inventory model that was
 * acquired (Accessory / Consumable / Component / Asset / License) so a
 * single Order can span multiple item types. qty is the number of units
 * on this line. Price is the per-unit cost (matches the semantic that
 * the existing purchase_cost columns use across the codebase).
 *
 * @mixin IdeHelperOrderItem
 */
class OrderItem extends Model
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;

    protected $table = 'order_items';

    /**
     * The inventory model classes that can legally appear as the
     * polymorphic `item` on an OrderItem. Referenced from the item_type
     * validation rule and from the backfill / reconcile migrations,
     * which each iterate a filtered subset of this list.
     */
    public const ITEM_TYPES = [
        Accessory::class,
        Consumable::class,
        Component::class,
        Asset::class,
        License::class,
    ];

    // NOTE: `created_by` is deliberately NOT fillable, accepting it via
    // mass-assignment would let a request attribute a line to any
    // user_id the caller writes. All internal writers set it via
    // explicit `$line->created_by = auth()->id()`.
    protected $fillable = [
        'order_id',
        'item_type',
        'item_id',
        'qty',
        'price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:4',
    ];

    /**
     * Static rules that don't depend on runtime data. The `item_type`
     * rule is composed from ITEM_TYPES in getRules() below so the
     * polymorphic class list has one source of truth.
     */
    public $rules = [
        'order_id' => 'required|integer|exists:orders,id',
        'item_id' => 'required|integer|min:1',
        'qty' => 'required|integer|min:1',
        'price' => 'nullable|numeric|gte:0|max:99999999999999999.99',
    ];

    /**
     * Validation for the polymorphic pivot. item_id can't be validated
     * exists-style without a custom rule (the target table is a runtime
     * decision keyed off item_type), so we scope item_type to the
     * inventory classes that legally participate in Orders and leave
     * item_id to caller sanity. order_id blocks reference to a deleted
     * Order via the exists rule (ignoring soft-deleted rows).
     */
    public function getRules(): array
    {
        return $this->rules + [
            'item_type' => 'required|string|in:'.implode(',', self::ITEM_TYPES),
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User who added this specific line. Distinct from Order.admin
     * because staggered receipts under one order_number can accumulate
     * lines from multiple operators.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The action_log entry that created this OrderItem. Both a `create`
     * observer-write and a `QuantityAdjust` receive their own action_log
     * with `order_item_id` set, so joining on that FK surfaces the file
     * uploaded through the adjust-quantity modal (which attaches its
     * receipt to the action_log, not the OrderItem itself).
     */
    public function actionlog(): HasOne
    {
        return $this->hasOne(Actionlog::class, 'order_item_id')->latest('id');
    }
}
