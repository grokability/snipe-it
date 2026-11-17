<?php

namespace App\Importer;

use App\Exceptions\ImportRowRejected;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyableScope;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;

class ItemImporter extends Importer
{
    protected $item;

    protected Setting $settings;

    // Every concrete subclass currently disables the reject-empty pass on
    // update so that a present-but-empty CSV cell clears the corresponding
    // DB column. Absent columns still preserve the DB value because the
    // subclasses' handle() methods only populate $this->item for cells that
    // are actually in the row. Flip to true on a subclass, or via
    // setRejectEmptyOnUpdate() at runtime, to fall back to "keep DB value
    // on empty CSV cell" behavior.
    protected bool $rejectEmptyOnUpdate = false;

    public function setRejectEmptyOnUpdate(bool $reject): self
    {
        $this->rejectEmptyOnUpdate = $reject;

        return $this;
    }

    public function __construct($filename)
    {
        parent::__construct($filename);
        $this->settings = Setting::getSettings();
    }

    protected function handle($row)
    {

        /**
         * This section adds the most common fields into the $item array so we don't have to manually add them to
         * things like accessories, consumables, etc.
         */

        // Need to reset this between iterations or we'll have stale data.
        $this->item = [];

        $item_category = $this->findCsvMatch($row, 'category');
        if ($this->shouldUpdateField($item_category)) {
            $this->item['category_id'] = $this->createOrFetchCategory($item_category);
        }

        $item_company_name = $this->findCsvMatch($row, 'company');
        if ($this->shouldUpdateField($item_company_name)) {
            $this->item['company_id'] = $this->createOrFetchCompany($item_company_name);
        }

        $item_location = $this->findCsvMatch($row, 'location');
        if ($this->shouldUpdateField($item_location)) {
            $this->item['location_id'] = $this->createOrFetchLocation($item_location);
        }

        $item_manufacturer = $this->findCsvMatch($row, 'manufacturer');
        if ($this->shouldUpdateField($item_manufacturer)) {
            $this->item['manufacturer_id'] = $this->createOrFetchManufacturer($item_manufacturer);
        }

        $item_status_name = $this->findCsvMatch($row, 'status');
        if ($this->shouldUpdateField($item_status_name)) {
            $this->item['status_id'] = $this->createOrFetchStatusLabel($item_status_name);
        }

        $item_supplier = $this->findCsvMatch($row, 'supplier');
        if ($this->shouldUpdateField($item_supplier)) {
            $this->item['supplier_id'] = $this->createOrFetchSupplier($item_supplier);
        }

        $item_department = $this->findCsvMatch($row, 'department');
        if ($this->shouldUpdateField($item_department)) {
            $this->item['department_id'] = $this->createOrFetchDepartment($item_department);
        }

        $item_manager_first_name = $this->findCsvMatch($row, 'manager_first_name');
        $item_manager_last_name = $this->findCsvMatch($row, 'manager_last_name');

        if ($this->shouldUpdateField($item_manager_first_name)) {
            $this->item['manager_id'] = $this->fetchManager($item_manager_first_name, $item_manager_last_name);
        }

        $this->item['name'] = $this->findCsvMatch($row, 'item_name');
        $this->item['notes'] = $this->findCsvMatch($row, 'notes');
        // order_number is no longer a column on the inventory tables —
        // it moved to the Orders / OrderItems data model. Sub-importers
        // call ItemImporter::recordOrderForImportedRow() after the row
        // saves to record the acquisition. Reading the value straight
        // off the row inside that helper (rather than staging into
        // $this->item['order_number']) avoids a fillable-drop no-op.
        $this->item['purchase_cost'] = $this->findCsvMatch($row, 'purchase_cost');
        $this->item['model_number'] = trim($this->findCsvMatch($row, 'model_number'));
        $this->item['min_amt'] = $this->findCsvMatch($row, 'min_amt');
        $this->item['qty'] = $this->findCsvMatch($row, 'quantity');
        $this->item['requestable'] = $this->findCsvMatch($row, 'requestable');
        $this->item['created_by'] = $this->created_by;
        $this->item['asset_tag'] = $this->findCsvMatch($row, 'asset_tag');
        $this->item['serial'] = $this->findCsvMatch($row, 'serial');
        $this->item['item_no'] = trim($this->findCsvMatch($row, 'item_no'));

        $this->item['purchase_date'] = null;
        if ($this->findCsvMatch($row, 'purchase_date') != '') {
            $this->item['purchase_date'] = $this->findCsvMatch($row, 'purchase_date');
            $this->item['purchase_date'] = $this->parseOrNullDate('purchase_date');
        }

        // NO need to call this method if we're running the user import.
        // TODO: Merge these methods.
        $this->item['checkout_class'] = $this->findCsvMatch($row, 'checkout_class');
        if (get_class($this) !== UserImporter::class) {
            // $this->item["user"] = $this->createOrFetchUser($row);
            $this->item['checkout_target'] = $this->determineCheckout($row);
        }
    }

