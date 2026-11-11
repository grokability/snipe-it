<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation rules for listing / filtering users.
 *
 * Consumed by the MCP ListUsersTool. See AssetIndexRequest for the
 * same rationale: one source of truth for filter rules, callable both
 * from HTTP FormRequest validation and from MCP tool handlers.
 */
class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('index', User::class) ?? false;
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
            'department_id' => 'nullable|integer|exists:departments,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            // Login-access flag, not a "still with the company" signal.
            'activated' => 'nullable|boolean',
            // Soft-delete scope toggles. only_deleted takes precedence
            // over include_deleted when both are set.
            'include_deleted' => 'nullable|boolean',
            'only_deleted' => 'nullable|boolean',
        ];
    }
}
