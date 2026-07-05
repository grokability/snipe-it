<?php

namespace App\Http\Requests;

class CustomComponentReportRequest extends Request
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if ($this->filled('quantity_end') && ! $this->filled('quantity_start')) {
            $this->merge(['quantity_start' => 0]);
        }

        if ($this->filled('min_quantity_end') && ! $this->filled('min_quantity_start')) {
            $this->merge(['min_quantity_start' => 0]);
        }

        if ($this->filled('unit_cost_end') && ! $this->filled('unit_cost_start')) {
            $this->merge(['unit_cost_start' => 0]);
        }
    }

    public function rules(): array
    {
        return [
            'purchase_start' => 'date|date_format:Y-m-d|nullable',
            'purchase_end' => 'date|date_format:Y-m-d|nullable|after_or_equal:purchase_start',
            'quantity_start' => 'numeric|nullable',
            'quantity_end' => 'numeric|nullable|gte:quantity_start',
            'min_quantity_start' => 'numeric|nullable',
            'min_quantity_end' => 'numeric|nullable|gte:min_quantity_start',
            'unit_cost_start' => 'numeric|nullable',
            'unit_cost_end' => 'numeric|nullable|gte:unit_cost_start',
            'checkout_date_start' => 'date|date_format:Y-m-d|nullable',
            'checkout_date_end' => 'date|date_format:Y-m-d|nullable|after_or_equal:checkout_date_start',
            'created_start' => 'date|date_format:Y-m-d|nullable',
            'created_end' => 'date|date_format:Y-m-d|nullable|after_or_equal:created_start',
            'last_updated_start' => 'date|date_format:Y-m-d|nullable',
            'last_updated_end' => 'date|date_format:Y-m-d|nullable|after_or_equal:last_updated_start',
            'last_updated_before' => 'integer|nullable',
        ];
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }
}
