<?php
namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Helpers\StorageHelper;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\AssetModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ActionlogsTransformer
{

    public function transformActionlogs (Collection $actionlogs, $total)
    {
        $array = array();
        $settings = Setting::getSettings();
        foreach ($actionlogs as $actionlog) {
            $array[] = self::transformActionlog($actionlog, $settings);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    private function clean_field($value)
    {
        // This object stuff is weird, and is used to make up for the fact that
        // older data can get strangely formatted if an asset existed,
        // then a new custom field is added, and the asset is saved again.
        // It can result in funnily-formatted strings like:
        //
        // {"_snipeit_right_sized_fault_tolerant_localareanetwo_1":
        // {"old":null,"new":{"value":"1579490695972","_snipeit_new_field_2":2,"_snipeit_new_field_3":"Monday, 20 January 2020 2:24:55 PM"}}
        // so we have to walk down that next level
        if(is_object($value) && isset($value->value)) {
            return $this->clean_field($value->value);
        }
        return is_scalar($value) || is_null($value) ? e($value) : e(json_encode($value));
    }

    public function transformActionlog (Actionlog $actionlog, $settings = null)
    {

        $icon = $actionlog->present()->icon();

        if (($actionlog->filename!='') && ($actionlog->action_type!='upload deleted')) {
            $icon =  Helper::filetype_icon($actionlog->filename);
        }

        static $custom_fields = false;

        if ($custom_fields === false) {
            $custom_fields = CustomField::all();
        }



        // This is necessary since we can't escape special characters within a JSON object
        if (($actionlog->log_meta) && ($actionlog->log_meta!='')) {
            $meta_array = json_decode($actionlog->log_meta);

            $clean_meta = [];

            if ($meta_array) {

                foreach ($meta_array as $fieldname => $fieldata) {

                    $clean_meta[$fieldname]['old'] = $this->clean_field($fieldata->old);
                    $clean_meta[$fieldname]['new'] = $this->clean_field($fieldata->new);

                    // this is a custom field
                    if (str_starts_with($fieldname, '_snipeit_')) {
                        
                        foreach ($custom_fields as $custom_field) {

                            if ($custom_field->db_column == $fieldname) {

                                if ($custom_field->field_encrypted == '1')  {

                                    // Unset these fields. We need to decrypt them, since even if the decrypted value
                                    // didn't change, their value in the DB will, so we have to compare the unencrypted version
                                    // to see if the values actually did change
                                    unset($clean_meta[$fieldname]);
                                    unset($clean_meta[$fieldname]);

                                    $enc_old = '';
                                    $enc_new = '';

                                    if ($this->clean_field($fieldata->old!='')) {
                                        try {
                                            $enc_old = Crypt::decryptString($this->clean_field($fieldata->old));
                                        } catch (\Exception $e) {
                                            Log::debug('Could not decrypt old field value - maybe the key changed?');
                                        }
                                    }

                                    if ($this->clean_field($fieldata->new!='')) {
                                        try {
                                            $enc_new = Crypt::decryptString($this->clean_field($fieldata->new));
                                        } catch (\Exception $e) {
                                            Log::debug('Could not decrypt new field value - maybe the key changed?');
                                        }
                                    }

                                    if ($enc_old != $enc_new) {
                                        $clean_meta[$fieldname]['old'] = "************";
                                        $clean_meta[$fieldname]['new'] = "************";

                                        // Display the changes if the user is an admin or superadmin
                                        if (Gate::allows('admin')) {
                                            $clean_meta[$fieldname]['old'] = ($enc_old) ? unserialize($enc_old, ['allowed_classes' => false]) : '';
                                            $clean_meta[$fieldname]['new'] = ($enc_new) ? unserialize($enc_new, ['allowed_classes' => false]) : '';
                                        }

                                    }


                                }

                            }

                        }
                    }

                }

            }
            $clean_meta= $this->changedInfo($clean_meta);
        }


        $array = [
            'id'          => (int) $actionlog->id,
            'icon'          => $icon,
            'file' => ($actionlog->filename!='')
                ?
                [
                    'url' => $actionlog->uploads_file_url(),
                    'filename' => $actionlog->filename,
                    'inlineable' => StorageHelper::allowSafeInline($actionlog->uploads_file_path()),
                    'exists_on_disk' => Storage::exists($actionlog->uploads_file_path()) ? true : false,
                ] : null,

            'item' => ($actionlog->item) ? [
                'id' => (int) $actionlog->item->id,
                'name' => e($actionlog->item->display_name) ?? null,
                'type' => e($actionlog->itemType()),
                'serial' => e($actionlog->item->serial) ? e($actionlog->item->serial) : null
            ] : null,
            'location' => ($actionlog->location) ? [
                'id' => (int) $actionlog->location->id,
                'name' => e($actionlog->location->name),
            ] : null,
            'created_at'    => Helper::getFormattedDateObject($actionlog->created_at, 'datetime'),
            'updated_at'    => Helper::getFormattedDateObject($actionlog->updated_at, 'datetime'),
            'next_audit_date' => ($actionlog->itemType()=='asset') ? Helper::getFormattedDateObject($actionlog->calcNextAuditDate(null, $actionlog->item), 'date'): null,
            'days_to_next_audit' => $actionlog->daysUntilNextAudit($settings->audit_interval, $actionlog->item),
            'action_type'   => $actionlog->present()->actionType(),
            'admin' => ($actionlog->adminuser) ? [
                'id' => (int) $actionlog->adminuser->id,
                'name' => e($actionlog->adminuser->display_name) ?? null,
                'first_name'=> e($actionlog->adminuser->first_name),
                'last_name'=> e($actionlog->adminuser->last_name)
            ] : null,
            'created_by' => ($actionlog->adminuser) ? [
                'id' => (int) $actionlog->adminuser->id,
                'name' => e($actionlog->adminuser->display_name),
                'first_name'=> e($actionlog->adminuser->first_name),
                'last_name'=> e($actionlog->adminuser->last_name)
            ] : null,
            'target' => ($actionlog->target) ? [
                'id' => (int) $actionlog->target->id,
                'name' => e($actionlog->target->display_name) ?? null,
                'type' => e($actionlog->targetType()),
            ] : null,

            'note'          => ($actionlog->note) ? Helper::parseEscapedMarkedownInline($actionlog->note): null,
            'signature_file'   => ($actionlog->accept_signature) ? route('log.signature.view', ['filename' => $actionlog->accept_signature ]) : null,
            'log_meta'          => ((isset($clean_meta)) && (is_array($clean_meta))) ? $clean_meta: null,
            'remote_ip' => e($actionlog->remote_ip) ?? null,
            'user_agent' => e($actionlog->user_agent) ?? null,
            'action_source'          => ($actionlog->action_source) ??  null,
            'action_date'   => ($actionlog->action_date) ? Helper::getFormattedDateObject($actionlog->action_date, 'datetime'): Helper::getFormattedDateObject($actionlog->created_at, 'datetime'),
        ];

//        Log::info("Clean Meta is: ".print_r($clean_meta,true));
        //dd($array);

        return $array;
    }



    public function transformCheckedoutActionlog (Collection $accessories_checkout, $total)
    {

        $array = array();
        foreach ($accessories_checkout as $user) {
            $array[] = (new UsersTransformer)->transformUser($user);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }
    /**
     * This takes the ids of the changed attributes and returns the names instead for the history view of an Asset
     *
     * @param  array $clean_meta
     * @return array
     */

    public function changedInfo(array $clean_meta)
    {
        static $location = false;
        static $supplier = false;
        static $model = false;
        static $status = false;
        static $company = false;


        if ($location === false) {
            $location = Location::select('id', 'name')->withTrashed()->get();
        }
        if ($supplier === false) {
            $supplier = Supplier::select('id', 'name')->withTrashed()->get();
        }
        if ($model === false) {
            $model = AssetModel::select('id', 'name')->withTrashed()->get();
        }
        if ($status === false) {
            $status = Statuslabel::select('id', 'name')->withTrashed()->get();
        }
        if ($company === false) {
            $company = Company::select('id', 'name')->get();
        }

        if(array_key_exists('rtd_location_id',$clean_meta)) {

            $oldRtd = $location->find($clean_meta['rtd_location_id']['old']);
            $oldRtdName = $oldRtd ? e($oldRtd->name) : trans('general.deleted');

            $newRtd = $location->find($clean_meta['rtd_location_id']['new']);
            $newRtdName = $newRtd ? e($newRtd->name) : trans('general.deleted');

            $clean_meta['rtd_location_id']['old'] = $clean_meta['rtd_location_id']['old'] ? "[id: ".$clean_meta['rtd_location_id']['old']."] ". $oldRtdName : '';
            $clean_meta['rtd_location_id']['new'] = $clean_meta['rtd_location_id']['new'] ? "[id: ".$clean_meta['rtd_location_id']['new']."] ". $newRtdName : '';
            $clean_meta[trans('admin/hardware/form.default_location')] = $clean_meta['rtd_location_id'];
            unset($clean_meta['rtd_location_id']);
        }


        if (array_key_exists('location_id', $clean_meta)) {

            $oldLocation = $location->find($clean_meta['location_id']['old']);
            $oldLocationName = $oldLocation ? e($oldLocation->name) : trans('general.deleted');

            $newLocation = $location->find($clean_meta['location_id']['new']);
            $newLocationName = $newLocation ? e($newLocation->name) : trans('general.deleted');


            $clean_meta['location_id']['old'] = $clean_meta['location_id']['old'] ? "[id: ".$clean_meta['location_id']['old']."] ". $oldLocationName : '';
            $clean_meta['location_id']['new'] = $clean_meta['location_id']['new'] ? "[id: ".$clean_meta['location_id']['new']."] ". $newLocationName : '';
            $clean_meta[trans('admin/locations/message.current_location')] = $clean_meta['location_id'];
            unset($clean_meta['location_id']);
        }

        if(array_key_exists('model_id', $clean_meta)) {

            $oldModel = $model->find($clean_meta['model_id']['old']);
            $oldModelName = $oldModel ? e($oldModel->name) : trans('admin/models/message.deleted');

            $newModel = $model->find($clean_meta['model_id']['new']);
            $newModelName = $newModel ? e($newModel->name) : trans('admin/models/message.deleted');

            $clean_meta['model_id']['old'] = "[id: ".$clean_meta['model_id']['old']."] ".$oldModelName;
            $clean_meta['model_id']['new'] = "[id: ".$clean_meta['model_id']['new']."] ".$newModelName; /** model is required at asset creation */

            $clean_meta[trans('admin/hardware/form.model')] = $clean_meta['model_id'];
            unset($clean_meta['model_id']);
        }
        if(array_key_exists('company_id', $clean_meta)) {

            $oldCompany = $company->find($clean_meta['company_id']['old']);
            $oldCompanyName = $oldCompany ? e($oldCompany->name) : trans('admin/company/message.deleted');

            $newCompany = $company->find($clean_meta['company_id']['new']);
            $newCompanyName = $newCompany ? e($newCompany->name) : trans('admin/company/message.deleted');

            $clean_meta['company_id']['old'] = $clean_meta['company_id']['old'] ? "[id: ".$clean_meta['company_id']['old']."] ". $oldCompanyName : trans('general.unassigned');
            $clean_meta['company_id']['new'] = $clean_meta['company_id']['new'] ? "[id: ".$clean_meta['company_id']['new']."] ". $newCompanyName : trans('general.unassigned');
            $clean_meta[trans('general.company')] = $clean_meta['company_id'];
            unset($clean_meta['company_id']);
        }
        if(array_key_exists('supplier_id', $clean_meta)) {

            $oldSupplier = $supplier->find($clean_meta['supplier_id']['old']);
            $oldSupplierName = $oldSupplier ? e($oldSupplier->name) : trans('admin/suppliers/message.deleted');

            $newSupplier = $supplier->find($clean_meta['supplier_id']['new']);
            $newSupplierName = $newSupplier ? e($newSupplier->name) : trans('admin/suppliers/message.deleted');

            $clean_meta['supplier_id']['old'] = $clean_meta['supplier_id']['old'] ? "[id: ".$clean_meta['supplier_id']['old']."] ". $oldSupplierName : trans('general.unassigned');
            $clean_meta['supplier_id']['new'] = $clean_meta['supplier_id']['new'] ? "[id: ".$clean_meta['supplier_id']['new']."] ". $newSupplierName : trans('general.unassigned');
            $clean_meta[trans('general.supplier')] = $clean_meta['supplier_id'];
            unset($clean_meta['supplier_id']);
        }
        if(array_key_exists('status_id', $clean_meta)) {

            $oldStatus = $status->find($clean_meta['status_id']['old']);
            $oldStatusName = $oldStatus ? e($oldStatus->name) : trans('admin/statuslabels/message.deleted_label');

            $newStatus = $status->find($clean_meta['status_id']['new']);
            $newStatusName = $newStatus ? e($newStatus->name) : trans('admin/statuslabels/message.deleted_label');

            $clean_meta['status_id']['old'] = $clean_meta['status_id']['old'] ? "[id: ".$clean_meta['status_id']['old']."] ". $oldStatusName : trans('general.unassigned');
            $clean_meta['status_id']['new'] = $clean_meta['status_id']['new'] ? "[id: ".$clean_meta['status_id']['new']."] ". $newStatusName : trans('general.unassigned');
            $clean_meta[trans('general.status_label')] = $clean_meta['status_id'];
            unset($clean_meta['status_id']);
        }
        if(array_key_exists('asset_eol_date', $clean_meta)) {
            $clean_meta[trans('admin/hardware/form.eol_date')] = $clean_meta['asset_eol_date'];
            unset($clean_meta['asset_eol_date']);
        }

        return $clean_meta;

    }



}
