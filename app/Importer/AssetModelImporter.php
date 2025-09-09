<?php

namespace App\Importer;

use App\Models\AssetModel;
use App\Models\Depreciation;
use App\Models\CustomFieldset;
use Illuminate\Support\Facades\Log;

/**
 * When we are importing users via an Asset/etc import, we use createOrFetchUser() in
 * Importer\Importer.php. [ALG]
 *
 * Class LocationImporter
 */
class AssetModelImporter extends ItemImporter
{
    protected $models;

    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        parent::handle($row);
        $this->createAssetModelIfNotExists($row);
    }

    /**
     * Create a model if a duplicate does not exist.
     * @todo Investigate how this should interact with Importer::createModelIfNotExists
     *
     * @author A. Gianotto
     * @since 6.1.0
     * @param array $row
     */
    public function createAssetModelIfNotExists(array $row)
    {

        $editingAssetModel = false;

        /**
         * This part gets a little confusing, since folks might be importing multiple models with the same name and different model numbers for the first time
         * or they might be wanting to update existing models with new model numbers.
         */

        // They are not trying to update existing models, so we'll check for duplicates with model name *and* number
        if (! $this->updating) {
            $this->log('Finding model by name and model number: '.$this->findCsvMatch($row, 'name').' / '.$this->findCsvMatch($row, 'model_number'));
            $assetModel = AssetModel::where('name', '=', $this->findCsvMatch($row, 'name'))->where('model_number', '=', $this->findCsvMatch($row, 'model_number'))->first();
        } else {

            if ($this->findCsvMatch($row, 'id')!='') {
                // Override model if an ID was given
                $this->log('Finding model by ID: '.$this->findCsvMatch($row, 'id'));
                $assetModel = AssetModel::find($this->findCsvMatch($row, 'id'));
            } else {
                $this->log('Finding model by name: '.$this->findCsvMatch($row, 'name'));
                $assetModel = AssetModel::where('name', '=', $this->findCsvMatch($row, 'name'))->first();
            }
        }


        if ($assetModel) {
            if (! $this->updating) {
                $this->log('A matching Model '.$this->item['name'].' already exists and we are not updating. Skipping.');
                return;
            }

            $this->log('Updating Model');
            $editingAssetModel = true;
        } else {
            $this->log('No Matching Model, Create a new one');
            $assetModel = new AssetModel();
        }

        // Pull the records from the CSV to determine their values
        $this->item['name'] = trim($this->findCsvMatch($row, 'name'));
        $this->item['category'] = trim($this->findCsvMatch($row, 'category'));
        $this->item['manufacturer'] = trim($this->findCsvMatch($row, 'manufacturer'));
        $this->item['min_amt'] = trim($this->findCsvMatch($row, 'min_amt'));
        $this->item['model_number'] = trim($this->findCsvMatch($row, 'model_number'));
        $this->item['eol'] = trim($this->findCsvMatch($row, 'eol'));
        $this->item['notes'] = trim($this->findCsvMatch($row, 'notes'));
        $this->item['fieldset'] = trim($this->findCsvMatch($row, 'fieldset'));
        $this->item['depreciation'] = trim($this->findCsvMatch($row, 'depreciation'));
        $this->item['requestable'] = trim(($this->fetchHumanBoolean($this->findCsvMatch($row, 'requestable'))) == 1) ? 1 : 0;
        $this->item['require_serial'] = trim(($this->fetchHumanBoolean($this->findCsvMatch($row, 'require_serial'))) == 1) ? 1 : 0;

        if (!empty($this->item['category'])) {
            if ($category = $this->createOrFetchCategory($this->item['category'])) {
                $this->item['category_id'] = $category;
            }
        }
        if (!empty($this->item['manufacturer'])) {
            if ($manufacturer = $this->createOrFetchManufacturer($this->item['manufacturer'])) {
                $this->item['manufacturer_id'] = $manufacturer;
            }
        }

        if (!empty($this->item['depreciation'])) {
            if ($depreciation = $this->fetchDepreciation($this->item['depreciation'])) {
                $this->item['depreciation_id'] = $depreciation;
            }
        }

        if (!empty($this->item['fieldset'])) {
            if ($fieldset = $this->createOrFetchCustomFieldset($this->item['fieldset'])) {
                $this->item['fieldset_id'] = $fieldset;
            }
        }

        Log::debug('Item array is: ');
        Log::debug(print_r($this->item, true));


        if ($editingAssetModel) {
            Log::debug('Updating existing model');
            $assetModel->update($this->sanitizeItemForUpdating($assetModel));
        } else {
            Log::debug('Creating model');
            $assetModel->fill($this->sanitizeItemForStoring($assetModel));
            $assetModel->created_by = auth()->id();
        }

        if ($assetModel->save()) {
            $this->log('AssetModel '.$assetModel->name.' created or updated from CSV import');
            return $assetModel;

        } else {
            $this->log($assetModel->getErrors()->first());
            $this->addErrorToBag($assetModel,  $assetModel->getErrors()->keys()[0], $assetModel->getErrors()->first());
            return $assetModel->getErrors();
        }

    }


    /**
     * Fetch an existing depreciation, or create new if it doesn't exist.
     *
     * We only do a fetch vs create here since Depreciations have additional fields required
     * and cannot be created without them (months, for example.))
     *
     * @author A. Gianotto
     * @since 7.1.3
     * @param $depreciation_name string
     * @return int id of depreciation created/found
     */
    public function fetchDepreciation($depreciation_name) : ?int
    {
        if ($depreciation_name != '') {

            if ($depreciation = Depreciation::where('name', '=', $depreciation_name)->first()) {
                $this->log('A matching Depreciation '.$depreciation_name.' already exists');
                return $depreciation->id;
            }
        }

        return null;
    }

    /**
     * Fetch an existing fieldset, or create new if it doesn't exist
     *
     * @author A. Gianotto
     * @since 7.1.3
     * @param $fieldset_name string
     * @return int id of fieldset created/found
     */
    public function createOrFetchCustomFieldset($fieldset_name) : ?int
    {
        if ($fieldset_name != '') {
            $fieldset = CustomFieldset::where('name', '=', $fieldset_name)->first();

            if ($fieldset) {
                $this->log('A matching fieldset '.$fieldset_name.' already exists');
                return $fieldset->id;
            }

            $fieldset = new CustomFieldset();
            $fieldset->name = $fieldset_name;

            if ($fieldset->save()) {
                $this->log('Fieldset '.$fieldset_name.' was created');

                return $fieldset->id;
            }
            $this->logError($fieldset, 'Fieldset');
        }

        return null;
    }
}