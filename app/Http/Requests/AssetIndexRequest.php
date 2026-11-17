<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation rules for listing / filtering assets.
 *
 * Consumed by the MCP ListAssetsTool. The HTTP API controller
 * (Api\AssetsController@index) can adopt this FormRequest when it
 * wants structured validation on its filter query params. Any future
 * rule change (new filter, tightened existence check, new status_type
 * value) stays in sync for both surfaces.
 */
class AssetIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('index', Asset::class) ?? false;
    }

    public function rules(): array
    {
        return static::rulesArray();
    }

    public static function rulesArray(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            // Structured advanced-search payload (same shape the UI
            // modal posts). Routes through Searchable::scopeTextSearch.
            'filter' => 'nullable|array',
            'status_type' => 'nullable|string|in:RTD,Deployed,Archived,Pending,Undeployable',
            'company_id' => 'nullable|integer|exists:companies,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            'rtd_location_id' => 'nullable|integer|exists:locations,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'model_id' => 'nullable|integer|exists:models,id',
            'manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
        ];
    }
}
