<?php

namespace App\Http\Transformers;

use App\Enums\ActionType;
use App\Enums\FileStorage;
use App\Helpers\Helper;
use App\Helpers\StorageHelper;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\License;
use App\Models\Location;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ActionlogsTransformer
{
    public function transformActionlogs(Collection $actionlogs, $total)
    {
        $array = [];
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
        if (is_object($value) && isset($value->value)) {
            return $this->clean_field($value->value);
        }

        return is_scalar($value) || is_null($value) ? e($value) : e(json_encode($value));
    }

    public function transformActionlog(Actionlog $actionlog, $settings = null)
    {

        $icon = $actionlog->present()->icon();

        // File-type icon only wins for events that are inherently ABOUT
        // a file (`uploaded`). Other events that happen to carry a
        // receipt attachment (QuantityAdjust with an invoice PDF,
        // checkouts with a signed acceptance form, etc.) keep their
        // semantic icon so the history tab telegraphs what happened, not
        // that a file was attached.
        if ($actionlog->action_type === ActionType::Uploaded->value && $actionlog->filename != '') {
            $icon = Helper::filetype_icon($actionlog->filename);
        }

        static $custom_fields = false;

        if ($custom_fields === false) {
            $custom_fields = CustomField::all();
        }

        // This is necessary since we can't escape special characters within a JSON object
        $meta_array = null;
        if (($actionlog->log_meta) && ($actionlog->log_meta != '')) {
            $meta_array = json_decode($actionlog->log_meta);

            $clean_meta = [];

            if ($meta_array) {

                foreach ($meta_array as $fieldname => $fieldata) {

                    // Snapshot of audit-visible custom field values — handled separately below.
                    if ($fieldname === '_audit_snapshot') {
                        continue;
                    }

                    // Some log_meta entries carry a scalar value rather than the
                    // {old, new} field-diff shape (e.g. the "reason" attached to
                    // an impersonation start). Copy scalars through untouched so
                    // the transformer doesn't blow up trying to read ->old on a
                    // string.
                    if (! is_object($fieldata)) {
                        $clean_meta[$fieldname] = $fieldata;

                        continue;
                    }

                    $clean_meta[$fieldname]['old'] = $this->clean_field($fieldata->old);
                    $clean_meta[$fieldname]['new'] = $this->clean_field($fieldata->new);

                    // this is a custom field
                    if (str_starts_with($fieldname, '_snipeit_')) {

                        foreach ($custom_fields as $custom_field) {

                            if ($custom_field->db_column == $fieldname) {

                                if ($custom_field->field_encrypted == '1') {

                                    // Unset these fields. We need to decrypt them, since even if the decrypted value
                                    // didn't change, their value in the DB will, so we have to compare the unencrypted version
                                    // to see if the values actually did change
                                    unset($clean_meta[$fieldname]);
                                    unset($clean_meta[$fieldname]);

                                    $enc_old = '';
                                    $enc_new = '';

                                    if ($this->clean_field($fieldata->old != '')) {
                                        try {
                                            $enc_old = Crypt::decryptString($this->clean_field($fieldata->old));
                                        } catch (\Exception $e) {
                                            Log::debug('Could not decrypt old field value - maybe the key changed?');
                                        }
                                    }

                                    if ($this->clean_field($fieldata->new != '')) {
                                        try {
                                            $enc_new = Crypt::decryptString($this->clean_field($fieldata->new));
                                        } catch (\Exception $e) {
                                            Log::debug('Could not decrypt new field value - maybe the key changed?');
                                        }
                                    }

                                    if ($enc_old != $enc_new) {
                                        $clean_meta[$fieldname]['old'] = '************';
                                        $clean_meta[$fieldname]['new'] = '************';

                                        // Display the changes if the user has permission to view encrypted custom fields
                                        if (Gate::allows('assets.view.encrypted_custom_fields')) {
                                            $clean_meta[$fieldname]['old'] = ($enc_old) ? e(unserialize($enc_old, ['allowed_classes' => false])) : '';
                                            $clean_meta[$fieldname]['new'] = ($enc_new) ? e(unserialize($enc_new, ['allowed_classes' => false])) : '';
                                        }

                                    }

                                }

                            }

                        }
                    }

                }

            }
            $clean_meta = $this->changedInfo($clean_meta);
        }

        // License::getSerialAttribute and Actionlog::getLogMetaAttribute
        // apply the licenses.keys mask upstream, so the raw product key
        // never reaches this layer when the viewer is not permitted.
        $itemSerial = ($actionlog->item && $actionlog->item->serial)
            ? e($actionlog->item->serial)
            : null;

        $array = [
            'id' => (int) $actionlog->id,
            'icon' => $icon,
            'file' => ($actionlog->filename != '')
                ?
                [
                    'url' => $actionlog->uploads_file_url(),
                    'filename' => $actionlog->filename,
                    'inlineable' => StorageHelper::allowSafeInline($actionlog->uploads_file_path()),
                    'exists_on_disk' => Storage::exists($actionlog->uploads_file_path()) ? true : false,
                    'mediatype' => StorageHelper::getMediaType($actionlog->uploads_file_path()),
                ] : null,

            'item' => ($actionlog->item) ? [
                'id' => (int) $actionlog->item->id,
                'name' => e($actionlog->item->display_name) ?? null,
                'type' => e($actionlog->itemType()),
                'serial' => $itemSerial,
                // Per-row view flag consumed by polymorphicItemFormatter
                // to render the name as plain text when the caller
                // cannot reach the show controller for this record.
                // Without it, clicking through a scoped viewer's
                // recent-activity row 403s on the target page.
                'viewable' => Gate::allows('view', $actionlog->item),
            ] : null,
            'location' => ($actionlog->location) ? [
                'id' => (int) $actionlog->location->id,
                'name' => e($actionlog->location->name),
                'tag_color' => ($actionlog->location->tag_color) ? e($actionlog->location->tag_color) : null,
            ] : null,
            'created_at' => Helper::getFormattedDateObject($actionlog->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($actionlog->updated_at, 'datetime'),
            'next_audit_date' => ($actionlog->itemType() == 'asset') ? Helper::getFormattedDateObject($actionlog->calcNextAuditDate(null, $actionlog->item), 'date') : null,
            'days_to_next_audit' => $actionlog->daysUntilNextAudit($settings->audit_interval, $actionlog->item),
            'action_type' => $actionlog->present()->actionType(),
            'admin' => ($actionlog->adminuser) ? [
                'id' => (int) $actionlog->adminuser->id,
                'name' => e($actionlog->adminuser->display_name) ?? null,
                'first_name' => e($actionlog->adminuser->first_name),
                'last_name' => e($actionlog->adminuser->last_name),
                // Per-row view flag consumed by
                // genericColumnObjLinkFormatter to render the actor
                // as plain text when the caller cannot reach the
                // user show page. Same reasoning as item.viewable /
                // target.viewable above.
                'viewable' => Gate::allows('view', $actionlog->adminuser),
            ] : null,
            'created_by' => ($actionlog->adminuser) ? [
                'id' => (int) $actionlog->adminuser->id,
                'name' => e($actionlog->adminuser->display_name),
                'first_name' => e($actionlog->adminuser->first_name),
                'last_name' => e($actionlog->adminuser->last_name),
                'viewable' => Gate::allows('view', $actionlog->adminuser),
            ] : null,
            'target' => ($actionlog->target) ? [
                'id' => (int) $actionlog->target->id,
                'name' => e($actionlog->target->display_name) ?? null,
                'type' => e($actionlog->targetType()),
                // See item.viewable above for shape reasoning.
                'viewable' => Gate::allows('view', $actionlog->target),
            ] : null,
            'quantity' => $this->getQuantity($actionlog),
            // action_logs.order_number was replaced by action_logs.order_item_id
            // pointing at the specific OrderItem line. The parent Order
            // is one hop away. Null for log rows with no OrderItem
            // attached (checkouts, edits, anything that isn't a
            // QuantityAdjust with an order supplied).
            'order_number' => $actionlog->orderItem?->order?->order_number
                ? e($actionlog->orderItem->order->order_number)
                : null,
            'note' => ($actionlog->note) ? Helper::parseEscapedMarkedownInline($actionlog->note) : null,
            'signature_file' => (($actionlog->accept_signature) && Storage::exists(FileStorage::Signatures->privateStorageKey().$actionlog->accept_signature)) ? route('log.signature.view', ['filename' => $actionlog->accept_signature]) : null,
            'log_meta' => $clean_meta ?? null,
            'remote_ip' => e($actionlog->remote_ip) ?? null,
            'user_agent' => e($actionlog->user_agent) ?? null,
            'action_source' => ($actionlog->action_source) ?? null,
            'action_date' => ($actionlog->action_date) ? Helper::getFormattedDateObject($actionlog->action_date, 'datetime') : Helper::getFormattedDateObject($actionlog->created_at, 'datetime'),
        ];

        // Expose audit-visible custom field values as top-level keys so the
        // audits datatable can bind each column directly to its field name.
        if ($actionlog->action_type === 'audit' && isset($meta_array->_audit_snapshot)) {
            foreach ($meta_array->_audit_snapshot as $fieldname => $value) {
                $field = $custom_fields->firstWhere('db_column', $fieldname);
                if ($field && $field->field_encrypted == '1') {
                    if (Gate::allows('assets.view.encrypted_custom_fields')) {
                        try {
                            $array[$fieldname] = e(Crypt::decryptString($value));
                        } catch (\Exception $e) {
                            $array[$fieldname] = '************';
                        }
                    } else {
                        $array[$fieldname] = '************';
                    }
                } else {
                    $array[$fieldname] = e($value);
                }
            }
        }

        return $array;
    }

    public function transformCheckedoutActionlog(Collection $accessories_checkout, $total)
    {

        $array = [];
        foreach ($accessories_checkout as $user) {
            $array[] = (new UsersTransformer)->transformUser($user);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    /**
     * This takes the ids of the changed attributes and returns the names instead for the history view of an Asset
     *
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

        if (array_key_exists('rtd_location_id', $clean_meta)) {

            $oldRtd = $location->find($clean_meta['rtd_location_id']['old']);
            $oldRtdName = $oldRtd ? e($oldRtd->name) : trans('general.deleted');

            $newRtd = $location->find($clean_meta['rtd_location_id']['new']);
            $newRtdName = $newRtd ? e($newRtd->name) : trans('general.deleted');

            $clean_meta['rtd_location_id']['old'] = $clean_meta['rtd_location_id']['old'] ? '[id: '.$clean_meta['rtd_location_id']['old'].'] '.$oldRtdName : '';
            $clean_meta['rtd_location_id']['new'] = $clean_meta['rtd_location_id']['new'] ? '[id: '.$clean_meta['rtd_location_id']['new'].'] '.$newRtdName : '';
            $clean_meta[trans('admin/hardware/form.default_location')] = $clean_meta['rtd_location_id'];
            unset($clean_meta['rtd_location_id']);
        }

        if (array_key_exists('location_id', $clean_meta)) {

            $oldLocation = $location->find($clean_meta['location_id']['old']);
            $oldLocationName = $oldLocation ? e($oldLocation->name) : trans('general.deleted');

            $newLocation = $location->find($clean_meta['location_id']['new']);
            $newLocationName = $newLocation ? e($newLocation->name) : trans('general.deleted');

            $clean_meta['location_id']['old'] = $clean_meta['location_id']['old'] ? '[id: '.$clean_meta['location_id']['old'].'] '.$oldLocationName : '';
            $clean_meta['location_id']['new'] = $clean_meta['location_id']['new'] ? '[id: '.$clean_meta['location_id']['new'].'] '.$newLocationName : '';
            $clean_meta[trans('admin/locations/message.current_location')] = $clean_meta['location_id'];
            unset($clean_meta['location_id']);
        }

        if (array_key_exists('model_id', $clean_meta)) {

            $oldModel = $model->find($clean_meta['model_id']['old']);
            $oldModelName = $oldModel ? e($oldModel->name) : trans('admin/models/message.deleted');

            $newModel = $model->find($clean_meta['model_id']['new']);
            $newModelName = $newModel ? e($newModel->name) : trans('admin/models/message.deleted');

            $clean_meta['model_id']['old'] = '[id: '.$clean_meta['model_id']['old'].'] '.$oldModelName;
            $clean_meta['model_id']['new'] = '[id: '.$clean_meta['model_id']['new'].'] '.$newModelName; /** model is required at asset creation */
            $clean_meta[trans('admin/hardware/form.model')] = $clean_meta['model_id'];
            unset($clean_meta['model_id']);
        }
        if (array_key_exists('company_id', $clean_meta)) {

            $oldCompany = $company->find($clean_meta['company_id']['old']);
            $oldCompanyName = $oldCompany ? e($oldCompany->name) : trans('admin/company/message.deleted');

            $newCompany = $company->find($clean_meta['company_id']['new']);
            $newCompanyName = $newCompany ? e($newCompany->name) : trans('admin/company/message.deleted');

            $clean_meta['company_id']['old'] = $clean_meta['company_id']['old'] ? '[id: '.$clean_meta['company_id']['old'].'] '.$oldCompanyName : trans('general.unassigned');
            $clean_meta['company_id']['new'] = $clean_meta['company_id']['new'] ? '[id: '.$clean_meta['company_id']['new'].'] '.$newCompanyName : trans('general.unassigned');
            $clean_meta[trans('general.company')] = $clean_meta['company_id'];
            unset($clean_meta['company_id']);
        }

        if (array_key_exists('companies', $clean_meta)) {
            // clean_field() JSON-encodes array values into a string (e.g. "[14,15]").
            // Decode them back to integer arrays before resolving names.
            // Use withoutGlobalScopes so FMCS does not hide companies from the log viewer.
            $resolveCompanyNames = function ($rawValue): string {
                $ids = json_decode($rawValue, true);
                if (empty($ids) || ! is_array($ids)) {
                    return trans('general.unassigned');
                }

                return collect($ids)
                    ->map(fn ($id) => Company::withoutGlobalScopes()->withTrashed()->find($id))
                    ->map(fn ($c) => $c ? e($c->name) : trans('general.deleted'))
                    ->join(', ');
            };

            $clean_meta['companies']['old'] = $resolveCompanyNames($clean_meta['companies']['old']);
            $clean_meta['companies']['new'] = $resolveCompanyNames($clean_meta['companies']['new']);
            $clean_meta[trans('general.companies')] = $clean_meta['companies'];
            unset($clean_meta['companies']);
        }
        if (array_key_exists('groups', $clean_meta)) {
            // groups meta is a list of {id, name} snapshots taken at
            // write time. The name is the load-bearing bit: it
            // preserves what the group was called at the moment the
            // change happened, so a later rename or delete doesn't
            // rewrite history. clean_field() ran e(json_encode()) on
            // the arrays, so we have to htmlspecialchars_decode the
            // JSON string before json_decode can read the escaped
            // quotes back. The companies handler above sidesteps this
            // because its ids are plain integers with no quoted
            // strings inside the JSON.
            $renderGroupSnapshot = function ($rawValue): string {
                $entries = json_decode(htmlspecialchars_decode((string) $rawValue, ENT_QUOTES), true);
                if (empty($entries) || ! is_array($entries)) {
                    return trans('general.unassigned');
                }

                return collect($entries)
                    ->map(fn ($entry) => is_array($entry) && isset($entry['name']) ? e($entry['name']) : trans('general.deleted'))
                    ->join(', ');
            };

            $clean_meta['groups']['old'] = $renderGroupSnapshot($clean_meta['groups']['old']);
            $clean_meta['groups']['new'] = $renderGroupSnapshot($clean_meta['groups']['new']);
            $clean_meta[trans('general.groups')] = $clean_meta['groups'];
            unset($clean_meta['groups']);
        }
        if (array_key_exists('supplier_id', $clean_meta)) {

            $oldSupplier = $supplier->find($clean_meta['supplier_id']['old']);
            $oldSupplierName = $oldSupplier ? e($oldSupplier->name) : trans('admin/suppliers/message.deleted');

            $newSupplier = $supplier->find($clean_meta['supplier_id']['new']);
            $newSupplierName = $newSupplier ? e($newSupplier->name) : trans('admin/suppliers/message.deleted');

            $clean_meta['supplier_id']['old'] = $clean_meta['supplier_id']['old'] ? '[id: '.$clean_meta['supplier_id']['old'].'] '.$oldSupplierName : trans('general.unassigned');
            $clean_meta['supplier_id']['new'] = $clean_meta['supplier_id']['new'] ? '[id: '.$clean_meta['supplier_id']['new'].'] '.$newSupplierName : trans('general.unassigned');
            $clean_meta[trans('general.supplier')] = $clean_meta['supplier_id'];
            unset($clean_meta['supplier_id']);
        }
        if (array_key_exists('status_id', $clean_meta)) {

            $oldStatus = $status->find($clean_meta['status_id']['old']);
            $oldStatusName = $oldStatus ? e($oldStatus->name) : trans('admin/statuslabels/message.deleted_label');

            $newStatus = $status->find($clean_meta['status_id']['new']);
            $newStatusName = $newStatus ? e($newStatus->name) : trans('admin/statuslabels/message.deleted_label');

            $clean_meta['status_id']['old'] = $clean_meta['status_id']['old'] ? '[id: '.$clean_meta['status_id']['old'].'] '.$oldStatusName : trans('general.unassigned');
            $clean_meta['status_id']['new'] = $clean_meta['status_id']['new'] ? '[id: '.$clean_meta['status_id']['new'].'] '.$newStatusName : trans('general.unassigned');
            $clean_meta[trans('general.status_label')] = $clean_meta['status_id'];
            unset($clean_meta['status_id']);
        }
        if (array_key_exists('asset_eol_date', $clean_meta)) {
            $clean_meta[trans('admin/hardware/form.eol_date')] = $clean_meta['asset_eol_date'];
            unset($clean_meta['asset_eol_date']);
        }

        return $clean_meta;

    }

    private function getQuantity(Actionlog $actionlog): ?int
    {
        if (! $actionlog->quantity) {
            return null;
        }

        // only a few action types will have a quantity we are interested in.
        if (! in_array($actionlog->action_type, [
            ActionType::Checkout->value,
            ActionType::Accepted->value,
            ActionType::Declined->value,
            ActionType::CheckinFrom->value,
            ActionType::AddSeats->value,
            ActionType::DeleteSeats->value,
            ActionType::QuantityAdjust->value,
            ActionType::Create->value,
        ])) {
            return null;
        }

        return (int) $actionlog->quantity;
    }
}
