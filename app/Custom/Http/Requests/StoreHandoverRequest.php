<?php

namespace App\Custom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id'         => 'required|exists:locations,id',
            'outgoing_notes'      => 'nullable|string|max:2000',
            'outgoing_signature'  => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.asset_id'    => 'required|exists:assets,id',
            'items.*.condition'   => 'required|in:iyi,hasarli,eksik',
            'items.*.notes'       => 'nullable|string|max:500',
            'items.*.photo'       => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.required'       => 'Lokasyon seçimi zorunludur.',
            'items.required'             => 'En az bir ekipman kaydı gereklidir.',
            'items.min'                  => 'En az bir ekipman kaydı gereklidir.',
            'items.*.asset_id.required'  => 'Ekipman seçimi zorunludur.',
            'items.*.condition.required' => 'Ekipman durumu seçilmelidir.',
            'items.*.condition.in'       => 'Geçersiz durum değeri.',
        ];
    }
}
