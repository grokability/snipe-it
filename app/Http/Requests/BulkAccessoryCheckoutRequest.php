<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\InfersCheckoutToType;
use App\Models\Accessory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class BulkAccessoryCheckoutRequest extends FormRequest
{
    use InfersCheckoutToType;

    public function authorize(): bool
    {
        return Gate::allows('checkout', Accessory::class);
    }

    public function prepareForValidation(): void
    {
        $this->inferCheckoutToType();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(AccessoryCheckoutRequest::rulesArray(), [
            'ids' => ['required', 'array', 'min:1'],
            // Per-row exists: check deliberately omitted. It fires one
            // SELECT per id (6 ids = 6 round trips) and the controller
            // already filters the submitted set through a scoped
            // Accessory::whereIn()->get() which drops anything the
            // operator cannot see. Integer cast is enough here.
            'ids.*' => ['integer'],
            'checkout_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'redirect_option' => ['nullable', 'string'],
            'checkout_to_type' => ['required', 'in:user,asset,location'],
        ]);
    }
}
