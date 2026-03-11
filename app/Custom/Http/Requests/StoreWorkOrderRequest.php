<?php

namespace App\Custom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id'    => 'required|exists:assets,id',
            'priority'    => 'required|in:kritik,yuksek,normal',
            'description' => 'required|string|min:10',
            'location_id' => 'nullable|exists:locations,id',
            'photo'       => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'asset_id.required'    => 'Varlık seçimi zorunludur.',
            'asset_id.exists'      => 'Seçilen varlık bulunamadı.',
            'priority.required'    => 'Öncelik seçimi zorunludur.',
            'priority.in'          => 'Geçersiz öncelik değeri.',
            'description.required' => 'Arıza açıklaması zorunludur.',
            'description.min'      => 'Arıza açıklaması en az 10 karakter olmalıdır.',
            'photo.image'          => 'Yüklenen dosya bir resim olmalıdır.',
            'photo.max'            => 'Fotoğraf en fazla 2MB olabilir.',
        ];
    }
}
