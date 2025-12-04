<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\MayContainCustomFields;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Helper;
use App\Models\Setting;
use App\Models\AssetModel;
use App\Rules\UniqueUndeleted;
use Illuminate\Support\Str;

class CreateMultipleAssetRequest extends ImageUploadRequest //should I extend from StoreAssetRequest? FIXME OR TODO OR THINKME
{
    use MayContainCustomFields;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //TODO - should I do the auth check here?
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        //grab the rules for serials and asset_tags, and tweak them into an array context for multi-create usage
        $modelRules = (new Asset)->getRules();
        unset($modelRules['serial']);

        $asset_tag_rules = $modelRules['asset_tag'];
        unset($modelRules['asset_tag']);
        // now we replace the 'not_array' rule with 'distinct'
        array_splice($asset_tag_rules, array_search('not_array', $asset_tag_rules), 1, 'distinct');
        // and replace the 'unique_undeleted' rule with the Rule object
        foreach ($asset_tag_rules as $i => $asset_tag_rule) {
            if (Str::startsWith($asset_tag_rule, 'unique_undeleted')) {
                $asset_tag_rules[$i] = new UniqueUndeleted('assets', 'asset_tag');
            }
        }

        $serials_unique = Setting::getSettings()['unique_serial'];
        $serials_required = AssetModel::find($this?->model_id)?->require_serial;

        $serial_rules = ['string'];
        if ($serials_unique) {
//            $serial_rules[] = 'unique_undeleted:assets,serial';
            $serial_rules[] = new UniqueUndeleted('assets', 'serial');
            $serial_rules[] = 'distinct';
        }
        if ($serials_required) {
            $serial_rules[] = 'required';
        } else {
            $serial_rules[] = 'nullable';
        }

        return array_merge($modelRules, [
            'serials.*' => $serial_rules,
            'asset_tags.*' => $asset_tag_rules,
        ]);
    }
}
