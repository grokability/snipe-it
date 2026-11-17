<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\InfersCheckoutToType;
use App\Models\Setting;

class AssetCheckoutRequest extends Request
{
    use InfersCheckoutToType;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->inferCheckoutToType();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return static::rulesArray();
    }

    /**
     * Statically-callable rules array so non-FormRequest callers (the MCP
     * CheckoutAssetTool, Artisan commands, tests) can validate against the
     * same shape without constructing a Request instance. Callers can
     * merge in their own extras (e.g. the MCP tool adds asset_tag / id
     * for its lookup step) with `array_merge`.
     */
    public static function rulesArray(): array
    {
        $settings = Setting::getSettings();

        $rules = [
            // exists_undeleted rejects soft-deleted checkout targets so the
            // controllers below cannot bind live inventory to trashed users,
            // assets, or locations. Applied at request-validation time so a
            // bad request bounces with 422 before any controller mutation.
            'assigned_user' => 'numeric|nullable|required_without_all:assigned_asset,assigned_location|prohibits:assigned_asset,assigned_location|exists_undeleted:users,id',
            'assigned_asset' => 'numeric|nullable|required_without_all:assigned_user,assigned_location|prohibits:assigned_user,assigned_location|exists_undeleted:assets,id',
            'assigned_location' => 'numeric|nullable|required_without_all:assigned_user,assigned_asset|prohibits:assigned_user,assigned_asset|exists_undeleted:locations,id',
            'status_id' => 'nullable|exists:status_labels,id,deployable,1',
            'checkout_to_type' => 'required|in:asset,location,user',
            'checkout_at' => [
                'nullable',
                'date',
            ],
            'expected_checkin' => [
                'nullable',
                'date',
            ],
            'requestable' => 'nullable|boolean',
        ];

        if ($settings->require_checkinout_notes) {
            $rules['note'] = 'required|string';
        }

        return $rules;
    }
}
