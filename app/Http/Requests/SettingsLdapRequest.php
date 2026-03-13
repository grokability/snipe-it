<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Session;

class SettingsLdapRequest extends Request
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
        $rules = [
            'ldap_server'               => 'sometimes|required_if:ldap_enabled,1|url|nullable',
            'ldap_uname'                => 'sometimes|required_if:ldap_enabled,1|nullable',
            'ldap_basedn'               => 'sometimes|required_if:ldap_enabled,1|nullable',
            'ldap_filter'               => 'sometimes|required_if:ldap_enabled,1|nullable',
            'ldap_username_field'       => 'sometimes|required_if:ldap_enabled,1|nullable',
            'ldap_fname_field'          => 'sometimes|required_if:ldap_enabled,1|nullable',
            'ldap_lname_field'          => 'sometimes|required_if:ldap_enabled,1|nullable',
            'ldap_auth_filter_query'    => 'sometimes|required_if:ldap_enabled,1|nullable',
            'ldap_version'              => 'sometimes|required_if:ldap_enabled,1|nullable',
            'ad_domain'                 => 'sometimes|required_if:is_ad,1|nullable',
        ];

        return $rules;
    }

    public function response(array $errors)
    {
        $this->session()->flash('errors', Session::get('errors', new \Illuminate\Support\ViewErrorBag)
            ->put('default', new \Illuminate\Support\MessageBag($errors)));
        \Input::flash();

        return parent::response($errors);
    }
}
