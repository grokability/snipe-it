<?php

namespace App\Livewire;

use App\Enums\FileStorage;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
// Snipe-IT's Component model name clashes with Livewire\Component - alias it.
use App\Models\Component as ComponentModel;
use App\Models\Consumable;
use App\Models\Contracts\Importable;
use App\Models\CustomField;
use App\Models\Import;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Traits\LocksLongRunningOperation;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Importer extends Component
{
    use LocksLongRunningOperation;
    use WithPagination;

    // How many import files to show per page. Not currently user-configurable;
    // twenty-five is enough to avoid pagination for most installs but small
    // enough to keep the page manageable when someone's uploaded hundreds.
    public $perPage = 25;

    // IDs the caller has checkbox-ticked on the current page. Cleared on page
    // change (see updatedPage) so "Select All" is naturally per-page.
    public $selectedIds = [];

    // Header select-all checkbox state. Purely visual — the source of truth
    // for which files get bulk-deleted is $selectedIds. Kept in sync via the
    // updatedSelectAll hook.
    public $selectAll = false;

    public $progress = -1; // upload progress - '-1' means don't show

    public $progress_message;

    public $progress_bar_class = 'progress-bar-warning';

    public $message; // status/error message?

    public $message_type; // success/error?

    // originally from ImporterFile
    public $import_errors; //

    public $activeFileId;

    // Count of data rows in the active file's CSV (excluding the header
    // row). Populated in selectFile() and surfaced in the mapping modal so
    // the user knows how much data they're about to process.
    public $activeFileRowCount = 0;

    // Wizard cursor. 1 = pick type + options, 2 = map columns, 3 = preview
    // + Process. Reset to 1 whenever a new file is selected so revisiting
    // the modal always starts at the beginning.
    public $wizardStep = 1;

    // First N data rows (post-header) from the active CSV, rendered on
    // step 3 so the user can eyeball how their mapping lands before the
    // real import commits. Bounded by PREVIEW_ROW_LIMIT.
    public $previewRows = [];

    // Set to true once the user clicks Process in step 3 - flips the
    // preview area over to a processing progress bar so the user has
    // real feedback during the slice loop.
    public $processing = false;

    // Whether the current processing run requested a backup (from the
    // step-1 checkbox). When true, the processing panel renders a
    // dedicated backup progress bar above the import bar so the two
    // phases (backup, then per-slice import) are visually distinct.
    public $backupRequested = false;

    // Flips true after slice 0 completes - by which point the sync
    // backup has finished on the server side, per
    // Api\ImportController::process(). Used to switch the backup bar
    // from active-warning to success-green.
    public $backupComplete = false;

    public const PREVIEW_ROW_LIMIT = 10;

    public $headerRow = [];

    public $typeOfImport;

    public $importTypes;

    public $columnOptions;

    public $statusType;

    public $statusText;

    public $update;

    // When true, blank CSV cells during an update pass are ignored (the DB
    // value stays put). Default false matches the legacy behavior: a
    // present-but-empty cell clears the corresponding DB column. Only the
    // update flow reads it. New-row inserts ignore it entirely.
    public $preserve_blanks = false;

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

    public $assethistory_fields;

    public $aliases_fields;

    // Bridge from importer target-field keys to (Model class, model
    // attribute) so the required-field list can be derived from each
    // model's own `$rules` array instead of duplicated here. When a
    // maintainer adds a `required` rule to Asset::$rules['name'], the
    // wizard's step-2 gate picks it up automatically.
    //
    // assetHistory sits outside this convention because it doesn't create
    // records: its "required" columns are enforced by the AssetHistory-
    // Importer's own row-processing (asset_tag / full_name / checkout_date
    // are needed to look up the target and stamp the actionlog). Those
    // stay hardcoded in requiredForType() below.
    // The importer target-field key on the LEFT of each translation
    // pair must match a key that actually appears in the corresponding
    // {type}_fields array below (that's what the mapping dropdowns use
    // as option values). The RIGHT side is the model attribute the rule
    // check reads from Model::$rules. Assets / accessories / consumables
    // / components / licenses use "item_name" for their name column;
    // asset models / locations / suppliers / manufacturers / categories
    // use "name" directly.
    // requiredForType() cross-checks each class string against the
    // Importable marker interface before `new`-ing. Defense in depth
    // against future Livewire regressions that might bypass Locked.
    #[Locked]
    public $required_field_model_map = [
        'asset' => [Asset::class, ['item_name' => 'name']],
        'assetModel' => [AssetModel::class, ['name' => 'name']],
        'accessory' => [Accessory::class, ['item_name' => 'name', 'category' => 'category_id']],
        'consumable' => [Consumable::class, ['item_name' => 'name', 'category' => 'category_id']],
        'component' => [ComponentModel::class, ['item_name' => 'name', 'category' => 'category_id']],
        // license: only item_name is enforced at wizard level. `seats` is
        // the license's total capacity count (not a per-seat pivot id).
        // License imports currently drive three different intents depending
        // on the row shape: (1) create a new License with N seats,
        // (2) update an existing License's capacity, (3) assign a user or
        // asset to one of an existing License's free seats. The importer
        // figures out which path each row takes at process time. seats is
        // only needed for (1) and (2), (3) doesn't reference it. Since the
        // wizard can't know per-row which path a caller intends, we don't
        // enforce seats at the wizard level. Server-side validation still
        // enforces `seats` on the create path per License::$rules. See
        // issue #19467.
        'license' => [License::class, ['item_name' => 'name']],
        'user' => [User::class, ['first_name' => 'first_name', 'username' => 'username']],
        'location' => [Location::class, ['name' => 'name']],
        'supplier' => [Supplier::class, ['name' => 'name']],
        'manufacturer' => [Manufacturer::class, ['name' => 'name']],
        'category' => [Category::class, ['name' => 'name']],
    ];

    public $match_username = false;

    public $match_email = false;

    public $match_firstnamelastname = false;

    public $match_flastname = false;

    public $match_firstname = false;

    protected $rules = [
        'files.*.file_path' => 'required|string',
        'files.*.created_at' => 'required|string',
        'files.*.filesize' => 'required|integer',
        'headerRow' => 'array',
        'typeOfImport' => 'string',
        'field_map' => 'array',
    ];

    /**
     * This is used in resources/views/livewire.importer.blade.php, and we kinda shouldn't need to check for
     * activeFile here, but there's some UI goofiness that allows this to crash out on some imports.
     *
     * @return string
     */
    public function generate_field_map()
    {
        $tmp = [];
        if ($this->activeFile) {
            $tmp = array_combine($this->headerRow, $this->field_map);
            // Drop only nulls (columns the auto-map couldn't bind to
            // anything for this import type). Preserve empty strings,
            // which encode the user's explicit "Do not import" choice
            // in the wizard select. Bare array_filter($tmp) treats both
            // as falsy and silently loses the user selection, forcing
            // them to re-set "Do not import" every time the template
            // is reloaded (see the wizard-side companion fix for #19450).
            $tmp = array_filter($tmp, fn ($v) => $v !== null);
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
            case 'assetHistory':
                $results = $this->assethistory_fields;
                break;
            default:
                $results = [];
        }
        asort($results, SORT_FLAG_CASE | SORT_STRING);

        if ($type == 'asset') {
            // add Custom Fields after a horizontal line
            $results['-'] = '———'.trans('admin/custom_fields/general.custom_fields').'———’';
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
            // Normalize once at the top so both exact-match and alias
            // branches see a trimmed header, and so an under_score CSV
            // column ("asset_tag") can still match a spaced target
            // label ("Asset Tag"). Comparison also swaps underscores
            // for spaces both directions.
            $header = trim((string) $header);
            $normalizedHeader = str_replace('_', ' ', $header);
            // do we have something mapped already?
            if (array_key_exists($i, $this->field_map)) {
                // yes, we do. Is it valid for this type of import?
                // (e.g. the import type might have been changed...?)
                if (array_key_exists($this->field_map[$i], $this->columnOptions[$type])) {
                    // yes, this key *is* valid. Continue on to the next field.
                    continue;
                } else {
                    // no, this key is *INVALID* for this import type. Better set it to null,
                    // and we'll hope that the $aliases_fields or something else picks it up.
                    $this->field_map[$i] = null; // fingers crossed! But it's not likely, tbh.
                } // TODO - strictly speaking, this isn't necessary here I don't think.
            }
            // first, check for exact matches - both against the raw header
            // and against the underscore-normalized form, and also against
            // the target-field KEY (so "asset_tag" as a CSV column matches
            // the asset_tag target even though its label is "Asset Tag").
            foreach ($this->columnOptions[$type] as $v => $text) {
                $textStr = trim((string) $text);
                if (
                    strcasecmp($textStr, $header) === 0
                    || strcasecmp($textStr, $normalizedHeader) === 0
                    || strcasecmp($v, $header) === 0
                    || strcasecmp(str_replace('_', ' ', (string) $v), $normalizedHeader) === 0
                ) {
                    $this->field_map[$i] = $v;

                    continue 2; // don't bother with the alias check, go to the next header
                }
            }
            // if you got here, we didn't find a match. Try the $aliases_fields
            foreach ($this->aliases_fields as $key => $alias_values) {
                foreach ($alias_values as $alias_value) {
                    $key = trim($key);
                    if (strcasecmp($alias_value, $header) === 0 || strcasecmp($alias_value, $normalizedHeader) === 0) {
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

    public function boot(): void
    {
        $this->authorize('import');
    }

    public function mount()
    {
        $this->refreshExistingImportFiles();
        $this->importTypes = [
            'accessory' => trans('general.accessories'),
            'asset' => trans('general.assets'),
            'assetHistory' => trans('general.assets').' - '.trans('general.import-history'),
            'assetModel' => trans('general.asset_models'),
            'component' => trans('general.components'),
            'consumable' => trans('general.consumables'),
            'license' => trans('general.licenses'),
            'location' => trans('general.locations'),
            'user' => trans('general.users'),
            'supplier' => trans('general.suppliers'),
            'manufacturer' => trans('general.manufacturers'),
            'category' => trans('general.categories'),
        ];

        /**
         * These are the item-type specific columns
         */
        $this->accessories_fields = [
            'archived' => trans('general.archived'),
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
            'requestable' => trans('general.requestable'),
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
            'default_location' => trans('admin/hardware/form.default_location'),
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
             * Checkout fields. Assets can be checked out to other assets, people, or
             * locations. Which target the importer picks is inferred from which of the
             * three target-shape columns has a value on the row (checkout_asset,
             * checkout_location, or the user-identity columns). checkout_class stays
             * available as an explicit override when a row has more than one populated
             * and needs disambiguation, or for backward-compat with pre-inference CSVs.
             **/
            'checkout_class' => trans('general.importer.checkout_type'),
            'first_name' => trans('general.importer.checked_out_to_first_name'),
            'last_name' => trans('general.importer.checked_out_to_last_name'),
            'full_name' => trans('general.importer.checked_out_to_fullname'),
            'email' => trans('general.importer.checked_out_to_email'),
            'username' => trans('general.importer.checked_out_to_username'),
            'checkout_location' => trans('general.importer.checkout_location'),
            'checkout_asset' => trans('general.importer.checkout_asset'),
            'checkout_user' => trans('general.importer.checkout_user'),
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
            // Internal key MUST be 'quantity' to match ItemImporter::handle(),
            // which does findCsvMatch($row, 'quantity'). A previous rename to
            // 'qty' silently broke consumable/component imports (issue #19312).
            'quantity' => trans('general.qty'),
            'requestable' => trans('general.requestable'),
            'supplier' => trans('general.supplier'),
        ];

        $this->components_fields = [
            'archived' => trans('general.archived'),
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
            // Internal key MUST be 'quantity' (see consumables_fields note above).
            'quantity' => trans('general.qty'),
            'requestable' => trans('general.requestable'),
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
            'company' => trans('general.company'),
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
            'tag_color' => trans('general.tag_color'),
        ];

        $this->suppliers_fields = [
            'id' => trans('general.id'),
            'name' => trans('general.name'),
            'address' => trans('general.address'),
            'address2' => trans('general.importer.address2'),
            'city' => trans('general.city'),
            'notes' => trans('general.notes'),
            'state' => trans('general.state'),
            'country' => trans('general.country'),
            'zip' => trans('general.zip'),
            'phone' => trans('general.phone'),
            'fax' => trans('general.fax'),
            'url' => trans('general.url'),
            'contact' => trans('general.contact'),
            'email' => trans('general.email'),
            'tag_color' => trans('general.tag_color'),
        ];

        $this->manufacturers_fields = [
            'id' => trans('general.id'),
            'name' => trans('general.name'),
            'notes' => trans('general.notes'),
            'support_phone' => trans('admin/manufacturers/table.support_phone'),
            'support_url' => trans('admin/manufacturers/table.support_url'),
            'support_email' => trans('admin/manufacturers/table.support_email'),
            'warranty_lookup_url' => trans('admin/manufacturers/table.warranty_lookup_url'),
            'url' => trans('general.url'),
            'tag_color' => trans('general.tag_color'),
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
            'alert_on_response' => trans('admin/categories/general.import_alert_on_response'),
            'tag_color' => trans('general.tag_color'),
        ];

        $this->assetmodels_fields = [
            'id' => trans('general.id'),
            'category' => trans('general.category'),
            'eol' => trans('general.eol'),
            'fieldset' => trans('admin/models/general.fieldset'),
            'name' => trans('general.name'),
            'manufacturer' => trans('general.manufacturer'),
            'min_amt' => trans('mail.min_QTY'),
            'model_number' => trans('general.model_no'),
            'notes' => trans('general.notes'),
            'requestable' => trans('general.requestable'),
            'require_serial' => trans('admin/hardware/general.require_serial'),
            'tag_color' => trans('general.tag_color'),
            'depreciation' => trans('general.depreciation'),
        ];

        $this->assethistory_fields = [
            'asset_tag' => trans('admin/hardware/table.asset_tag'),
            'full_name' => trans('general.name'),
            'email' => trans('general.email'),
            'checkout_date' => trans('admin/hardware/table.checkout_date'),
            'checkin_date' => trans('admin/hardware/form.checkin_date'),
            // Optional. Values "user" or "location" (case-insensitive)
            // disambiguate what Name should resolve to. Absent or empty
            // falls back to user for CSVs authored before the location
            // branch existed.
            'target_type' => trans('general.importer.checkout_type'),
            // Optional per-row note that lands on the checkout
            // actionlog. Legacy systems often carried a per-checkout
            // narrative ("student damaged screen", "shipped to remote
            // site"), and the column lets that history migrate through.
            'notes' => trans('general.notes'),
        ];

        /**
         * These are the "real fieldnames" with a list of possible aliases,
         * like misspellings, slight mis-phrasings, user-specific language, etc. that
         * could be in the imported file header.
         * This just makes the user's experience a little better when they're using
         * their own CSV template.
         */
        $this->aliases_fields = [
            'item_name' => [
                'item name',
                'asset name',
                'model name',
                'asset model name',
                'accessory name',
                'user name',
                'consumable name',
                'component name',
                'name',
                'supplier name',
                'location name',
                trans('general.name'),
                trans('general.asset_name'),
                trans('general.item_name'),
                trans('general.model_name'),
                trans('admin/hardware/form.name'),
            ],
            'item_no' => [
                'item number',
                'item no.',
                'item #',
            ],
            'order_number' => [
                'order #',
                'order no.',
                'order num',
                'order number',
                'order',
            ],
            'eula_text' => [
                'eula',
            ],

            'checkin_email' => [
                'checkin email',
            ],
            'asset_model' => [
                'model name',
                'model',
            ],
            'eol_date' => [
                'eol',
                'eol date',
                'asset eol date',
            ],
            'eol' => [
                'eol',
                'EOL',
                'eol months',
            ],
            'gravatar' => [
                'gravatar',
            ],
            'currency' => [
                '$',
            ],
            'jobtitle' => [
                'job title for user',
                'job title',
            ],
            'full_name' => [
                'full name',
                'fullname',
                trans('general.importer.checked_out_to_fullname'),
            ],
            'username' => [
                'user name',
                'username',
                trans('general.importer.checked_out_to_username'),
            ],
            'display_name' => [
                'display name',
                'displayName',
                'display',
                trans('admin/users/table.display_name'),
            ],
            'first_name' => [
                'first name',
                trans('general.importer.checked_out_to_first_name'),
            ],
            'last_name' => [
                'last name',
                'lastname',
                trans('general.importer.checked_out_to_last_name'),
            ],
            'email' => [
                'email',
                'e-mail',
                trans('general.importer.checked_out_to_email'),
            ],
            'phone_number' => [
                'phone',
                'phone number',
                'phone num',
                'telephone number',
                'telephone',
                'tel.',
            ],
            'mobile_number' => [
                'mobile',
                'mobile number',
                'cell',
                'cellphone',
            ],

            'serial' => [
                'serial number',
                'serial no.',
                'serial no',
                'product key',
                'key',
            ],
            'require_serial' => [
                trans('admin/models/general.importer.require_serial'),
                trans('admin/models/general.importer.serial_reqiured'),
            ],
            'model_number' => [
                'model',
                'model no',
                'model no.',
                'model number',
                'model num',
                'model num.',
            ],
            'warranty_months' => [
                'Warranty',
                'Warranty Months',
            ],
            'quantity' => [
                'QTY',
                'Qty',
                'Quantity',
            ],
            'zip' => [
                'Postal Code',
                'Post Code',
                'Zip Code',
            ],
            'min_amt' => [
                'Min Amount',
                'Minimum Amount',
                'Min Quantity',
                'Minimum Quantity',
            ],
            'next_audit_date' => [
                'Next Audit',
            ],
            'last_checkout' => [
                'Last Checkout',
                'Last Checkout Date',
                'Checkout Date',
            ],
            'address2' => [
                'Address 2',
                'Address2',
            ],
            'ldap_ou' => [
                'LDAP OU',
                'OU',
            ],
            'parent_location' => [
                'Parent',
                'Parent Location',
            ],
            'manager' => [
                'Managed By',
                'Manager Name',
                'Manager Full Name',
            ],
            'manager_username' => [
                'Manager Username',
            ],
            'tag_color' => [
                'color',
                'tag color',
                'label color',
                'color code',
                trans('general.tag_color'),
            ],
            'checkout_class' => [
                'checkout type',
                'checkout class',
            ],
            'checkout_date' => [
                'checkout date',
                'checked out',
                'checked out date',
            ],
            'checkin_date' => [
                'checkin date',
                'check-in date',
                'checked in',
                'checked in date',
            ],
            'asset_tag' => [
                'asset tag',
                'assettag',
                'tag',
            ],
        ];

        $this->columnOptions[''] = $this->getColumns(''); // blank mode? I don't know what this is supposed to mean
        foreach ($this->importTypes as $type => $name) {
            $this->columnOptions[$type] = $this->getColumns($type);
        }
    }

    public function selectFile($id)
    {
        $this->clearMessage();

        $this->activeFileId = $id;

        if (! $this->activeFile) {
            $this->message = trans('admin/hardware/message.import.file_missing');
            $this->message_type = 'danger';

            return;
        }

        // Existence via Storage so the check routes to whichever
        // driver PRIVATE_FILESYSTEM_DISK resolves to, instead of only
        // looking at the local filesystem.
        $storedPath = FileStorage::Imports->privateStorageKey().$this->activeFile->file_path;
        if (! Storage::exists($storedPath)) {
            $this->message = trans('admin/hardware/message.import.file_missing_on_disk');
            $this->message_type = 'danger';

            return;
        }

        $this->activeFileRowCount = $this->countActiveFileRows();
        if ($this->activeFileRowCount === 0) {
            $this->message = trans('admin/hardware/message.import.file_empty');
            $this->message_type = 'danger';

            return;
        }

        $this->headerRow = $this->activeFile->header_row;

        // header_row is populated by the initial upload path but can be null for
        // legacy imports created before that column was persisted, or for rows
        // where a background job never wrote it. Without this guard the foreach
        // below explodes with "foreach() argument must be of type array|object,
        // null given" and the wizard is unrecoverable.
        if (! is_array($this->headerRow) || $this->headerRow === []) {
            $this->message = trans('admin/hardware/message.import.header_row_missing');
            $this->message_type = 'danger';

            return;
        }

        $this->typeOfImport = $this->activeFile->import_type;

        $this->field_map = null;
        foreach ($this->headerRow as $element) {
            if (isset($this->activeFile->field_map[$element])) {
                // Preserved values may be either a real target-field key
                // or the empty string "" (user's explicit "Do not import"
                // choice, persisted by generate_field_map). Push through
                // as-is; the blade side's is_null-based @continue keeps
                // "" rows visible so the user can flip them back.
                $this->field_map[] = $this->activeFile->field_map[$element];
            } else {
                // Header wasn't in the saved map at all. Treat as
                // never-mapped (auto-map couldn't bind or this header
                // is new since the template was saved). Null hides the
                // row in the wizard by design.
                $this->field_map[] = null;
            }
        }

        $this->file_id = $id;
        $this->import_errors = null;
        $this->statusText = null;
        $this->wizardStep = 1;
        $this->previewRows = [];
        $this->processing = false;
        $this->progress = -1;
        $this->progress_message = '';
        $this->progress_bar_class = 'progress-bar-warning';

        $this->dispatch('open-import-modal');
    }

    /**
     * Flip step 3 from preview mode to processing mode. Called from the
     * modal's Process-button JS handler right before it starts firing
     * slice requests. progress / progress_message / progress_bar_class
     * get updated by that same JS between slices to drive the bar.
     */
    public function startProcessing(bool $withBackup = false): void
    {
        // Demo mode: uploads are blocked at Api\ImportController::store,
        // but a demo superadmin should still be able to run the seeded
        // sample imports so the end-to-end flow is exercisable in the
        // demo. Non-superadmins get the same "feature disabled" bail as
        // before. Api\ImportController::process() applies the matching
        // gate on the per-slice POSTs.
        if (config('app.lock_passwords') && ! auth()->user()->isSuperUser()) {
            $this->message = trans('general.feature_disabled');
            $this->message_type = 'danger';

            return;
        }

        // Per-file mutex. Two admins (or two tabs) trying to process
        // the SAME file concurrently would double-insert rows and race
        // the slice index. Different files stay independent because
        // the key is scoped by file id. The lock persists across all
        // per-slice API POSTs until stopProcessing() releases it, or
        // the 30-min TTL expires if the admin abandons the page
        // mid-run.
        $file = Import::find($this->activeFileId);
        if ($file === null) {
            $this->message = trans('general.file_not_found');
            $this->message_type = 'danger';

            return;
        }
        if (! $this->acquireOperationLock($file->lockKey())) {
            $this->message = $this->operationAlreadyRunningMessage('general.import');
            $this->message_type = 'danger';

            return;
        }

        $this->processing = true;
        $this->progress = 0;
        $this->progress_bar_class = 'progress-bar-warning';
        $this->progress_message = '';
        $this->backupRequested = $withBackup;
        $this->backupComplete = false;
        // The dedicated processing block renders its own spinner + label,
        // so clear the alert-style status text the JS handler set before
        // us - otherwise both render simultaneously.
        $this->statusText = null;
        $this->statusType = null;
    }

    /**
     * Called from JS after slice 0's request returns. The sync backup
     * runs at the very top of Api\ImportController::process() before
     * any rows are touched, so slice 0's completion is the earliest
     * moment we can be sure the backup finished.
     */
    public function markBackupComplete(): void
    {
        $this->backupComplete = true;
    }

    /**
     * Called from the modal's Process-button JS handler if any slice
     * fails. Drops back to preview mode so the user can back out or
     * retry.
     */
    public function stopProcessing(): void
    {
        $this->processing = false;

        // Release the per-file mutex acquired in startProcessing().
        // Called by the slice-driver JS on either successful completion
        // or any slice failure, so the lock clears whether the run
        // finished or bailed early.
        $file = Import::find($this->activeFileId);
        if ($file !== null) {
            $this->releaseOperationLock($file->lockKey());
        }
    }

    /**
     * Advance the wizard one step. Guards: can't leave step 1 without a
     * type selected, can't leave step 2 without a mapping (any non-null
     * field_map entries), and step 3 is the last step (Process is a
     * separate JS handler). Populates preview rows when transitioning
     * into step 3 so the modal's preview table has content ready.
     */
    public function nextStep(): void
    {
        if ($this->wizardStep === 1) {
            if (empty($this->typeOfImport)) {
                $this->statusType = 'error';
                $this->statusText = trans('admin/hardware/message.import.type_required');

                return;
            }
            // updatingTypeOfImport() is the standard auto-map trigger,
            // but it only fires when the caller CHANGES the type dropdown.
            // On a fresh file selection where the stored import_type gets
            // assigned programmatically inside selectFile(), that hook
            // never runs and the user lands in step 2 with an all-null
            // field_map. Re-run the auto-map on 1->2 so the mapping step
            // always starts with our best-guess bindings.
            $this->updatingTypeOfImport($this->typeOfImport);
            $this->wizardStep = 2;
            $this->clearMessage();
            $this->statusText = null;
            $this->statusType = null;

            return;
        }

        if ($this->wizardStep === 2) {
            $missing = $this->missingRequiredMappings();
            if (! empty($missing)) {
                $labels = array_map(
                    fn ($key) => $this->columnOptions[$this->typeOfImport][$key] ?? $key,
                    $missing,
                );
                $this->statusType = 'error';
                $this->statusText = trans('admin/hardware/message.import.required_fields_missing', [
                    'fields' => implode(', ', $labels),
                ]);

                return;
            }

            $this->clearMessage();
            $this->statusText = null;
            $this->statusType = null;
            $this->previewRows = $this->loadPreviewRows();
            $this->wizardStep = 3;
        }
    }

    /**
     * Return the required target-field keys that the current field_map
     * doesn't cover. Called before advancing from mapping to preview so
     * users can't get to Process on a mapping that's guaranteed to have
     * every row skipped.
     */
    public function missingRequiredMappings(): array
    {
        $required = $this->requiredForType($this->typeOfImport);
        $mapped = array_filter((array) $this->field_map);

        return array_values(array_diff($required, $mapped));
    }

    /**
     * Compute the list of required importer target-field keys for a given
     * import type. Reads the underlying model's `$rules` array so a rule
     * change (adding / removing `required`) doesn't need a matching edit
     * here. assetHistory has no model to introspect so its requirement
     * list is inlined.
     */
    public function requiredForType(?string $type): array
    {
        if ($type === 'assetHistory') {
            return ['asset_tag', 'full_name', 'checkout_date'];
        }

        if (! $type || ! isset($this->required_field_model_map[$type])) {
            return [];
        }

        [$modelClass, $translation] = $this->required_field_model_map[$type];

        // Defense in depth for GHSA-r43j-45c3-9vvw. #[Locked] on the
        // property above should already reject client writes, but if a
        // Livewire regression ever bypasses Locked, this interface check
        // keeps the `new $modelClass` dispatch bounded to the set of
        // models that opt in by implementing the Importable marker.
        if (! is_string($modelClass) || ! class_exists($modelClass) || ! is_subclass_of($modelClass, Importable::class)) {
            return [];
        }

        $rules = (new $modelClass)->getRules();

        $required = [];
        foreach ($translation as $importerKey => $modelAttr) {
            $rule = $rules[$modelAttr] ?? '';
            $ruleString = is_array($rule) ? implode('|', $rule) : (string) $rule;
            if (str_contains($ruleString, 'required')) {
                $required[] = $importerKey;
            }
        }

        // Custom fields are intentionally NOT flagged as required at the
        // wizard level for asset imports. Required-ness varies per fieldset,
        // and a CSV can span multiple asset models pointing at different
        // fieldsets, so a field required in Fieldset A may be irrelevant
        // for rows destined for Fieldset B (issue #19468). Server-side
        // validation on Asset::save() enforces the correct per-asset rule
        // via the resolved custom_fieldset validation rules,
        // which reads the pivot->required flag for the specific fieldset
        // attached to the row's model. Rows that legitimately need the
        // field will still fail at save-time and surface in the import
        // error output.

        return $required;
    }

    public function previousStep(): void
    {
        if ($this->wizardStep > 1) {
            $this->wizardStep--;
            $this->clearMessage();
            $this->statusText = null;
            $this->statusType = null;
        }
    }

    /**
     * Read the first PREVIEW_ROW_LIMIT data rows out of the active file's
     * CSV. Returns an array of arrays, each keyed by the same header
     * strings the mapping table uses. Silent-fallback on read errors so
     * the modal can still transition to the preview step (which will show
     * an empty table + the user can back out to step 2).
     */
    private function loadPreviewRows(): array
    {
        if (! $this->activeFile) {
            return [];
        }

        // Reader::createFromStream so League CSV reads through the
        // Storage abstraction. On local this is effectively the same
        // as the old createFromPath. On s3_private the stream pulls
        // bytes directly from S3 via the SDK so we never need the
        // file to touch local disk.
        $storedPath = FileStorage::Imports->privateStorageKey().$this->activeFile->file_path;
        $stream = Storage::readStream($storedPath);
        if ($stream === null) {
            return [];
        }

        try {
            $reader = Reader::createFromStream($stream);
            $reader->setHeaderOffset(0);

            $rows = [];
            foreach ($reader->getRecords() as $row) {
                if (self::rowIsBlank($row)) {
                    continue;
                }
                $rows[] = $row;
                if (count($rows) >= self::PREVIEW_ROW_LIMIT) {
                    break;
                }
            }

            return $rows;
        } catch (\Throwable $e) {
            return [];
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * Count the number of NON-BLANK data rows in the currently active file's
     * CSV. Rows where every cell is empty (like ",,,,,,") are excluded so
     * a file that is technically 50 rows tall but has nothing importable
     * counts as 0 and the wizard's file_empty guard refuses to open. Returns
     * 0 also when the file is missing or the CSV can't be read.
     */
    private function countActiveFileRows(): int
    {
        if (! $this->activeFile) {
            return 0;
        }

        // Same disk-aware read path as loadPreviewRows.
        $storedPath = FileStorage::Imports->privateStorageKey().$this->activeFile->file_path;
        $stream = Storage::readStream($storedPath);
        if ($stream === null) {
            return 0;
        }

        try {
            $reader = Reader::createFromStream($stream);
            $reader->setHeaderOffset(0);

            $count = 0;
            foreach ($reader->getRecords() as $row) {
                if (! self::rowIsBlank($row)) {
                    $count++;
                }
            }

            return $count;
        } catch (\Throwable $e) {
            return 0;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * True when every value in the CSV row is an empty string (after trim).
     * Used to skip cells like ",,,,,,,," that carry no information but pad
     * the file length and would otherwise inflate row counts, appear in the
     * preview, and dispatch pointless slices to the server on Process.
     */
    private static function rowIsBlank(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    public function destroy($id)
    {
        if (config('app.lock_passwords')) {
            $this->message = trans('general.feature_disabled');
            $this->message_type = 'danger';

            return;
        }

        $import = Import::find($id);

        // Check that the import wasn't deleted after while page was already loaded...
        // @todo: next up...handle the file being missing for other interactions...
        // for example having an import open in two tabs, deleting it, and then changing
        // the import type in the other tab. The error message below wouldn't display in that case.
        if (! $import) {
            $this->message = trans('admin/hardware/message.import.file_already_deleted');
            $this->message_type = 'danger';

            return;
        }

        if ((auth()->user()->id != $import->created_by) && (! auth()->user()->isSuperUser())) {
            $this->message = trans('general.generic_model_not_found', ['model' => trans('general.import')]);
            $this->message_type = 'danger';

            return;
        }

        if (Storage::delete(FileStorage::Imports->privateStorageKey().$import->file_path)) {
            $import->delete();
            $this->message = trans('admin/hardware/message.import.file_delete_success');
            $this->message_type = 'success';

            unset($this->files);
            unset($this->existingImportFiles[$import->file_path]);

            return;
        }

        $this->message = trans('general.generic_model_not_found', ['model' => trans('general.import')]);
        $this->message_type = 'danger';
    }

    public function process(): void
    {
        $this->message = trans('general.token_expired');
        $this->message_type = 'danger';
    }

    public function clearMessage()
    {
        $this->message = null;
        $this->message_type = null;
    }

    /**
     * IDs of imports that were freshly uploaded in the current session
     * and haven't been dismissed yet. Rendered with a subtle green row
     * tint in the file list so users can spot the row their upload just
     * added, especially after refresh-invalidation shuffles the paginator.
     */
    public $newlyUploadedIds = [];

    /**
     * Called from the fileupload widget's done() callback. Records the
     * freshly-uploaded IDs so the file table row gets a green highlight
     * on the next render, and busts the memoized files() computed so the
     * paginator picks up the new rows. Per-file progress bars are managed
     * entirely client-side now (the old shared $progress state can't
     * represent multiple concurrent uploads cleanly).
     */
    public function uploadSucceeded(array $ids = []): void
    {
        foreach ($ids as $id) {
            $intId = (int) $id;
            if ($intId > 0 && ! in_array($intId, $this->newlyUploadedIds, true)) {
                $this->newlyUploadedIds[] = $intId;
            }
        }

        unset($this->files);
        // New uploads land during this cycle. Repopulate the set so
        // fileMissingOnDisk sees them without waiting for the next
        // mount.
        $this->refreshExistingImportFiles();

        // Fire-and-forget signal to the JS side to schedule the
        // clear-highlights timeout. Doing the delay client-side keeps
        // Livewire from having to babysit a wall-clock timer.
        $this->dispatch('new-uploads-highlighted');
    }

    /**
     * Called from JS after the star-spin timeout expires. Empties the
     * list so subsequent renders drop the fa-spin star from the file
     * rows.
     */
    public function clearNewlyUploadedIds(): void
    {
        $this->newlyUploadedIds = [];
    }

    /**
     * Companion to uploadSucceeded() for the fail() callback. Currently
     * a no-op on the server (the JS shows the red bar and error text per
     * file); kept as a stub so the JS callback has a landing spot if we
     * want to add server-side tracking later.
     */
    public function uploadFailed(): void
    {
        //
    }

    /**
     * True when the current field_map maps any user-identifying column
     * (username, email, first/last/full/display name, or checkout_user).
     * Asset / accessory / consumable / license imports may check items
     * out to users, in which case the wizard should surface the
     * send-welcome checkbox even though the import type isn't 'user'.
     * The welcome email itself only fires when a new user is actually
     * created; existing-user matches don't retrigger it.
     */
    #[Computed]
    public function hasUserCheckoutMapping(): bool
    {
        if (empty($this->field_map) || ! is_array($this->field_map)) {
            return false;
        }

        $userIdentityFields = [
            'username',
            'checkout_user',
            'email',
            'first_name',
            'last_name',
            'full_name',
            'display_name',
        ];

        foreach ($this->field_map as $mapped) {
            if (in_array($mapped, $userIdentityFields, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when the current user's FMCS memberships restrict what they
     * can import into. Superusers, and empty-pivot floater actors skip this.
     */
    #[Computed]
    public function showFmcsRestrictionNotice(): bool
    {
        if (auth()->user()->isSuperUser()) {
            return false;
        }

        $settings = Setting::getSettings();
        if (! $settings->full_multiple_companies_support) {
            return false;
        }

        $userCompanyIds = auth()->user()->companies()->pluck('companies.id')->all();

        return ! (empty($userCompanyIds) && $settings->null_company_is_floater);
    }

    #[Computed]
    public function files()
    {
        // Non-superusers only see their own imports. The delete path
        // already enforced this (see destroy() + canDeleteFile()), but
        // the file listing, activeFile computed property, and selectFile
        // action all read Import records without an owner filter -
        // letting a user with import permission enumerate every other
        // user's uploaded CSV and preview its header + first-row values
        // through the Livewire UI. Matches the scoping the API uses at
        // Api\ImportController::index.
        return Import::query()
            ->when(! auth()->user()->isSuperUser(), fn ($q) => $q->where('created_by', auth()->id()))
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function activeFile()
    {
        // Same owner-scope as files() above. Returning null for records
        // the caller doesn't own means selectFile()'s "file not found"
        // branch fires and no header/preview state gets populated.
        return Import::query()
            ->when(! auth()->user()->isSuperUser(), fn ($q) => $q->where('created_by', auth()->id()))
            ->find($this->activeFileId);
    }

    /**
     * True when the current caller may delete the given Import (owner or
     * superuser). Kept as a method so the view can guard both the row
     * checkbox and the singular delete button consistently.
     */
    public function fileMissingOnDisk(Import $import): bool
    {
        // Read against the cached set of file basenames rather than
        // firing an S3 request per render. The set is populated once
        // at mount and refreshed only when this component causes a
        // mutation (uploadSucceeded, destroy, bulkDestroy). Every
        // other re-render (selectAll toggle, individual row check,
        // pagination, wizard step change) reads the cached array
        // without any S3 traffic, which was previously stretching a
        // simple checkbox click into a multi-second round-trip on
        // buckets with a few thousand import files.
        return ! isset($this->existingImportFiles[$import->file_path]);
    }

    /**
     * Basename-keyed set of files currently in
     * private_uploads/imports on the configured private disk.
     * Serialized as component state so subsequent renders don't need
     * to re-list the disk. Callers that mutate the imports dir
     * (upload success + delete paths) invoke refreshExistingImportFiles
     * to keep this in sync.
     *
     * @var array<string, true>
     */
    public array $existingImportFiles = [];

    /**
     * Re-scan the imports dir on the configured private disk and
     * rebuild the basename set. Called from mount() so the initial
     * render has a fresh view, and from uploadSucceeded / destroy /
     * bulkDestroy so post-mutation renders reflect the change.
     */
    public function refreshExistingImportFiles(): void
    {
        $set = [];
        foreach (Storage::files(rtrim(FileStorage::Imports->privateStorageKey(), '/')) as $path) {
            $set[basename($path)] = true;
        }
        $this->existingImportFiles = $set;
    }

    public function canDeleteFile(Import $import): bool
    {
        return auth()->user()->id === $import->created_by || auth()->user()->isSuperUser();
    }

    /**
     * Livewire lifecycle hook: fires when Livewire's built-in $page property
     * changes (any paginator link click). Selection is a per-page affordance,
     * so drop it whenever the page changes.
     */
    public function updatedPage()
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    /**
     * Livewire lifecycle hook for the header "select all" checkbox. When
     * toggled on, pick every current-page id the caller is allowed to delete;
     * when toggled off, clear the selection. Rows the caller cannot delete
     * (not owner, not superuser) are never selectable, so leaving them out
     * of $selectedIds matches what the disabled row checkboxes convey.
     */
    public function updatedSelectAll($value)
    {
        if (! $value) {
            $this->selectedIds = [];

            return;
        }

        $this->selectedIds = collect($this->files->items())
            ->filter(fn (Import $import) => $this->canDeleteFile($import))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    /**
     * Delete every Import in $selectedIds the caller may delete. Rows they
     * cannot delete are counted separately and reported so the caller knows
     * why the total may not match what they ticked. File-system delete
     * failures are swallowed intentionally: the record delete still runs so
     * a missing/orphaned physical file doesn't lock the import list forever.
     */
    public function bulkDestroy()
    {
        if (config('app.lock_passwords')) {
            $this->message = trans('general.feature_disabled');
            $this->message_type = 'danger';

            return;
        }

        if (empty($this->selectedIds)) {
            return;
        }

        $imports = Import::whereIn('id', $this->selectedIds)->get();

        $deleted = 0;
        $skipped = 0;

        foreach ($imports as $import) {
            if (! $this->canDeleteFile($import)) {
                $skipped++;

                continue;
            }

            Storage::delete(FileStorage::Imports->privateStorageKey().$import->file_path);
            $import->delete();
            unset($this->existingImportFiles[$import->file_path]);
            $deleted++;
        }

        $this->selectedIds = [];
        $this->selectAll = false;
        unset($this->files);

        if ($deleted === 0 && $skipped > 0) {
            $this->message = trans('admin/hardware/message.import.bulk_delete.skipped', ['count' => $skipped]);
            $this->message_type = 'danger';

            return;
        }

        $this->message = trans_choice('admin/hardware/message.import.bulk_delete.success', $deleted, ['count' => $deleted]);
        if ($skipped > 0) {
            $this->message .= ' '.trans('admin/hardware/message.import.bulk_delete.skipped', ['count' => $skipped]);
        }
        $this->message_type = 'success';
    }

    public function render()
    {
        return view('livewire.importer')
            ->extends('layouts.default')
            ->section('content');
    }
}
