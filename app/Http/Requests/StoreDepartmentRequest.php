<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Gate;

class StoreDepartmentRequest extends ImageUploadRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Department::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $modelRules = (new Department)->getRules();

        return array_merge(
            $modelRules,
        );
    }
}
