<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\MayContainCustomFields;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Gate;
use App\Rules\AssetCannotBeCheckedOutToNondeployableStatus;

class StoreAssetRequest extends ImageUploadRequest
{
    use MayContainCustomFields;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('create', new Asset);
    }

    public function prepareForValidation(): void
    {
        parent::prepareForValidation(); // call ImageUploadRequest thing
        // Guard against users passing in an array for company_id instead of an integer.
        // If the company_id is not an integer then we simply use what was
        // provided to be caught by model level validation later.
        // The use of is_numeric accounts for 1 and '1'.
        $idForCurrentUser = is_numeric($this->company_id)
            ? Company::getIdForCurrentUser($this->company_id)
            : $this->company_id;

        $this->parseLastAuditDate();

        $this->merge([
            'asset_tag' => $this->asset_tag ?? Asset::autoincrement_asset(),
            'company_id' => $idForCurrentUser,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $modelRules = (new Asset)->getRules();

        if (Setting::getSettings()->digit_separator === '1.234,56' && is_string($this->input('purchase_cost'))) {
            // If purchase_cost was submitted as a string with a comma separator
            // then we need to ignore the normal numeric rules.
            // Since the original rules still live on the model they will be run
            // right before saving (and after purchase_cost has been
            // converted to a float via setPurchaseCostAttribute).
            $modelRules = $this->removeNumericRulesFromPurchaseCost($modelRules);
        }

        return array_merge(
            $modelRules,
            ['status_id' => [new AssetCannotBeCheckedOutToNondeployableStatus()]],
            parent::rules(),
        );
    }

    private function parseLastAuditDate(): void
    {
        if ($this->input('last_audit_date')) {
            try {
                $lastAuditDate = Carbon::parse($this->input('last_audit_date'));

                $this->merge([
                    'last_audit_date' => $lastAuditDate->startOfDay()->format('Y-m-d H:i:s'),
                ]);
            } catch (InvalidFormatException $e) {
                // we don't need to do anything here...
                // we'll keep the provided date in an
                // invalid format so validation picks it up later
            }
        }
    }

    private function removeNumericRulesFromPurchaseCost(array $rules): array
    {
        $purchaseCost = $rules['purchase_cost'];

        // If rule is in "|" format then turn it into an array
        if (is_string($purchaseCost)) {
            $purchaseCost = explode('|', $purchaseCost);
        }

        $rules['purchase_cost'] = array_filter($purchaseCost, function ($rule) {
            return $rule !== 'numeric' && $rule !== 'gte:0';
        });

        return $rules;
    }
}
