<?php

namespace App\Livewire;

use App\Models\CustomField;
use App\Models\Import;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Importer extends Component
{
    public $progress = -1; //upload progress - '-1' means don't show
    public $progress_message;
    public $progress_bar_class = 'progress-bar-warning';

    public $message; //status/error message?
    public $message_type; //success/error?

    //originally from ImporterFile
    public $import_errors; //
    public $activeFileId;
    public $headerRow = [];
    public $typeOfImport;
    public $importTypes;
    public $columnOptions;
    public $statusType;
    public $statusText;
    public $update;
    public $send_welcome;
    public $run_backup;
    public $field_map; // we need a separate variable for the field-mapping, because the keys in the normal array are too complicated for Livewire to understand

    // Make these variables public - we set the properties in the constructor so we can localize them (versus the old static arrays)
    public $accessories_fields;
    public $assets_fields;
    public $users_fields;
    public $assetmodels_fields;
    public $suppliers_fields;
    public $licenses_fields;
    public $locations_fields;
    public $consumables_fields;
    public $components_fields;
    public $manufacturers_fields;
    public $categories_fields;
    public $aliases_fields;

    protected $rules = [
        'files.*.file_path' => 'required|string',
        'files.*.created_at' => 'required|string',
        'files.*.filesize' => 'required|integer',
        'headerRow' => 'array',
        'typeOfImport' => 'string',
        'field_map' => 'array'
    ];

    /**
     * This is used in resources/views/livewire.importer.blade.php, and we kinda shouldn't need to check for
     * activeFile here, but there's some UI goofiness that allows this to crash out on some imports.
     *
     * @return string
     */
    public function generate_field_map()
    {
        $tmp = array();
        if ($this->activeFile) {
            $tmp = array_combine($this->headerRow, $this->field_map);
            $tmp = array_filter($tmp);
        }
        return json_encode($tmp);

    }

    private function getColumns($type)
    {
        switch ($type) {
            case 'asset':
                $results = $this->assets_fields;
                break;
            case 'assetModel':
                $results = $this->assetmodels_fields;
                break;
            case 'accessory':
                $results = $this->accessories_fields;
                break;
            case 'consumable':
                $results = $this->consumables_fields;
                break;
            case 'component':
                $results = $this->components_fields;
                break;
            case 'license':
                $results = $this->licenses_fields;
                break;
            case 'user':
                $results = $this->users_fields;
                break;
            case 'location':
                $results = $this->locations_fields;
                break;
            case 'supplier':
                $results = $this->suppliers_fields;
                break;
            case 'manufacturer':
                $results = $this->manufacturers_fields;
                break;
            case 'category':
                $results = $this->categories_fields;
                break;
            default:
                $results = [];
        }
        asort($results, SORT_FLAG_CASE | SORT_STRING);

        if ($type == "asset") {
            // add Custom Fields after a horizontal line
            $results['-'] = "———" . trans('admin/custom_fields/general.custom_fields') . "———’";
            foreach (CustomField::orderBy('name')->get() as $field) {
                $results[$field->db_column_name()] = $field->name;
            }
        }
        return $results;
    }

    public function updatingTypeOfImport($type)
    {

        // go through each header, find a matching field to try and map it to.
        foreach ($this->headerRow as $i => $header) {
            // do we have something mapped already?
            if (array_key_exists($i, $this->field_map)) {
                // yes, we do. Is it valid for this type of import?
                // (e.g. the import type might have been changed...?)
                if (array_key_exists($this->field_map[$i], $this->columnOptions[$type])) {
                    //yes, this key *is* valid. Continue on to the next field.
                    continue;
                } else {
                    //no, this key is *INVALID* for this import type. Better set it to null,
                    // and we'll hope that the $aliases_fields or something else picks it up.
                    $this->field_map[$i] = null; // fingers crossed! But it's not likely, tbh.
                } // TODO - strictly speaking, this isn't necessary here I don't think.
            }
            // first, check for exact matches
            foreach ($this->columnOptions[$type] as $v => $text) {
                if (strcasecmp($text, $header) === 0) { // case-INSENSITIVe on purpose!
                    $this->field_map[$i] = $v;
                    continue 2; //don't bother with the alias check, go to the next header
                }
            }
            // if you got here, we didn't find a match. Try the $aliases_fields
            foreach ($this->aliases_fields as $key => $alias_values) {
                foreach ($alias_values as $alias_value) {
                    if (strcasecmp($alias_value, $header) === 0) { // aLsO CaSe-INSENSitiVE!
                        // Make *absolutely* sure that this key actually _exists_ in this import type -
                        // you can trigger this by importing accessories with a 'Warranty' column (which don't exist
                        // in "Accessories"!)
                        if (array_key_exists($key, $this->columnOptions[$type])) {
                            $this->field_map[$i] = $key;
                            continue 3; // bust out of both of these loops and the surrounding one - e.g. move on to the next header
                        }
                    }
                }
            }
            // and if you got here, we got nothing. Let's recommend 'null'
            $this->field_map[$i] = null; // Booooo :(
        }
    }

    public function mount()
    {
        $this->authorize('import');
        $this->importTypes = [
            'accessory'     =>  trans('general.accessories'),
            'asset'         =>      trans('general.assets'),
            'assetModel'    =>      trans('general.asset_models'),
            'component'     =>  trans('general.components'),
            'consumable'    => trans('general.consumables'),
            'license'       =>    trans('general.licenses'),
            'location'      =>   trans('general.locations'),
            'user'          =>       trans('general.users'),
            'supplier'      =>       trans('general.suppliers'),
            'manufacturer'  =>       trans('general.manufacturers'),
            'category'      =>       trans('general.categories'),
        ];

        /**
         * These are the item-type specific columns
         */
        $this->accessories_fields = [
            'category' => trans('general.category'),
            'company' => trans('general.company'),
            'item_name' => trans('general.item_name_var', ['item' => trans('general.accessory')]),
            'location' => trans('general.location'),
            'manufacturer' => trans('general.manufacturer'),
            'min_amt' => trans('mail.min_QTY'),
            'model_number' => trans('general.model_no'),
            'notes' => trans('general.notes'),
            'order_number' => trans('general.order_number'),
            'purchase_cost' => trans('general.purchase_cost'),
            'purchase_date' => trans('general.purchase_date'),
            'quantity' => trans('general.qty'),
            'supplier' => trans('general.supplier'),
        ];

        $this->assets_fields = [
            'id' => trans('general.id'),
            'asset_eol_date' => trans('admin/hardware/form.eol_date'),
            'asset_model' => trans('general.model_name'),
            'asset_notes' => trans('general.item_notes', ['item' => trans('admin/hardware/general.asset')]),
            'asset_tag' => trans('general.asset_tag'),
            'byod' => trans('general.byod'),
            'category' => trans('general.category'),
            'company' => trans('general.company'),
            'image' => trans('general.importer.image_filename'),
            'item_name' => trans('general.item_name_var', ['item' => trans('general.asset')]),
            'location' => trans('general.location'),
            'manufacturer' => trans('general.manufacturer'),
            'model_notes' => trans('general.item_notes', ['item' => trans('admin/hardware/form.model')]),
            'model_number' => trans('general.model_no'),
            'order_number' => trans('general.order_number'),
            'purchase_cost' => trans('general.purchase_cost'),
            'purchase_date' => trans('general.purchase_date'),
            'requestable' => trans('admin/hardware/general.requestable'),
            'serial' => trans('general.serial_number'),
            'status' => trans('general.status'),
            'supplier' => trans('general.supplier'),
            'warranty_months' => trans('admin/hardware/form.warranty'),
            /**
             * Checkout fields:
             * Assets can be checked out to other assets, people, or locations, but we currently
             * only support checkout to people and locations in the importer
             **/
            'checkout_class' => trans('general.importer.checkout_type'),
            'first_name' => trans('general.importer.checked_out_to_first_name'),
            'last_name' => trans('general.importer.checked_out_to_last_name'),
            'full_name' => trans('general.importer.checked_out_to_fullname'),
            'email' => trans('general.importer.checked_out_to_email'),
            'username' => trans('general.importer.checked_out_to_username'),
            'checkout_location' => trans('general.importer.checkout_location'),
            /**
             * These are here so users can import history, to replace the dinosaur that
             * was the history importer
             */
            'last_checkin' => trans('admin/hardware/table.last_checkin_date'),
            'last_checkout' => trans('admin/hardware/table.checkout_date'),
            'expected_checkin' => trans('admin/hardware/form.expected_checkin'),
            'last_audit_date' => trans('general.last_audit'),
            'next_audit_date' => trans('general.next_audit_date'),
        ];

        $this->consumables_fields = [
            'category' => trans('general.category'),
            'checkout_class' => trans('general.importer.checkout_type'),
            'company' => trans('general.company'),
            'item_name' => trans('general.item_name_var', ['item' => trans('general.consumable')]),
            'item_no' => trans('admin/consumables/general.item_no'),
            'location' => trans('general.location'),
            'manufacturer' => trans('general.manufacturer'),
            'min_amt' => trans('general.min_amt'),
            'model_number' => trans('general.model_no'),
            'notes' => trans('general.notes'),
            'order_number' => trans('general.order_number'),
            'purchase_cost' => trans('general.purchase_cost'),
            'purchase_date' => trans('general.purchase_date'),
            'quantity' => trans('general.qty'),
            'supplier' => trans('general.supplier'),
        ];

        $this->components_fields = [
            'category' => trans('general.category'),
            'company' => trans('general.company'),
            'item_name' => trans('general.item_name_var', ['item' => trans('general.component')]),
            'location' => trans('general.location'),
            'manufacturer' => trans('general.manufacturer'),
            'min_amt' => trans('mail.min_QTY'),
            'model_number' => trans('general.model_no'),
            'notes' => trans('general.notes'),
            'order_number' => trans('general.order_number'),
            'purchase_cost' => trans('general.purchase_cost'),
            'purchase_date' => trans('general.purchase_date'),
            'quantity' => trans('general.qty'),
            'serial' => trans('general.serial_number'),
            'supplier' => trans('general.supplier'),
        ];

        $this->licenses_fields = [
            'asset_tag' => trans('general.importer.checked_out_to_tag'),
            'category' => trans('general.category'),
            'checkout_class' => trans('general.importer.checkout_type'),
            'company' => trans('general.company'),
            'email' => trans('general.importer.checked_out_to_email'),
            'expiration_date' => trans('admin/licenses/form.expiration'),
            'full_name' => trans('general.importer.checked_out_to_fullname'),
            'item_name' => trans('general.item_name_var', ['item' => trans('general.license')]),
            'license_email' => trans('admin/licenses/form.to_email'),
            'license_name' => trans('admin/licenses/form.to_name'),
            'location' => trans('general.location'),
            'maintained' => trans('admin/licenses/form.maintained'),
            'manufacturer' => trans('general.manufacturer'),
            'min_amt' => trans('general.min_amt'),
            'notes' => trans('general.notes'),
            'order_number' => trans('general.order_number'),
            'purchase_cost' => trans('general.purchase_cost'),
            'purchase_date' => trans('general.purchase_date'),
            'purchase_order' => trans('admin/licenses/form.purchase_order'),
            'reassignable' => trans('admin/licenses/form.reassignable'),
            'seats' => trans('admin/licenses/form.seats'),
            'serial' => trans('general.license_serial'),
            'supplier' => trans('general.supplier'),
            'termination_date' => trans('admin/licenses/form.termination_date'),
            'username' => trans('general.importer.checked_out_to_username'),
        ];

        $this->users_fields = [
            'id' => trans('general.id'),
            'activated' => trans('general.activated'),
            'address' => trans('general.address'),
            'avatar' => trans('general.image'),
            'city' => trans('general.city'),
            'company' => trans('general.company'),
            'country' => trans('general.country'),
            'department' => trans('general.department'),
            'email' => trans('admin/users/table.email'),
            'employee_num' => trans('general.employee_number'),
            'end_date' => trans('general.end_date'),
            'first_name' => trans('general.first_name'),
            'gravatar' => trans('general.importer.gravatar'),
            'jobtitle' => trans('admin/users/table.title'),
            'last_name' => trans('general.last_name'),
            'location' => trans('general.location'),
            'manager_first_name' => trans('general.importer.manager_first_name'),
            'manager_last_name' => trans('general.importer.manager_last_name'),
            'manager_employee_num' => trans('general.importer.manager_employee_num'),
            'manager_username' => trans('general.importer.manager_username'),
            'notes' => trans('general.notes'),
            'phone_number' => trans('admin/users/table.phone'),
            'mobile_number' => trans('admin/users/table.mobile'),
            'remote' => trans('admin/users/general.remote'),
            'start_date' => trans('general.start_date'),
            'state' => trans('general.state'),
            'username' => trans('admin/users/table.username'),
            'display_name' => trans('admin/users/table.display_name'),
            'vip' => trans('general.importer.vip'),
            'website' => trans('general.website'),
            'zip' => trans('general.zip'),
        ];

        $this->locations_fields = [
            'id' => trans('general.id'),
            'name' => trans('general.name'),
            'address' => trans('general.address'),
            'address2' => trans('general.importer.address2'),
            'city' => trans('general.city'),
            'country' => trans('general.country'),
            'currency' => trans('general.importer.currency'),
            'ldap_ou' => trans('admin/locations/table.ldap_ou'),
            'manager' => trans('general.importer.manager_full_name'),
            'manager_username' => trans('general.importer.manager_username'),
            'notes' => trans('general.notes'),
            'parent_location' => trans('admin/locations/table.parent'),
            'state' => trans('general.state'),
            'zip' => trans('general.zip'),
        ];

        $this->suppliers_fields = [
            'id' => trans('general.id'),
            'name' => trans('general.name'),
            'address' => trans('general.address'),
            'address2' => trans('general.importer.address2'),
            'city' => trans('general.city'),
            'notes' => trans('general.notes'),
            'state' => trans('general.state'),
            'zip' => trans('general.zip'),
            'phone' => trans('general.phone'),
            'fax' => trans('general.fax'),
            'url' => trans('general.url'),
            'contact' => trans('general.contact'),
            'email' => trans('general.email'),
        ];

        $this->manufacturers_fields = [
            'id' => trans('general.id'),
            'name' => trans('general.name'),
            'notes' => trans('general.notes'),
            'support_phone' =>  trans('admin/manufacturers/table.support_phone'),
            'support_url' =>  trans('admin/manufacturers/table.support_url'),
            'support_email' =>  trans('admin/manufacturers/table.support_email'),
            'warranty_lookup_url' =>  trans('admin/manufacturers/table.warranty_lookup_url'),
            'url' =>  trans('general.url'),
        ];

        $this->categories_fields = [
            'id' => trans('general.id'),
            'name' => trans('general.name'),
            'notes' => trans('general.notes'),
            'category_type' => trans('admin/categories/general.import_category_type'),
            'eula_text' => trans('admin/categories/general.import_eula_text'),
            'use_default_eula' => trans('admin/categories/general.use_default_eula_column'),
            'require_acceptance' => trans('admin/categories/general.import_require_acceptance'),
            'checkin_email' => trans('admin/categories/general.import_checkin_email'),
        ];



        $this->assetmodels_fields  = [
            'id' => trans('general.id'),
            'category' => trans('general.category'),
            'eol' => trans('general.eol'),
            'fieldset' => trans('admin/models/general.fieldset'),
            'item_name' => trans('general.item_name_var', ['item' => trans('general.asset_model')]),
            'manufacturer' => trans('general.manufacturer'),
            'min_amt' => trans('mail.min_QTY'),
            'model_number' => trans('general.model_no'),
            'notes' => trans('general.item_notes', ['item' => trans('admin/hardware/form.model')]),
            'requestable' => trans('admin/models/general.requestable'),
            'require_serial' => trans('admin/hardware/general.require_serial'),

        ];

        // "real fieldnames" to a list of aliases for that field
        $this->aliases_fields = [
            'item_name' =>
                [
                    'item name',
                    'asset name',
                    'accessory name',
                    'user name',
                    'consumable name',
                    'component name',
                    'name',
                    'supplier name',
                    'location name',
                ],
            'item_no' => [
                'item number',
                'item no.',
                'item #',
            ],
            'asset_model' =>
                [
                    'model name',
                    'model',
                ],
            'eol_date' =>
                [
                    'eol',
                    'eol date',
                    'asset eol date',
                ],
            'eol' =>
                [
                    'eol',
                    'EOL',
                    'eol months',
                ],
            'depreciation' =>
                [
                    'Depreciation',
                    'depreciation',
                ],
            'requestable' =>
                [
                    'requestable',
                    'Requestable',
                ],
            'gravatar' =>
                [
                    'gravatar',
                ],
            'currency' =>
                [
                    '$',
                ],
            'jobtitle' =>
                [
                    'job title for user',
                    'job title',
                ],
            'full_name' =>
                [
                    'full name',
                    'fullname',
                    trans('general.importer.checked_out_to_fullname')
                ],
            'username' =>
                [
                    'user name',
                    'username',
                    trans('general.importer.checked_out_to_username'),
                ],
            'display_name' =>
                [
                    'display name',
                    'displayName',
                    'display',
                    trans('admin/users/table.display_name'),
                ],
            'first_name' =>
                [
                    'first name',
                    trans('general.importer.checked_out_to_first_name'),
                ],
            'last_name' =>
                [
                    'last name',
                    'lastname',
                    trans('general.importer.checked_out_to_last_name'),
                ],
            'email' =>
                [
                    'email',
                    'e-mail',
                    trans('general.importer.checked_out_to_email'),
                ],
            'phone_number' =>
                [
                    'phone',
                    'phone number',
                    'phone num',
                    'telephone number',
                    'telephone',
                    'tel.',
                ],
            'mobile_number' =>
                [
                    'mobile',
                    'mobile number',
                    'cell',
                    'cellphone',
                ],

            'serial' =>
                [
                    'serial number',
                    'serial no.',
                    'serial no',
                    'product key',
                    'key',
                ],
            'require_serial' =>
                [
                    'serial required',
                ],
            'model_number' =>
                [
                    'model',
                    'model no',
                    'model no.',
                    'model number',
                    'model num',
                    'model num.'
                ],
            'warranty_months' =>
                [
                    'Warranty',
                    'Warranty Months'
                ],
            'qty' =>
                [
                    'QTY',
                    'Quantity'
                ],
            'zip' =>
                [
                    'Postal Code',
                    'Post Code',
                    'Zip Code'
                ],
            'min_amt' =>
                [
                    'Min Amount',
                    'Minimum Amount',
                    'Min Quantity',
                    'Minimum Quantity',
                ],
            'next_audit_date' =>
                [
                    'Next Audit',
                ],
            'last_checkout' =>
                [
                    'Last Checkout',
                    'Last Checkout Date',
                    'Checkout Date',
                ],
            'address2' =>
                [
                    'Address 2',
                    'Address2',
                ],
            'ldap_ou' =>
                [
                    'LDAP OU',
                    'OU',
                ],
            'parent_location' =>
                [
                    'Parent',
                    'Parent Location',
                ],
            'manager' =>
                [
                    'Managed By',
                    'Manager Name',
                    'Manager Full Name',
                ],
            'manager_username' =>
                [
                    'Manager Username',
                ],
        ];

        $this->columnOptions[''] = $this->getColumns(''); //blank mode? I don't know what this is supposed to mean
        foreach ($this->importTypes as $type => $name) {
            $this->columnOptions[$type] = $this->getColumns($type);
        }
    }

    public function selectFile($id)
    {
        $this->clearMessage();

        $this->activeFileId = $id;

        if (!$this->activeFile) {
            $this->message = trans('admin/hardware/message.import.file_missing');
            $this->message_type = 'danger';
            return;
        }

        $this->headerRow = $this->activeFile->header_row;
        $this->typeOfImport = $this->activeFile->import_type;

        $this->field_map = null;
        foreach ($this->headerRow as $element) {
            if (isset($this->activeFile->field_map[$element])) {
                $this->field_map[] = $this->activeFile->field_map[$element];
            } else {
                $this->field_map[] = null; // re-inject the 'nulls' if a file was imported with some 'Do Not Import' settings
            }
        }

        $this->file_id = $id;
        $this->import_errors = null;
        $this->statusText = null;

    }

    public function destroy($id)
    {
        $this->authorize('import');

        $import = Import::find($id);

        // Check that the import wasn't deleted after while page was already loaded...
        // @todo: next up...handle the file being missing for other interactions...
        // for example having an import open in two tabs, deleting it, and then changing
        // the import type in the other tab. The error message below wouldn't display in that case.
        if (!$import) {
            $this->message = trans('admin/hardware/message.import.file_already_deleted');
            $this->message_type = 'danger';

            return;
        }

        if (Storage::delete('private_uploads/imports/' . $import->file_path)) {
            $import->delete();
            $this->message = trans('admin/hardware/message.import.file_delete_success');
            $this->message_type = 'success';

            unset($this->files);

            return;
        }

        $this->message = trans('admin/hardware/message.import.file_delete_error');
        $this->message_type = 'danger';
    }

    public function clearMessage()
    {
        $this->message = null;
        $this->message_type = null;
    }

    #[Computed]
    public function files()
    {
        return Import::orderBy('id', 'desc')->get();
    }

    #[Computed]
    public function activeFile()
    {
        return Import::find($this->activeFileId);
    }

    public function render()
    {
        return view('livewire.importer')
            ->extends('layouts.default')
            ->section('content');
    }
}
