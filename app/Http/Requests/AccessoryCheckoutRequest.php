<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\InfersCheckoutToType;
use App\Models\Accessory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Gate;

class AccessoryCheckoutRequest extends ImageUploadRequest
{
    use InfersCheckoutToType;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('checkout', Accessory::class);
    }

    public function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->inferCheckoutToType();

        if ($this->accessory) {

            $this->diff = ($this->accessory->numRemaining() - $this->checkout_qty);
            $this->merge([
                'checkout_qty' => $this->checkout_qty ?? 1,
                'number_remaining_after_checkout' => (int) ($this->accessory->numRemaining() - $this->checkout_qty),
                'number_currently_remaining' => (int) $this->accessory->numRemaining(),
                'checkout_difference' => (int) $this->diff,
            ]);

            \Log::debug('---------------------------------------------');
        }

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(static::rulesArray(), [
            // Qty rules depend on data computed in prepareForValidation()
            // (number_currently_remaining, number_remaining_after_checkout)
            // which only this FormRequest produces, so they are kept
            // instance-local and not part of the shared rulesArray().
            'number_remaining_after_checkout' => [
                'min:0',
                'required',
                'integer',
            ],
            'checkout_qty' => [
                'integer',
                'lte:number_currently_remaining',
                'min:1',
            ],
        ]);
    }

    /**
     * Statically-callable rules array of the target-selection rules shared
     * between this FormRequest and the MCP CheckoutAccessoryTool. Qty
     * rules stay in the instance rules() because they depend on request-
     * level state the MCP tool does not produce.
     */
    public static function rulesArray(): array
    {
        return [
            'assigned_user' => 'required_without_all:assigned_asset,assigned_location|prohibits:assigned_asset,assigned_location|nullable|exists_undeleted:users,id',
            'assigned_asset' => 'required_without_all:assigned_user,assigned_location|prohibits:assigned_user,assigned_location|nullable|exists_undeleted:assets,id',
            'assigned_location' => 'required_without_all:assigned_user,assigned_asset|prohibits:assigned_user,assigned_asset|nullable|exists_undeleted:locations,id',
        ];
    }

    public function messages(): array
    {
        $messages = [
            'checkout_qty.lte' => trans_choice('admin/accessories/message.checkout.checkout_qty.lte', $this->number_currently_remaining, [
                'number_currently_remaining' => $this->number_currently_remaining,
                'checkout_qty' => $this->checkout_qty,
            ]),
        ];

        return $messages;
    }
}
