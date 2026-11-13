<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation rules for listing / filtering maintenances.
 *
 * The HTTP API controller (Api\MaintenancesController@index) can adopt
 * this FormRequest when it wants structured validation on the filter
 * query params it currently reads inline. The MCP ListMaintenancesTool
 * consumes the static rulesArray() so any future rule change (new
 * filter, tightened date rule, polymorphic item_type support) stays in
 * sync between the two.
 */
class MaintenanceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', Asset::class) ?? false;
    }

    public function rules(): array
    {
        return static::rulesArray();
    }

    /**
     * Statically-callable rules array so non-FormRequest callers (the
     * MCP list_maintenances tool, artisan commands, tests) can
     * validate against the same shape without constructing a Request
     * instance.
     */
    public static function rulesArray(): array
    {
        return [
            'asset_id' => 'nullable|integer|exists:assets,id',
            'asset_tag' => 'nullable|string|max:191',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            // Completion-status filter maps to completed_at null-check
            // in the consuming code. "all" is the no-op default.
            'status' => 'nullable|string|in:open,completed,all',
            'is_warranty' => 'nullable|boolean',
            'start_date_from' => 'nullable|date',
            'start_date_to' => 'nullable|date',
            // completion_date_* filters the actual completed_at
            // timestamp, not the planned expected_completion_date.
            'completion_date_from' => 'nullable|date',
            'completion_date_to' => 'nullable|date',
        ];
    }
}
