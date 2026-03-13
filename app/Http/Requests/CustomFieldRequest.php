<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CustomFieldRequest extends FormRequest
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
    public function rules(Request $request)
    {
        $rules = [];

        $rules['associate_fieldsets.*'] = 'nullable|integer|exists:custom_fieldsets,id';

        switch ($this->method()) {

            // Brand new
            case 'POST':
            {
                $rules['name'] = 'required|unique:custom_fields';
                break;
            }

            // Save all fields
            case 'PUT':
                $rules['name'] = 'required';
                break;

            // Save only what's passed
            case 'PATCH':
            {
                $rules['name'] = 'required';
                break;
            }

            default:break;
        }

        $rules['custom_format'] = 'valid_regex';

        return  $rules;
    }

    public function messages()
    {
        return [
            'associate_fieldsets.*.exists' => trans('admin/custom_fields/message/does_not_exist'),
        ];
    }
}
