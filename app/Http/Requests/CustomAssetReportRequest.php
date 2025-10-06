<?php

namespace App\Http\Requests;

class CustomAssetReportRequest extends Request
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


    public function prepareForValidation()
    {
        if($this->filled('purchase_cost_end') && !$this->filled('purchase_cost_start')){
            $this->merge(['purchase_cost_start' => 0 ]);
        }
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'purchase_start'        => 'date|date_format:Y-m-d|nullable',
            'purchase_end'          => 'date|date_format:Y-m-d|nullable',
            'purchase_cost_end'     => 'numeric|nullable|gte:purchase_cost_start',
            'created_start'         => 'date|date_format:Y-m-d|nullable',
            'created_end'           => 'date|date_format:Y-m-d|nullable',
            'checkout_date_start'   => 'date|date_format:Y-m-d|nullable',
            'checkout_date_end'     => 'date|date_format:Y-m-d|nullable',
            'expected_checkin_start'      => 'date|date_format:Y-m-d|nullable',
            'expected_checkin_end'        => 'date|date_format:Y-m-d|nullable',
            'checkin_date_start'      => 'date|date_format:Y-m-d|nullable',
            'checkin_date_end'        => 'date|date_format:Y-m-d|nullable',
            'last_audit_start'      => 'date|date_format:Y-m-d|nullable',
            'last_audit_end'        => 'date|date_format:Y-m-d|nullable',
            'next_audit_start'      => 'date|date_format:Y-m-d|nullable',
            'next_audit_end'        => 'date|date_format:Y-m-d|nullable',
        ];
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }
}
