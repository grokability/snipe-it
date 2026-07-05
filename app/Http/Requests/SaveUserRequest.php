<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Setting;
use App\Rules\UserCannotSwitchCompaniesIfItemsAssigned;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveUserRequest extends FormRequest
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

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'department_id' => 'nullable|integer|exists:departments,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'integer|exists:companies,id',
            'location_id' => 'nullable|integer|exists:locations,id|fmcs_location',
        ];

        switch ($this->method()) {

            // Brand new user
            case 'POST':
                $rules['first_name'] = 'required|string|min:1';
                $rules['username'] = 'required_unless:ldap_import,1|string|min:1';
                if ($this->input('ldap_import') == false) {
                    $rules['password'] = Setting::passwordComplexityRulesSaving('store').'|confirmed';
                }
                break;

                // Save all fields
            case 'PUT':
                $rules['first_name'] = 'required|string|min:1';
                $rules['username'] = 'required_unless:ldap_import,1|string|min:1';
                $rules['password'] = Setting::passwordComplexityRulesSaving('update').'|confirmed';
                $rules['company_id'] = ['nullable', 'integer', 'exists:companies,id', new UserCannotSwitchCompaniesIfItemsAssigned];
                break;

                // Save only what's passed
            case 'PATCH':
                $rules['password'] = Setting::passwordComplexityRulesSaving('update');
                $rules['company_id'] = ['nullable', 'integer', 'exists:companies,id', new UserCannotSwitchCompaniesIfItemsAssigned];
                break;

            default:
                break;
        }

        return $rules;
    }

    /**
     * Block non-superusers from saving a user whose resulting company set would
     * be empty *while floater mode is enabled* — that combination promotes the
     * target user to a system-wide floater (sees everything), and is the
     * privilege-escalation vector flagged in #19200. Superusers can still make
     * floaters intentionally. Applies to web and API store/update.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Console commands (artisan/seeders/queue workers) need to be able
            // to manage users without going through the HTTP floater gate.
            // We deliberately check runningInConsole() (rather than "no auth
            // user") so a future unauthenticated HTTP endpoint can never
            // sneak past the gate — flagged in PR review for #19200. Exclude
            // the PHPUnit/Pest runner, which also reports as "in console" but
            // is using the HTTP stack and must see the gate fire.
            $inActualConsole = app()->runningInConsole() && ! app()->runningUnitTests();
            if ($inActualConsole || auth()->user()?->canGrantFloaterStatus()) {
                return;
            }

            // Mirror the controller's resolution: prefer company_ids[], fall
            // back to legacy company_id, intval, drop empties.
            $submitted = (array) ($this->input('company_ids') ?? ($this->filled('company_id') ? [$this->input('company_id')] : []));
            $submitted = array_filter(array_map('intval', $submitted));

            // Filter to companies the actor can actually assign (matches the
            // controller's syncCompaniesWithLogging(Company::getIdsForCurrentUser(...))
            // step). If nothing survives the filter, the save would clear the
            // user's pivot.
            $effective = Company::getIdsForCurrentUser($submitted);

            if (empty($effective)) {
                $validator->errors()->add('company_ids', trans('admin/users/general.cannot_make_floater'));
            }
        });
    }
}