    /**
     * Parse row to determine what (if anything) we should checkout to.
     *
     * @param  array  $row  CSV Row being parsed
     * @return ?SnipeModel Model to be checked out to
     */
    protected function determineCheckout($row)
    {
        // Locations don't get checked out to anyone/anything
        if ((get_class($this) == LocationImporter::class) || (get_class($this) == AssetModelImporter::class) || (get_class($this) == SupplierImporter::class) || (get_class($this) == ManufacturerImporter::class) || (get_class($this) == CategoryImporter::class)) {
            return;
        }

        $checkoutClass = strtolower((string) $this->item['checkout_class']);
        $checkoutLocation = $this->findCsvMatch($row, 'checkout_location');
        $checkoutAsset = $this->findCsvMatch($row, 'checkout_asset');
        // checkout_user is not read locally: createOrFetchUser looks it up
        // via findCsvMatch as a username fallback, so the user path picks it
        // up whether we reach it via the explicit checkout_class=user branch
        // or the default fall-through. Populating $this->item here is
        // deliberate so subclasses that inspect the item array see it.

        // Checkout intent is inferred from which target-shape column has a
        // value: checkout_asset (asset tag of parent asset), checkout_location
        // (location name), checkout_user (username), or the multi-column
        // user-identity path (email / username / full_name / etc.).
        // checkout_class is only required as an explicit override when a row
        // has more than one populated and needs disambiguation, or for
        // backward-compat with pre-inference CSVs. Prior behavior required
        // both a checkout_location AND a checkout_class column set to
        // "Location" on every row; when checkout_class was missing the code
        // silently fell through to user lookup, produced a null target, and
        // no checkout event fired with no error surfaced. checkout_user is
        // keyed on username specifically because email is not enforced as
        // unique on the users table (the unique index was dropped in the
        // 2015_07_25_055415 migration) and would silently match the wrong
        // user in installs with duplicates. createOrFetchUser reads
        // checkout_user as a username fallback, so the user path (lookup
        // OR create when the row has enough identity data) works whether
        // the operator maps username, checkout_user, or both.

        // Explicit checkout_class wins when set and has a matching target
        // column populated. Preserves the disambiguation escape hatch
        // (e.g. checkout_class=user beats a populated checkout_location).
        if ($checkoutClass === 'asset' && $checkoutAsset) {
            return $this->findAssetCheckoutTarget($checkoutAsset);
        }
        if ($checkoutClass === 'location' && $checkoutLocation) {
            return Location::findOrFail($this->createOrFetchLocation($checkoutLocation));
        }
        if (in_array($checkoutClass, ['user', 'person'], true)) {
            return $this->createOrFetchUser($row);
        }

        // Inference paths. Any target-shape column populated is enough to
        // route to that target type when no checkout_class contradicted it.
        if ($checkoutAsset) {
            return $this->findAssetCheckoutTarget($checkoutAsset);
        }

        if ($checkoutLocation) {
            return Location::findOrFail($this->createOrFetchLocation($checkoutLocation));
        }

        // User path handles both checkout_user (single-column shortcut) and
        // the multi-column user-identity shape. createOrFetchUser looks up
        // by username first and creates a new user if the row has enough
        // additional identity data (full_name / first_name / email).
        return $this->createOrFetchUser($row);
    }

