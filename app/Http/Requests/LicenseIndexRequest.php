<?php

namespace App\Http\Requests;

use App\Models\License;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation rules for listing / filtering licenses.
 * Consumed by the MCP ListLicensesTool.
 */
class LicenseIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('index', License::class) ?? false;
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
            'depreciation_id' => 'nullable|integer|exists:depreciations,id',
            // Window filter for renewal-due reports. Filters on
            // expiration_date between today and today + N days.
            'expiring_within_days' => 'nullable|integer|min:1',
            // Returns licenses with zero free seats remaining.
            'fully_consumed_only' => 'nullable|boolean',
        ];
    }
}
