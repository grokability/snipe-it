<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\Setting;
use App\Rules\AllowedUploadExtension;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation for the adjust-quantity modal submitted from any
 * accessory / consumable / component listing or view page. Authorization
 * is left to the controller (it needs the resolved route-model to run
 * the 'update' policy) so authorize() here is unconditionally true.
 */
class AdjustQuantityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $noteRule = Setting::getSettings()?->require_adjust_quantity_notes
            ? ['required', 'string', 'max:65535']
            : ['nullable', 'string', 'max:65535'];

        return [
            // Signed delta: positive to replenish, negative to decrement.
            // Zero is intentionally allowed so users can record an audit
            // (physical count against the current DB qty). The trait
            // writes a QuantityAdjust log entry with quantity=0 in that
            // case without touching the on-hand column. The trait itself
            // rejects the actual below-in-use case.
            'amount' => ['required', 'integer'],
            'note' => $noteRule,
            'order_number' => ['nullable', 'string', 'max:191'],
            // Acquisition metadata that feeds Order / OrderItem creation
            // via HandlesAdjustQuantity::resolveOrderForAdjustment. All
            // optional — audit-only submissions leave them blank and no
            // Order row is generated. supplier_id lookup goes through
            // resolveOrderForAdjustment (not a direct exists rule here)
            // so the caller sees a clean error instead of the
            // hard-coded validation message.
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'purchase_date' => ['nullable', 'date_format:Y-m-d'],
            // Per-unit cost — matches Snipe-IT's existing purchase_cost
            // semantic. Lands on OrderItem.price. gte:0 protects against
            // negative-value posts; max mirrors the widest decimal(20,4).
            'unit_cost' => ['nullable', 'numeric', 'gte:0', 'max:9999999999999999.9999'],
            // Currency free-text (matches locations.currency /
            // settings.default_currency shape). Lands on Order.currency.
            'currency' => ['nullable', 'string', 'max:10'],
            // Optional receipt/invoice/PO scan. Attaches to the same
            // QuantityAdjust action_log row via its filename column,
            // not a separate 'uploaded' entry, so the download surfaces
            // inline with the replenish history. AllowedUploadExtension
            // is the same rule UploadFileRequest uses post-#19389, so
            // legit text uploads that trip libmagic quirks still get
            // through without over-strict rejection.
            'file' => [
                'nullable',
                'file',
                new AllowedUploadExtension(config('filesystems.allowed_upload_extensions_array')),
                'max:'.Helper::file_upload_max_size(),
            ],
        ];
    }
}
