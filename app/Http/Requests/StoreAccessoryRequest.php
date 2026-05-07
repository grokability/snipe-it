<?php

namespace App\Http\Requests;

use App\Models\Accessory;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Gate;

class StoreAccessoryRequest extends ImageUploadRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Accessory::class);
    }

    public function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if ($this->category_id) {
            if ($category = Category::find($this->category_id)) {
                $this->merge([
                    'category_type' => $category->category_type ?? null,
                ]);
            }
        }

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            ['category_type' => 'in:accessory'],
            parent::rules(),
        );
    }

    public function messages(): array
    {
        $messages = ['category_type.in' => trans('admin/accessories/message.invalid_category_type')];

        return $messages;
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }
}
