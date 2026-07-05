<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Http\Requests\Traits\MayContainCustomFields;
use App\Models\Asset;
use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends ImageUploadRequest
{
    use MayContainCustomFields;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('update', $this->asset);
    }

    public function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if ($this->filled('purchase_cost') && ! is_float($this->input('purchase_cost')) && preg_match('/^[\d.,]+$/', (string) $this->input('purchase_cost'))) {
            $this->merge(['purchase_cost' => Helper::ParseCurrency($this->input('purchase_cost'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $setting = Setting::getSettings();

        $assetRules = (new Asset)->getRules();

        // assigned_to / assigned_type are intentionally excluded: they must only
        // be written via assigned_user / assigned_asset / assigned_location (which
        // route through checkOut() and produce the required audit-log entry).
        unset($assetRules['assigned_to'], $assetRules['assigned_type']);

        $rules = array_merge(
            parent::rules(),
            $assetRules,
            // This overwrites the rulesets that are set at the model level (via Watson) but are not necessarily required at the request level when doing a PATCH update.
            // Confusingly, this skips the unique_undeleted validator at the model level (and therefore the UniqueUndeletedTrait), so we have to re-add those
            // rules here without the requiredness, since those values will already exist if you're updating an existing asset.
            [
                'model_id' => ['integer', 'exists:models,id,deleted_at,NULL', 'not_array'],
                'status_id' => ['integer', 'exists:status_labels,id'],
                'asset_tag' => [
                    'min:1', 'max:255', 'not_array',
                    Rule::unique('assets', 'asset_tag')->ignore($this->asset)->withoutTrashed(),
                ],
                'serial' => [
                    'string', 'max:255', 'not_array',
                    $setting->unique_serial == '1' ? Rule::unique('assets', 'serial')->ignore($this->asset)->withoutTrashed() : 'nullable',
                ],
            ],
        );

        return $rules;
    }
}