    /**
     * Look up an existing asset by tag to use as a checkout target. We do
     * NOT auto-create assets here the way createOrFetchLocation does. A
     * checkout target is an existing physical thing; creating a phantom
     * asset just to satisfy a checkout row would mask CSV typos and
     * pollute the inventory. Returns null when the tag doesn't resolve,
     * which the caller in AssetImporter surfaces as a per-row warning
     * (see the checkoutColumnPopulated block there).
     */
    protected function findAssetCheckoutTarget($assetTag)
    {
        if (empty($assetTag)) {
            return null;
        }

        $asset = \App\Models\Asset::where('asset_tag', (string) $assetTag)->first();

        if (! $asset) {
            $this->log('WARNING: checkout_asset "'.$assetTag.'" does not match any existing asset tag. The checkout will be skipped.');

            return null;
        }

        return $asset;
    }

    /**
     * Cleanup the $item array before storing.
     * We need to remove any values that are not part of the fillable fields.
     * Also, if updating, we remove any fields from the array that are empty.
     *
     * @author Daniel Melzter
     *
     * @since 4.0
     *
     * @param  $model  SnipeModel Model that's being updated.
     * @param  $updating  boolean Should we remove blank values?
     * @return array
     */
    protected function sanitizeItemForStoring($model, $updating = false)
    {
        // Create a collection for all manipulations to come.
        $item = collect($this->item);
        // First Filter the item down to the model's fillable fields
        $item = $item->only($model->getFillable());

        // Then iterate through the item and, if we are updating, remove any blank values.
        if ($updating && $this->rejectEmptyOnUpdate) {
            $item = $item->reject(function ($value) {
                return empty($value);
            });
        }

        return $item->toArray();
    }

    /**
     * Convenience function for updating that strips the empty values.
     *
     * @param  $model  SnipeModel Model that's being updated.
     * @return array
     */
    protected function sanitizeItemForUpdating($model)
    {
        return $this->sanitizeItemForStoring($model, true);
    }

    /**
     * Apply a sanitized update payload to a model, routing any qty change
     * through the AdjustsQuantity trait so it becomes a QuantityAdjust
     * action_log entry rather than a silent update-log overwrite. Only
     * kicks in for models that use the trait (Accessory, Consumable,
     * Component). For everything else it's a plain $model->update().
     *
     * A DomainException from adjustQuantity (would drop qty below the
     * currently-in-use count) is logged and the row's non-qty updates
     * still stick. The AdjustsQuantity trait's `$orderNumber` argument
     * is passed as null here because the Orders / OrderItems data model
     * now owns the acquisition record; wiring the importer to also
     * create an Order for the qty delta is a separate follow-up under
     * the adjust-quantity flow rework.
     */
    protected function applyUpdateWithQtyAdjust($model, array $sanitized): void
    {
        $qtyRequested = null;

        if (method_exists($model, 'adjustQuantity') && array_key_exists('qty', $sanitized)) {
            // Empty CSV cell (present but blank) means "don't touch qty"
            // on update — not "set qty to 0". Casting '' straight to (int)
            // 0 produced a delta of -currentQty and silently drained
            // inventory on any import row that included an empty
            // quantity column.
            if ($sanitized['qty'] !== '' && $sanitized['qty'] !== null) {
                $qtyRequested = (int) $sanitized['qty'];
            }
            unset($sanitized['qty']);
        }

        $qtyBefore = $qtyRequested !== null ? (int) $model->qty : null;

        $model->update($sanitized);

        if ($qtyRequested === null || $qtyRequested === $qtyBefore) {
            return;
        }

        try {
            $model->adjustQuantity(
                $qtyRequested - $qtyBefore,
                "Import: qty updated from {$qtyBefore} to {$qtyRequested}",
                null,
            );
        } catch (\DomainException) {
            $this->log('Skipping qty change for '.($model->name ?? 'row').': would drop on-hand below the currently-checked-out count.');
        }
    }

