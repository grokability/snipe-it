<?php

namespace App\Http\Requests;

use App\Models\AssetModel;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Gate;

class StoreAssetModelRequest extends ImageUploadRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', AssetModel::class);
    }

    public function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Scalar-guard: Category::find() on an array/object arg returns
        // a Collection (whereIn semantics) which does not expose
        // ->category_type and blows up. Non-scalar inputs land the row
        // in the standard validator with the model's category_id rule
        // (integer|exists), which cleanly rejects with 422.
        if ($this->category_id && is_scalar($this->category_id)) {
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
        // FK type rules live at the FormRequest layer so a malformed
        // payload (array/object where an int is expected) trips a
        // clean 422 before the controller reaches DB write. The
        // model's own $rules cover the ->save() path (factories,
        // seeders) but don't intercept the HTTP boundary.
        return array_merge(
            [
                'category_type' => 'in:asset',
                'category_id' => 'integer|nullable',
                'manufacturer_id' => 'integer|nullable',
                'depreciation_id' => 'integer|nullable',
                'fieldset_id' => 'integer|nullable',
            ],
            parent::rules(),
        );
    }

    public function messages(): array
    {
        $messages = ['category_type.in' => trans('admin/models/message.invalid_category_type')];

        return $messages;
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }
}
