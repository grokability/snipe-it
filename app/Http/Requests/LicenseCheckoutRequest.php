<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'note'   => 'string|nullable',
            'asset_id'  => 'required_without:assigned_to',
        ];
    }
}