    /**
     * Record the CSV row's order_number (if any) as an Order + OrderItem
     * pair against the freshly-saved model. Called from the CREATE
     * branch of every sub-importer that participates in the Orders data
     * model (Accessory, Consumable, Component, Asset, License).
     *
     * Dedupes on the Order side via (order_number, supplier_id, company_id)
     * so multiple items in the same CSV that share an order_number all
     * land under a single Order row. Never dedupes on the OrderItem
     * side — each imported row is its own line, matching the "one line
     * per item purchased" semantic.
     *
     * Deliberately does not run on UPDATE imports: importer update mode
     * means "the CSV has a corrected version of an existing row", not
     * "a new purchase happened". The adjust-quantity flow is the path
     * that records replenishment events for existing rows and will
     * grow its own Order-creation wiring when that flow is reworked.
     */
    protected function recordOrderForImportedRow($model, array $row): void
    {
        $orderNumber = trim((string) $this->findCsvMatch($row, 'order_number'));
        $currency = trim((string) $this->findCsvMatch($row, 'currency'));
        $supplierId = $this->item['supplier_id'] ?? null;
        $purchaseDate = $this->item['purchase_date'] ?? null;
        $rawCost = $this->item['purchase_cost'] ?? null;
        $purchaseCost = ($rawCost !== null && $rawCost !== '') ? (float) $rawCost : null;

        if ($orderNumber === ''
            && $currency === ''
            && $supplierId === null
            && $purchaseDate === null
            && $purchaseCost === null
        ) {
            return;
        }

        // Every accessory / consumable / component / asset create fires
        // its observer which writes an initial Order + OrderItem from
        // parent attributes (with null acquisition metadata, parents
        // don't carry those columns any more). The importer's job here
        // is to enrich the observer-created rows with the CSV's values.
        $initialLine = $model->orderItems()->latest('id')->first();
        if (! $initialLine || ! $initialLine->order) {
            return;
        }

        $order = $initialLine->order;
        $updates = [];

        if ($orderNumber !== '' && $order->order_number !== $orderNumber) {
            $updates['order_number'] = $orderNumber;
        }
        if ($currency !== '' && $order->currency !== $currency) {
            $updates['currency'] = $currency;
        }
        if ($supplierId !== null && (int) $order->supplier_id !== (int) $supplierId) {
            $updates['supplier_id'] = (int) $supplierId;
        }
        if ($purchaseDate !== null && optional($order->purchase_date)->toDateString() !== (string) $purchaseDate) {
            $updates['purchase_date'] = $purchaseDate;
        }

        if ($updates !== []) {
            $order->update($updates);
        }

        if ($purchaseCost !== null && (float) $initialLine->price !== $purchaseCost) {
            $initialLine->update(['price' => $purchaseCost]);
        }
    }

    /**
     * Determines if a field needs updating
     * Follows the following rules:
     * If we are not updating, we should update the field
     * If We are updating, we only update the field if it's not empty.
     *
     * @author Daniel Melzter
     *
     * @since 4.0
     *
     * @param  $field  string
     * @return bool
     */
    protected function shouldUpdateField($field)
    {
        if (empty($field)) {
            return false;
        }

        return ! ($this->updating && empty($field));
    }

