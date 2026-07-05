<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Http\Requests\Traits\MayContainCustomFields;
use App\Models\Asset;
use App\Models\Company;
use App\Rules\AssetCannotBeCheckedOutToNondeployableStatus;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Gate;

class StoreAssetRequest extends ImageUploadRequest
{
    use MayContainCustomFields;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Asset::class);
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
            'purchase_cost' => $this->filled('purchase_cost') && ! is_float($this->input('purchase_cost')) && preg_match('/^[\d.,]+$/', (string) $this->input('purchase_cost'))
                ? Helper::ParseCurrency($this->input('purchase_cost'))
                : $this->input('purchase_cost'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $modelRules = (new Asset)->getRules();

        // assigned_to / assigned_type are intentionally excluded: they must only
        // be written via assigned_user / assigned_asset / assigned_location (which
        // route through checkOut() and produce the required audit-log entry).
        unset($modelRules['assigned_to'], $modelRules['assigned_type']);

        return array_merge(
            $modelRules,
            ['status_id' => [new AssetCannotBeCheckedOutToNondeployableStatus]],
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
}
