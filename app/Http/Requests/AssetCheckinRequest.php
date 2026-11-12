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
        return static::rulesArray();
    }

    /**
     * Statically-callable rules array so non-FormRequest callers (the MCP
     * CheckinAssetTool, Artisan commands, tests) can validate against the
     * same shape without constructing a Request instance.
     */
    public static function rulesArray(): array
    {
        $settings = Setting::getSettings();

        $rules = [
            'requestable' => 'nullable|boolean',
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