    /**
     * Select the asset model if it exists, otherwise create it.
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     *
     * @param  array<string, mixed>  $row
     * @return int Id of asset model created/found
     */
    public function createOrFetchAssetModel(array $row)
    {
        $condition = [];
        $asset_model_name = $this->findCsvMatch($row, 'asset_model');
        $asset_model_category = $this->findCsvMatch($row, 'category');
        $asset_modelNumber = $this->findCsvMatch($row, 'model_number');

        // TODO: At the moment, this means  we can't update the model number if the model name stays the same.
        if (! $this->shouldUpdateField($asset_model_name)) {
            return;
        }

        if ((empty($asset_model_name)) && (! empty($asset_modelNumber))) {
            $asset_model_name = $asset_modelNumber;
        } elseif ((empty($asset_model_name)) && (empty($asset_modelNumber))) {
            $asset_model_name = 'Unknown';
        }

        $asset_model = AssetModel::select('id');

        if (! empty($asset_model_name)) {
            $asset_model = $asset_model->where('name', '=', $asset_model_name);

            if (! empty($asset_modelNumber)) {
                $asset_model = $asset_model->where('model_number', '=', $asset_modelNumber);
            }
        }

        $editingModel = $this->updating;
        $asset_model = $asset_model->first();

        if ($asset_model) {

            if (! $this->updating) {
                $this->log('A matching model already exists, returning it.');

                return $asset_model->id;
            }

            $this->log('Matching Model found, updating it.');
            $item = $this->sanitizeItemForStoring($asset_model, $editingModel);
            $item['name'] = $asset_model_name;
            $item['notes'] = $this->findCsvMatch($row, 'model_notes');

            if (! empty($asset_modelNumber)) {
                $item['model_number'] = $asset_modelNumber;
            }

            $asset_model->update($item);
            $asset_model->save();
            $this->log('Asset Model Updated');

            return $asset_model->id;

        }

        $this->log('No Matching Model, Creating a new one');
        $asset_model = new AssetModel;
        $asset_model->created_by = $this->created_by;
        $item = $this->sanitizeItemForStoring($asset_model, $editingModel);
        $item['name'] = $asset_model_name;
        $item['model_number'] = $asset_modelNumber;
        $item['notes'] = $this->findCsvMatch($row, 'model_notes');
        $item['category_id'] = $this->createOrFetchCategory($asset_model_category);

        $asset_model->fill($item);
        $item = null;

        if ($asset_model->save()) {
            $this->log('Asset Model '.$asset_model_name.' with model number '.$asset_modelNumber.' was created');

            return $asset_model->id;
        }
        $this->log('Asset Model Errors: '.$asset_model->getErrors());
        $this->logError($asset_model, 'Asset Model "'.$asset_model_name.'"');

        return null;
    }

    /**
     * Finds a category with the same name and item type in the database, otherwise creates it
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     *
     * @param  $asset_category  string
     * @return int Id of category created/found
     *
     * @internal param string $item_type
     */
    public function createOrFetchCategory($asset_category)
    {
        // Magic to transform "AssetImporter" to "asset" or similar.
        $classname = class_basename(get_class($this));
        $item_type = strtolower(substr($classname, 0, strpos($classname, 'Importer')));

        // If we're importing asset models only (without attached assets), override the category type to asset
        if ($item_type == 'assetmodel') {
            $item_type = 'asset';
        }

        if (empty($asset_category)) {
            $asset_category = 'Unnamed Category';
        }

        $category = Category::where(['name' => $asset_category, 'category_type' => $item_type])->first();

        if ($category) {
            $this->log('A matching category: '.$category->name.' already exists');

            return $category->id;
        }

        $category = new Category;
        $category->created_by = $this->created_by;
        $category->name = $asset_category;
        $category->category_type = $item_type;

        if ($category->save()) {
            $this->log('Category '.$asset_category.' was created');

            return $category->id;
        }
        $this->logError($category, 'Category "'.$asset_category.'"');

        return null;
    }

