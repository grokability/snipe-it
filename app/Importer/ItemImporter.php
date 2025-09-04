<?php

namespace App\Importer;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

class ItemImporter extends Importer
{
    protected $item;

    public function __construct($filename)
    {
        parent::__construct($filename);
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
        $this->item['order_number'] = $this->findCsvMatch($row, 'order_number');
        $this->item['purchase_cost'] = $this->findCsvMatch($row, 'purchase_cost');
        $this->item['model_number'] = trim($this->findCsvMatch($row, 'model_number'));
        $this->item['min_amt'] = $this->findCsvMatch($row, 'min_amt');
        $this->item['qty'] = $this->findCsvMatch($row, 'quantity');
        $this->item['requestable'] = $this->findCsvMatch($row, 'requestable');
        $this->item['created_by'] = auth()->id();
        $this->item['serial'] = $this->findCsvMatch($row, 'serial');
        $this->item['item_no'] = trim($this->findCsvMatch($row, 'item_no'));


        $this->item['purchase_date'] = null;
        if ($this->findCsvMatch($row, 'purchase_date') != '') {
            $this->item['purchase_date'] = date('Y-m-d', strtotime($this->findCsvMatch($row, 'purchase_date')));
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
     * @param  array $row CSV Row being parsed
     * @return ?SnipeModel      Model to be checked out to
     */
    protected function determineCheckout($row)
    {
        // Locations don't get checked out to anyone/anything
        if ((get_class($this) == LocationImporter::class) || (get_class($this) == AssetModelImporter::class) || (get_class($this) == SupplierImporter::class) || (get_class($this) == ManufacturerImporter::class)  || (get_class($this) == CategoryImporter::class)) {
            return;
        }

        if (strtolower($this->item['checkout_class']) === 'location' && $this->findCsvMatch($row, 'checkout_location') != null ) {
            return Location::findOrFail($this->createOrFetchLocation($this->findCsvMatch($row, 'checkout_location')));
        }

        return $this->createOrFetchUser($row);
    }

    /**
     * Cleanup the $item array before storing.
     * We need to remove any values that are not part of the fillable fields.
     * Also, if updating, we remove any fields from the array that are empty.
     *
     * @author Daniel Melzter
     * @since 4.0
     * @param $model SnipeModel Model that's being updated.
     * @param $updating boolean Should we remove blank values?
     * @return array
     */
    protected function sanitizeItemForStoring($model, $updating = false)
    {
        // Create a collection for all manipulations to come.
        $item = collect($this->item);
        // First Filter the item down to the model's fillable fields
        $item = $item->only($model->getFillable());

        // Then iterate through the item and, if we are updating, remove any blank values.
        if ($updating) {
            $item = $item->reject(function ($value) {
                return empty($value);
            });
        }

        return $item->toArray();
    }

    /**
     * Convenience function for updating that strips the empty values.
     * @param $model SnipeModel Model that's being updated.
     * @return array
     */
    protected function sanitizeItemForUpdating($model)
    {
        return $this->sanitizeItemForStoring($model, true);
    }

    /**
     * Determines if a field needs updating
     * Follows the following rules:
     * If we are not updating, we should update the field
     * If We are updating, we only update the field if it's not empty.
     *
     * @author Daniel Melzter
     * @since 4.0
     * @param $field string
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
     * @since 3.0
     * @param array
     * @param $row Row
     * @return int Id of asset model created/found
     * @internal param $asset_modelno string
     */
    public function createOrFetchAssetModel(array $row)
    {
        $condition = array();
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

        if (!empty($asset_model_name)) {
            $asset_model = $asset_model->where('name', '=', $asset_model_name);

            if (!empty($asset_modelNumber)) {
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

            if (!empty($asset_modelNumber)){
                $item['model_number'] = $asset_modelNumber;
            }

            $asset_model->update($item);
            $asset_model->save();
            $this->log('Asset Model Updated');
            
            return $asset_model->id;

        }

        $this->log('No Matching Model, Creating a new one');
        $asset_model = new AssetModel();
        $asset_model->created_by = auth()->id();
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
     * @since 3.0
     * @param $asset_category string
     * @return int Id of category created/found
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

        $category = new Category();
        $category->created_by = auth()->id();
        $category->name = $asset_category;
        $category->category_type = $item_type;

        if ($category->save()) {
            $this->log('Category '.$asset_category.' was created');

            return $category->id;
        }
        $this->logError($category, 'Category "'. $asset_category. '"');

        return null;
    }

    /**
     * Fetch an existing company, or create new if it doesn't exist
     *
     * @author Daniel Melzter
     * @since 3.0
     * @param $asset_company_name string
     * @return int id of company created/found
     */
    public function createOrFetchCompany($asset_company_name)
    {
        $company = Company::where(['name' => $asset_company_name])->first();
        if ($company) {
            $this->log('A matching Company '.$asset_company_name.' already exists');

            return $company->id;
        }
        $company = new Company();
        $company->created_by = auth()->id();
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
     * @since 4.6.5
     * @param $user_manager string
     * @return int id of company created/found
     */
    public function fetchManager($user_manager_username = null, $user_manager_employee_num = null, $user_manager_first_name = null, $user_manager_last_name = null)
    {
        if ($user_manager_username!='') {
            $manager = User::where('username', '=', $user_manager_username)->first();
            $this->log('Checking on username '.$user_manager_username);
        } elseif ($user_manager_employee_num!='') {
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
     * @since 3.0
     * @param string $asset_statuslabel_name
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
        $status = new Statuslabel();
        $status->created_by = auth()->id();
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
     * @since 3.0
     * @param $item_manufacturer string
     * @return Manufacturer
     */
    public function createOrFetchManufacturer($item_manufacturer)
    {
        if (empty($item_manufacturer)) {
            $item_manufacturer = 'Unknown';
        }
        $manufacturer = Manufacturer::where(['name'=> $item_manufacturer])->first();

        if ($manufacturer) {
            $this->log('Manufacturer '.$item_manufacturer.' already exists');

            return $manufacturer->id;
        }

        //Otherwise create a manufacturer.
        $manufacturer = new Manufacturer();
        $manufacturer->name = trim($item_manufacturer);
        $manufacturer->created_by = auth()->id();

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
     * @since 3.0
     * @param $asset_location string
     * @return Location|null
     */
    public function createOrFetchLocation($asset_location)
    {
        if (empty($asset_location)) {
            $this->log('No location given, so none created.');
            return null;
        }

        $location = Location::where(['name' => $asset_location])->first();

        if ($location) {
            $this->log('Location '.$asset_location.' already exists');
            return $location->id;
        }
        // No matching locations in the collection, create a new one.
        $location = new Location();
        $location->name = $asset_location;
        $location->address = '';
        $location->city = '';
        $location->state = '';
        $location->country = '';
        $location->created_by = auth()->id();

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
     * @since 3.0
     * @param $row array
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

        $supplier = new Supplier();
        $supplier->name = $item_supplier;
        $supplier->created_by = auth()->id();

        if ($supplier->save()) {
            $this->log('Supplier '.$item_supplier.' was created');
            return $supplier->id;
        }
        $this->logError($supplier, 'Supplier');

        return null;
    }

}
