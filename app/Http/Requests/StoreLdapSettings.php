<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreLdapSettings extends FormRequest
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
            'ldap_username_field' => 'not_in:sAMAccountName|required_if:ldap_enabled,1',
            'ldap_auth_filter_query' => 'not_in:uid=samaccountname|required_if:ldap_enabled,1',
            'ldap_filter' => 'nullable|regex:"^[^(]"|required_if:ldap_enabled,1',
            'ldap_server' => 'nullable|required_if:ldap_enabled,1|starts_with:ldap://,ldaps://',
            'ldap_basedn' => 'nullable|required_if:ldap_enabled,1',
            'ldap_fname_field' => 'nullable|required_if:ldap_enabled,1',
            'custom_forgot_pass_url' => 'nullable|url',
        ];
    }

}
