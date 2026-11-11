<?php

namespace App\Http\Requests;

use App\Models\Accessory;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation rules for listing / filtering accessories.
 * Consumed by the MCP ListAccessoriesTool.
 */
class AccessoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('index', Accessory::class) ?? false;
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
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            // Returns rows where (qty - active checkouts) <= min_amt.
            // SQL-side filter so pagination remains correct at scale.
            'low_stock_only' => 'nullable|boolean',
        ];
    }
}
