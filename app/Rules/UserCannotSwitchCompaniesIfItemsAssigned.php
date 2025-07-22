<?php

namespace App\Rules;
use App\Models\Setting;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCannotSwitchCompaniesIfItemsAssigned implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = request()->route('user');

        if (($value) && ($user->allAssignedCount() > 0) && (Setting::getSettings()->full_multiple_companies_support=='1')) {

            // Check for assets with a different company_id than the selected company_id
            $user_assets = $user->assets()->where('assets.company_id', '!=', $value)->count();
            if ($user_assets > 0) {
                $fail(trans('admin/users/message.error.multi_company_items_assigned'));
            }
        }
    }
}
