<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
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
                if ($this->input('ldap_import') == false && $this->boolean('activated')) {
                    // Password rules only apply when the user will actually be
                    // able to log in. If "This user can login" is unchecked,
                    // the password is functionally useless (activation gates
                    // authentication) so we skip the required + complexity
                    // rules entirely and the controller inserts an unencrypted
                    // placeholder via User::noPassword() so no Hash::check can
                    // ever match it at login time.
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
     * be empty:
     *
     *  - Floater mode ON:  the resulting user would become a system-wide
     *    floater (sees everything). That's the privilege-escalation vector
     *    flagged in #19200 — only superusers (and users already able to grant
     *    floater status) may do this deliberately.
     *  - Floater mode OFF (strict FMCS): the resulting user would land with
     *    an empty pivot and be immediately invisible to the creator's own
     *    scope. That's the visibility bug flagged in #19192 — only superusers
     *    (whose scope reaches nulls) may do this.
     *
     * Applies to both web and API store/update.
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
            if ($inActualConsole) {
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
                $settings = Setting::getSettings();
                $actor = auth()->user();
                $creatorIsSuper = (bool) $actor?->isSuperUser();
                $creatorHasCompanies = (bool) $actor?->companies()->exists();
                $strictFmcs = $settings->full_multiple_companies_support && ! $settings->null_company_is_floater;

                // Strict-FMCS #19192 gate — hits before the older floater
                // gate so its more specific error message wins when both
                // apply. Skips uncompanied actors because they legitimately
                // work in the null pseudo-company namespace under strict
                // mode; forcing them to add memberships they don't have
                // would lock them out of their normal workflow.
                if ($strictFmcs && ! $creatorIsSuper && $creatorHasCompanies) {
                    $validator->errors()->add('company_ids', trans('validation.fmcs_company', ['attribute' => trans('general.company')]));

                    return;
                }

                // Original #19200 floater-grant gate.
                if (! $actor?->canGrantFloaterStatus()) {
                    $validator->errors()->add('company_ids', trans('admin/users/general.cannot_make_floater'));
                }
            }
        });
    }
}
