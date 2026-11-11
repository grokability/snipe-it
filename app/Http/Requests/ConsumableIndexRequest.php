<?php

namespace App\Http\Requests;

use App\Models\Consumable;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation rules for listing / filtering consumables.
 * Consumed by the MCP ListConsumablesTool.
 */
class ConsumableIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('index', Consumable::class) ?? false;
    }

    public function rules(): array
    {
        return static::rulesArray();
    }

    public static function rulesArray(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'filter' => 'nullable|array',
            'company_id' => 'nullable|integer|exists:companies,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            // Returns rows where (qty - active checkouts) <= min_amt.
            // SQL-side filter so pagination remains correct at scale.
            'low_stock_only' => 'nullable|boolean',
        ];
    }
}
