<?php

namespace App\Http\Requests;

use App\Models\Setting;

class AssetCheckinRequest extends Request
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
        $settings = Setting::getSettings();

        $rules = [
            'set_requestable' => 'nullable|boolean',
        ];

        if ($settings->require_checkinout_notes) {
            $rules['note'] = 'string|required';
        }

        return $rules;
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }
}
