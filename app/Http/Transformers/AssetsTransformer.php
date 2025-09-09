<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AssetsTransformer
{
    public function transformAssets(Collection $assets, $total)
    {
        $array = [];
        foreach ($assets as $asset) {
            $array[] = self::transformAsset($asset);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformAsset(Asset $asset)
    {
        // This uses the getSettings() method so we're pulling from the cache versus querying the settings on single asset
        $setting = Setting::getSettings();

        $array = [
            'id' => (int) $asset->id,
            'name' => e($asset->name),
            'asset_tag' => e($asset->asset_tag),
            'serial' => e($asset->serial),
            'model' => ($asset->model) ? [
                'id' => (int) $asset->model->id,
                'name'=> e($asset->model->name),
            ] : null,
            'byod' => ($asset->byod ? true : false),
            'requestable' => ($asset->requestable ? true : false),

            'model_number' => (($asset->model) && ($asset->model->model_number)) ? e($asset->model->model_number) : null,
            'eol' => (($asset->asset_eol_date != '') && ($asset->purchase_date != '')) ? (int) Carbon::parse($asset->asset_eol_date)->diffInMonths($asset->purchase_date, true) . ' months' : null,
            'asset_eol_date' => ($asset->asset_eol_date != '') ? Helper::getFormattedDateObject($asset->asset_eol_date, 'date') : null,
            'status_label' => ($asset->assetstatus) ? [
                'id' => (int) $asset->assetstatus->id,
                'name'=> e($asset->assetstatus->name),
                'status_type'=> e($asset->assetstatus->getStatuslabelType()),
                'status_meta' => e($asset->present()->statusMeta),
            ] : null,
            'category' => (($asset->model) && ($asset->model->category)) ? [
                'id' => (int) $asset->model->category->id,
                'name'=> e($asset->model->category->name),
            ] : null,
            'manufacturer' => (($asset->model) && ($asset->model->manufacturer)) ? [
                'id' => (int) $asset->model->manufacturer->id,
                'name'=> e($asset->model->manufacturer->name),
            ] : null,
            'depreciation' => (($asset->model) && ($asset->model->depreciation)) ? [
                'id' => (int) $asset->model->depreciation->id,
                'name'=> e($asset->model->depreciation->name),
                'months'=> (int) $asset->model->depreciation->months,
                'type'=>  e($asset->model->depreciation->depreciation_type),
                'minimum'=> ($asset->model->depreciation->depreciation_min) ? (int) $asset->model->depreciation->depreciation_min : null,
            ] : null,
            'supplier' => ($asset->supplier) ? [
                'id' => (int) $asset->supplier->id,
                'name'=> e($asset->supplier->name),
            ] : null,
            'notes' => ($asset->notes) ? Helper::parseEscapedMarkedownInline($asset->notes) : null,
            'order_number' => ($asset->order_number) ? e($asset->order_number) : null,
            'company' => ($asset->company) ? [
                'id' => (int) $asset->company->id,
                'name'=> e($asset->company->name),
            ] : null,
            'location' => ($asset->location) ? [
                'id' => (int) $asset->location->id,
                'name'=> e($asset->location->name),
            ] : null,
            'rtd_location' => ($asset->defaultLoc) ? [
                'id' => (int) $asset->defaultLoc->id,
                'name'=> e($asset->defaultLoc->name),
            ] : null,
            'image' => ($asset->getImageUrl()) ? $asset->getImageUrl() : null,
            'qr' => ($setting->qr_code=='1') ? config('app.url').'/uploads/barcodes/qr-'.str_slug($asset->asset_tag).'-'.str_slug($asset->id).'.png' : null,
            'alt_barcode' => ($setting->alt_barcode_enabled=='1') ? config('app.url').'/uploads/barcodes/'.str_slug($setting->alt_barcode).'-'.str_slug($asset->asset_tag).'.png' : null,
            'assigned_to' => $this->transformAssignedTo($asset),
            'warranty_months' =>  ($asset->warranty_months > 0) ? e($asset->warranty_months.' '.trans('admin/hardware/form.months')) : null,
            'warranty_expires' => ($asset->warranty_months > 0) ? Helper::getFormattedDateObject($asset->warranty_expires, 'date') : null,
            'created_by' => ($asset->adminuser) ? [
                'id' => (int) $asset->adminuser->id,
                'name'=> e($asset->adminuser->display_name),
            ] : null,
            'created_at' => Helper::getFormattedDateObject($asset->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($asset->updated_at, 'datetime'),
            'last_audit_date' => Helper::getFormattedDateObject($asset->last_audit_date, 'datetime'),
            'next_audit_date' => Helper::getFormattedDateObject($asset->next_audit_date, 'date'),
            'deleted_at' => Helper::getFormattedDateObject($asset->deleted_at, 'datetime'),
            'purchase_date' => Helper::getFormattedDateObject($asset->purchase_date, 'date'),
            'age' => $asset->purchase_date ? $asset->purchase_date->locale(app()->getLocale())->diffForHumans() : '',
            'last_checkout' => Helper::getFormattedDateObject($asset->last_checkout, 'datetime'),
            'last_checkin' => Helper::getFormattedDateObject($asset->last_checkin, 'datetime'),
            'expected_checkin' => Helper::getFormattedDateObject($asset->expected_checkin, 'date'),
            'purchase_cost' => Helper::formatCurrencyOutput($asset->purchase_cost),
            'checkin_counter' => (int) $asset->checkin_counter,
            'checkout_counter' => (int) $asset->checkout_counter,
            'requests_counter' => (int) $asset->requests_counter,
            'user_can_checkout' => (bool) $asset->availableForCheckout(),
            'book_value' => Helper::formatCurrencyOutput($asset->getDepreciatedValue()),
        ];


        if (($asset->model) && ($asset->model->fieldset) && ($asset->model->fieldset->fields->count() > 0)) {
            $fields_array = [];

            foreach ($asset->model->fieldset->fields as $field) {
                if ($field->isFieldDecryptable($asset->{$field->db_column})) {
                    $decrypted = Helper::gracefulDecrypt($field, $asset->{$field->db_column});
                    $value = (Gate::allows('assets.view.encrypted_custom_fields')) ? $decrypted : strtoupper(trans('admin/custom_fields/general.encrypted'));

                    if ($field->format == 'DATE'){
                        if (Gate::allows('assets.view.encrypted_custom_fields')){
                            $value = Helper::getFormattedDateObject($value, 'date', false);
                        } else {
                           $value = strtoupper(trans('admin/custom_fields/general.encrypted'));
                        }
                    }

                    $fields_array[$field->name] = [
                            'field' => e($field->db_column),
                            'value' => e($value),
                            'field_format' => $field->format,
                            'element' => $field->element,
                        ];

                } else {
                    $value = $asset->{$field->db_column};

                    if (($field->format == 'DATE') && (!is_null($value)) && ($value!='')){
                        $value = Helper::getFormattedDateObject($value, 'date', false);
                    }
                    
                    $fields_array[$field->name] = [
                        'field' => e($field->db_column),
                        'value' => e($value),
                        'field_format' => $field->format,
                        'element' => $field->element,
                    ];
                }

                $array['custom_fields'] = $fields_array;
            }
        } else {
            $array['custom_fields'] = new \stdClass; // HACK to force generation of empty object instead of empty list
        }

        $permissions_array['available_actions'] = [
            'checkout'      => ($asset->deleted_at=='' && Gate::allows('checkout', Asset::class)) ? true : false,
            'checkin'       => ($asset->deleted_at=='' && Gate::allows('checkin', Asset::class)) ? true : false,
            'clone'         => Gate::allows('create', Asset::class) ? true : false,
            'restore'       => ($asset->deleted_at!='' && Gate::allows('create', Asset::class)) ? true : false,
            'update'        => ($asset->deleted_at=='' && Gate::allows('update', Asset::class)) ? true : false,
            'audit'        => Gate::allows('audit', Asset::class) ? true : false,
            'delete'        => ($asset->deleted_at=='' && $asset->assigned_to =='' && Gate::allows('delete', Asset::class) && ($asset->deleted_at == '')) ? true : false,
        ];      


        if (request('components')=='true') {
        
            if ($asset->components) {
                $array['components'] = [];
    
                foreach ($asset->components as $component) {
                    $array['components'][] = [
                        
                            'id' => $component->id,
                            'pivot_id' => $component->pivot->id,
                            'name' => e($component->name),
                            'qty' => $component->pivot->assigned_qty,
                            'price_cost' => $component->purchase_cost,
                            'purchase_total' => $component->purchase_cost * $component->pivot->assigned_qty,
                            'checkout_date' => Helper::getFormattedDateObject($component->pivot->created_at, 'datetime') ,
                        
                    ];
                }
            }

        }
        
        $array += $permissions_array;

        return $array;
    }

    public function transformAssetsDatatable($assets)
    {
        return (new DatatablesTransformer)->transformDatatables($assets);
    }

    public function transformAssignedTo($asset)
    {
        if ($asset->checkedOutToUser()) {
            return $asset->assigned ? [
                    'id' => (int) $asset->assigned->id,
                    'username' => e($asset->assigned->username),
                    'name' => e($asset->assigned->getFullNameAttribute()),
                    'first_name'=> e($asset->assigned->first_name),
                    'last_name'=> ($asset->assigned->last_name) ? e($asset->assigned->last_name) : null,
                    'email'=> ($asset->assigned->email) ? e($asset->assigned->email) : null,
                    'employee_number' =>  ($asset->assigned->employee_num) ? e($asset->assigned->employee_num) : null,
                    'jobtitle' => $asset->assigned->jobtitle ? e($asset->assigned->jobtitle) : null,
                    'type' => 'user',
                ] : null;
        }

        return $asset->assigned ? [
            'id' => $asset->assigned->id,
            'name' => e($asset->assigned->display_name),
            'type' => $asset->assignedType()
        ] : null;
    }


    public function transformRequestedAssets(Collection $assets, $total)
    {
        $array = [];
        foreach ($assets as $asset) {
            $array[] = self::transformRequestedAsset($asset);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformRequestedAsset(Asset $asset)
    {
        $array = [
            'id' => (int)$asset->id,
            'name' => e($asset->name),
            'asset_tag' => e($asset->asset_tag),
            'serial' => e($asset->serial),
            'image' => ($asset->getImageUrl()) ? $asset->getImageUrl() : null,
            'model' => ($asset->model) ? e($asset->model->name) : null,
            'model_number' => (($asset->model) && ($asset->model->model_number)) ? e($asset->model->model_number) : null,
            'expected_checkin' => Helper::getFormattedDateObject($asset->expected_checkin, 'date'),
            'location' => ($asset->location) ? e($asset->location->name) : null,
            'status' => ($asset->assetstatus) ? $asset->present()->statusMeta : null,
            'assigned_to_self' => ($asset->assigned_to == auth()->id()),
        ];

        if (($asset->model) && ($asset->model->fieldset) && ($asset->model->fieldset->fields->count() > 0)) {
            $fields_array = [];

            foreach ($asset->model->fieldset->fields as $field) {

                // Only display this if it's allowed via the custom field setting
                if (($field->field_encrypted == '0') && ($field->show_in_requestable_list == '1')) {

                    $value = $asset->{$field->db_column};
                    if (($field->format == 'DATE') && (!is_null($value)) && ($value != '')) {
                        $value = Helper::getFormattedDateObject($value, 'date', false);
                    }

                    $fields_array[$field->db_column] = e($value);
                }

                $array['custom_fields'] = $fields_array;
            }
        } else {
            $array['custom_fields'] = new \stdClass; // HACK to force generation of empty object instead of empty list
        }


        $permissions_array['available_actions'] = [
            'cancel' => ($asset->isRequestedBy(auth()->user())) ? true : false,
            'request' => ($asset->isRequestedBy(auth()->user())) ? false : true,
        ];

        $array += $permissions_array;
        return $array;
    }

    public function transformAssetCompact(Asset $asset)
    {
        $array = [
            'id' => (int) $asset->id,
            'image' => ($asset->getImageUrl()) ? $asset->getImageUrl() : null,
            'type' => 'asset',
            'name' => e($asset->display_name),
            'model' => ($asset->model) ? e($asset->model->name) : null,
            'model_number' => (($asset->model) && ($asset->model->model_number)) ? e($asset->model->model_number) : null,
            'asset_tag' => e($asset->asset_tag),
            'serial' => e($asset->serial),
        ];

        return $array;
    }

    public function transformCheckedoutAccessories($accessory_checkouts, $total)
    {

        $array = [];
        foreach ($accessory_checkouts as $checkout) {
            $array[] = self::transformCheckedoutAccessory($checkout);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }


    public function transformCheckedoutAccessory(AccessoryCheckout $accessory_checkout)
    {
        if ($accessory_checkout->accessory) {
            $array = [
                'id' => $accessory_checkout->id,
                'accessory' => [
                    'id' => $accessory_checkout->accessory->id,
                    'name' => $accessory_checkout->accessory->name,
                ],
                'assigned_to' => $accessory_checkout->assigned_to,
                'image' => ($accessory_checkout->accessory->image) ? Storage::disk('public')->url('accessories/' . e($accessory_checkout->accessory->image)) : null,
                'note' => $accessory_checkout->note ? e($accessory_checkout->note) : null,
                'created_by' => $accessory_checkout->adminuser ? [
                    'id' => (int)$accessory_checkout->adminuser->id,
                    'name' => e($accessory_checkout->adminuser->present()->fullName),
                ] : null,
                'created_at' => Helper::getFormattedDateObject($accessory_checkout->created_at, 'datetime'),
                'deleted_at' => Helper::getFormattedDateObject($accessory_checkout->deleted_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'checkout' => false,
                'checkin' => Gate::allows('checkin', Accessory::class),
            ];

            $array += $permissions_array;
            return $array;
        }
    }

}
