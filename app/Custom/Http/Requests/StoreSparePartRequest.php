<?php

namespace App\Custom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSparePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'part_number'      => 'required|string|max:100|unique:spare_parts,part_number',
            'description'      => 'nullable|string|max:2000',
            'category_id'      => 'nullable|exists:categories,id',
            'manufacturer_id'  => 'nullable|exists:manufacturers,id',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'location_id'      => 'nullable|exists:locations,id',
            'quantity_on_hand' => 'nullable|integer|min:0',
            'minimum_quantity' => 'required|integer|min:1',
            'unit_cost'        => 'nullable|numeric|min:0',
            'lead_time_days'   => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:2000',
            'asset_model_ids'  => 'nullable|array',
            'asset_model_ids.*' => 'exists:models,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Parça adı zorunludur.',
            'part_number.required'      => 'Parça numarası zorunludur.',
            'part_number.unique'        => 'Bu parça numarası zaten kayıtlı.',
            'minimum_quantity.required' => 'Minimum stok miktarı zorunludur.',
            'minimum_quantity.min'      => 'Minimum stok en az 1 olmalıdır.',
        ];
    }
}