    /**
     * Fetch an existing company, or create new if it doesn't exist
     *
     * @param  $asset_company_name  string
     * @return int|null id of company created/found, or null when FMCS rejects the importing user's access to it
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     */
    public function createOrFetchCompany($asset_company_name)
    {
        // Bypass CompanyableScope so the lookup can see companies the
        // importer's user isn't FMCS-allowed to see. Without this, a
        // scoped user hits the create path against a name that already
        // exists in another tenant, and Watson ValidatingTrait rejects
        // the save because company.name has to be globally unique.
        $company = Company::withoutGlobalScope(CompanyableScope::class)
            ->where('name', $asset_company_name)
            ->first();

        $importing_user = auth()->user() ?? ($this->created_by ? User::find($this->created_by) : null);
        $fmcs = (bool) $this->settings->full_multiple_companies_support;

        if ($company) {
            if ($fmcs && $importing_user !== null && ! $importing_user->isSuperUser()) {
                $userCompanyIds = $importing_user->companies()->pluck('companies.id')->all();
                // Empty pivot under floater mode is a trusted global-access actor.
                $isFloater = empty($userCompanyIds) && (bool) $this->settings->null_company_is_floater;

                if (! $isFloater && ! in_array($company->id, $userCompanyIds, true)) {
                    throw new ImportRowRejected(
                        'company',
                        'User is not a member of company "'.$asset_company_name.'". Row rejected.',
                    );
                }
            }

            $this->log('A matching Company '.$asset_company_name.' already exists');

            return $company->id;
        }

        $company = new Company;
        $company->created_by = $this->created_by;
        $company->name = $asset_company_name;

        if ($company->save()) {
            $this->log('Company '.$asset_company_name.' was created');

            return $company->id;
        }
        $this->logError($company, 'Company');

        return null;
    }

    /**
     * Fetch an existing manager
     *
     * @author A. Gianotto
     *
     * @since 4.6.5
     *
     * @param  $user_manager  string
     * @return int id of company created/found
     */
    public function fetchManager($user_manager_username = null, $user_manager_employee_num = null, $user_manager_first_name = null, $user_manager_last_name = null)
    {
        if ($user_manager_username != '') {
            $manager = User::where('username', '=', $user_manager_username)->first();
            $this->log('Checking on username '.$user_manager_username);
        } elseif ($user_manager_employee_num != '') {
            $manager = User::where('employee_num', '=', $user_manager_employee_num)->first();
            $this->log('Checking on employee_num '.$user_manager_employee_num);
        } else {
            $manager = User::where('first_name', '=', $user_manager_first_name)
                ->where('last_name', '=', $user_manager_last_name)->first();
            $this->log('Checking on full name');
        }

        if ($manager) {
            $this->log('A matching Manager '.$user_manager_first_name.' '.$user_manager_last_name.' already exists');

            return $manager->id;
        }

        $this->log('No matching Manager found. If their user account is being created through this import, you should re-process this file again. ');

        return null;
    }

    /**
     * Fetch the existing status label or create new if it doesn't exist.
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     *
     * @param  string  $asset_statuslabel_name
     * @return Statuslabel|null
     */
    public function createOrFetchStatusLabel($asset_statuslabel_name)
    {
        if (empty($asset_statuslabel_name)) {
            return null;
        }
        $status = Statuslabel::where(['name' => trim($asset_statuslabel_name)])->first();

        if ($status) {
            $this->log('A matching Status '.$asset_statuslabel_name.' already exists');

            return $status->id;
        }
        $this->log('Creating a new status');
        $status = new Statuslabel;
        $status->created_by = $this->created_by;
        $status->name = trim($asset_statuslabel_name);

        $status->deployable = 1;
        $status->pending = 0;
        $status->archived = 0;

        if ($status->save()) {
            $this->log('Status '.$asset_statuslabel_name.' was created');

            return $status->id;
        }

        $this->logError($status, 'Status "'.$asset_statuslabel_name.'"');

        return null;
    }

