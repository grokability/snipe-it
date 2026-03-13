<?php

namespace App\Rules;

use App\Models\Statuslabel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;


class AssetCannotBeCheckedOutToNondeployableStatus implements DataAwareRule, ValidationRule
{

    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];


    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check to see if any of the assign-ish fields are set
        if ((isset($this->data['assigned_to'])) || (isset($this->data['assigned_user'])) || (isset($this->data['assigned_location']))  || (isset($this->data['assigned_asset'])) || (isset($this->data['assigned_type']))) {

            if (($value) && ($label = Statuslabel::find($value)) && ($label->getStatuslabelType()!='deployable')) {
                $fail(trans('admin/hardware/form.asset_not_deployable'));
            }

        }


    }
}
