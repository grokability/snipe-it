<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreSecuritySettings extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('superuser');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pwd_secure_min'                      => 'numeric|required|min:8',
            'custom_forgot_pass_url'              => 'url|nullable',
            'privacy_policy_link'                 => 'nullable|url',
            'login_remote_user_enabled'           => 'numeric|nullable',
            'login_common_disabled'               => 'numeric|nullable',
            'login_remote_user_custom_logout_url' => 'string|nullable',
            'login_remote_user_header_name'       => 'string|nullable',
        ];
    }
}