    /**
     * Finds a manufacturer with matching name, otherwise create it.
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     *
     * @param  $item_manufacturer  string
     * @return Manufacturer
     */
    public function createOrFetchManufacturer($item_manufacturer)
    {
        if (empty($item_manufacturer)) {
            $item_manufacturer = 'Unknown';
        }
        $manufacturer = Manufacturer::where(['name' => $item_manufacturer])->first();

        if ($manufacturer) {
            $this->log('Manufacturer '.$item_manufacturer.' already exists');

            return $manufacturer->id;
        }

        // Otherwise create a manufacturer.
        $manufacturer = new Manufacturer;
        $manufacturer->name = trim($item_manufacturer);
        $manufacturer->created_by = $this->created_by;

        if ($manufacturer->save()) {
            $this->log('Manufacturer '.$manufacturer->name.' was created');

            return $manufacturer->id;
        }
        $this->logError($manufacturer, 'Manufacturer "'.$manufacturer->name.'"');

        return null;
    }

    /**
     * Checks the DB to see if a location with the same name exists, otherwise create it
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     *
     * @param  $asset_location  string
     * @return Location|null
     */
    public function createOrFetchLocation($asset_location)
    {
        if (empty($asset_location)) {
            $this->log('No location given, so none created.');

            return null;
        }

        // Bypass CompanyableScope so the lookup can see locations the
        // importer's user isn't FMCS-allowed to see. Falling through
        // to the create path when a matching row already exists would
        // just produce a second row with the same name.
        $location = Location::withoutGlobalScope(CompanyableScope::class)
            ->where('name', $asset_location)
            ->first();

        $importing_user = auth()->user() ?? ($this->created_by ? User::find($this->created_by) : null);
        $fmcs = (bool) $this->settings->full_multiple_companies_support;
        $locationsFmcs = (bool) $this->settings->scope_locations_fmcs;

        if ($location) {
            // Only enforced when scope_locations_fmcs is on. Otherwise
            // locations are global and every user can see all of them.
            if ($fmcs
                && $locationsFmcs
                && $importing_user !== null
                && ! $importing_user->isSuperUser()
                && $location->company_id
            ) {
                $userCompanyIds = $importing_user->companies()->pluck('companies.id')->all();
                $isFloater = empty($userCompanyIds) && (bool) $this->settings->null_company_is_floater;

                if (! $isFloater && ! in_array($location->company_id, $userCompanyIds, true)) {
                    throw new ImportRowRejected(
                        'location',
                        'User does not have access to location "'.$asset_location.'". Row rejected.',
                    );
                }
            }

            $this->log('Location '.$asset_location.' already exists');

            return $location->id;
        }

        // No matching locations in the collection, create a new one.
        $location = new Location;
        $location->name = $asset_location;
        $location->address = '';
        $location->city = '';
        $location->state = '';
        $location->country = '';
        $location->created_by = $this->created_by;

        if ($location->save()) {
            $this->log('Location '.$asset_location.' was created');

            return $location->id;
        }
        $this->logError($location, 'Location');

        return null;
    }

    /**
     * Fetch an existing supplier or create new if it doesn't exist
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     *
     * @param  $row  array
     * @return Supplier
     */
    public function createOrFetchSupplier($item_supplier)
    {
        if (empty($item_supplier)) {
            $item_supplier = 'Unknown';
        }

        $supplier = Supplier::where(['name' => $item_supplier])->first();

        if ($supplier) {
            $this->log('Supplier '.$item_supplier.' already exists');

            return $supplier->id;
        }

        $supplier = new Supplier;
        $supplier->name = $item_supplier;
        $supplier->created_by = $this->created_by;

        if ($supplier->save()) {
            $this->log('Supplier '.$item_supplier.' was created');

            return $supplier->id;
        }
        $this->logError($supplier, 'Supplier');

        return null;
    }
}
