<?php

namespace App\Http\Requests;

use App\Models\Accessory;
use Illuminate\Support\Facades\Gate;

class AccessoryCheckoutRequest extends ImageUploadRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('checkout', new Accessory);
    }

    public function prepareForValidation(): void
    {

        if ($this->accessory) {

            $this->diff = ($this->accessory->numRemaining() - $this->checkout_qty);
            $this->merge([
                'checkout_qty' => $this->checkout_qty ?? 1,
                'number_remaining_after_checkout' =>  (int) ($this->accessory->numRemaining() - $this->checkout_qty),
                'number_currently_remaining' =>  (int) $this->accessory->numRemaining(),
                'checkout_difference' =>  (int) $this->diff,
            ]);

            \Log::debug('---------------------------------------------');
        }

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return array_merge(
            [
                'assigned_user'         => 'required_without_all:assigned_asset,assigned_location',
                'assigned_asset'        => 'required_without_all:assigned_user,assigned_location',
                'assigned_location'     => 'required_without_all:assigned_user,assigned_asset',
                
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
            ],
        );
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
