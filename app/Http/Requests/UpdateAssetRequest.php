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

        // Strip fmcs_company from the request-level company_id rule. It's an
        // implicit rule (fires on null/absent) which is right for the
        // model-level ValidatingTrait check on save, but wrong at the
        // request-level PATCH context — omitting company_id means "don't
        // change it," not "set to null." The model-level rule still
        // catches a genuine null-value save. StoreAssetRequest and
        // BulkUpdateAssetsRequest inherit from this indirectly via
        // parent classes; the store path keeps the rule (create should
        // require Company), the bulk update path is already covered by
        // this strip since it extends UpdateAssetRequest.
        if (isset($assetRules['company_id']) && is_array($assetRules['company_id'])) {
            $assetRules['company_id'] = array_values(array_filter(
                $assetRules['company_id'],
                fn ($rule) => $rule !== 'fmcs_company',
            ));
        }

        // On the singular endpoint we can tell Rule::unique to ignore the
        // asset being updated. Bulk (BulkUpdateAssetsRequest) overrides this
        // to null because it would have to ignore N different ids at once,
        // which Rule::unique->ignore() can't express — collisions on the
        // bulk path are caught per-row by Watson at save time.
        $ignoreId = $this->ignoreIdForUnique();

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
                    Rule::unique('assets', 'asset_tag')->ignore($ignoreId)->withoutTrashed(),
                ],
                'serial' => [
                    'string', 'max:255', 'not_array',
                    $setting->unique_serial == '1' ? Rule::unique('assets', 'serial')->ignore($ignoreId)->withoutTrashed() : 'nullable',
                ],
            ],
        );

        return $rules;
    }

    /**
     * ID that Rule::unique should ignore when checking asset_tag / serial. The
     * singular endpoint has an Asset via route-model-binding; the bulk subclass
     * has no single id to point at and returns null.
     */
    protected function ignoreIdForUnique(): ?int
    {
        return $this->asset instanceof Asset ? $this->asset->id : null;
    }
}
