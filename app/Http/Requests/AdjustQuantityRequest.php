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
            'amount' => ['required', 'integer'],
            'note' => $noteRule,
            'order_number' => ['nullable', 'string', 'max:191'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'purchase_date' => ['nullable', 'date_format:Y-m-d'],
            'unit_cost' => ['nullable', 'numeric', 'gte:0', 'max:9999999999999999.9999'],
            'currency' => ['nullable', 'string', 'max:10'],
            'file' => [
                'nullable',
                'file',
                new AllowedUploadExtension(config('filesystems.allowed_upload_extensions_array')),
                'max:'.Helper::file_upload_max_size(),
            ],
        ];
    }
}
