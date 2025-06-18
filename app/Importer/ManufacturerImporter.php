<?php

namespace App\Importer;

use App\Models\Manufacturer;
use Illuminate\Support\Facades\Log;

/**
 * When we are importing users via an Asset/etc import, we use createOrFetchUser() in
 * Importer\Importer.php. [ALG]
 *
 * Class ManufacturerImporter
 */
class ManufacturerImporter extends ItemImporter
{
    protected $manufacturers;

    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        parent::handle($row);
        $this->createManufacturerIfNotExists($row);
    }

    /**
     * Create a supplier if a duplicate does not exist.
     * @todo Investigate how this should interact with Importer::createManufacturerIfNotExists
     *
     * @author A. Gianotto
     * @since 6.1.0
     * @param array $row
     */
    public function createManufacturerIfNotExists(array $row)
    {

        $editingManufacturer = false;

        $supplier = Manufacturer::where('name', '=', $this->findCsvMatch($row, 'name'))->first();

        if ($this->findCsvMatch($row, 'id')!='') {
            // Override supplier if an ID was given
            \Log::debug('Finding supplier by ID: '.$this->findCsvMatch($row, 'id'));
            $supplier = Manufacturer::find($this->findCsvMatch($row, 'id'));
        }


        if ($supplier) {
            if (! $this->updating) {
                $this->log('A matching Manufacturer '.$this->item['name'].' already exists');
                return;
            }

            $this->log('Updating Manufacturer');
            $editingManufacturer = true;
        } else {
            $this->log('No Matching Manufacturer, Create a new one');
            $supplier = new Manufacturer;
            $supplier->created_by = auth()->id();
        }

        // Pull the records from the CSV to determine their values
        $this->item['name'] = trim($this->findCsvMatch($row, 'name'));
        $this->item['support_phone'] = trim($this->findCsvMatch($row, 'support_phone'));
        $this->item['fax'] = trim($this->findCsvMatch($row, 'fax'));
        $this->item['support_email'] = trim($this->findCsvMatch($row, 'support_email'));
        $this->item['contact'] = trim($this->findCsvMatch($row, 'contact'));
        $this->item['url'] = trim($this->findCsvMatch($row, 'url'));
        $this->item['support_url'] = trim($this->findCsvMatch($row, 'support_url'));
        $this->item['warranty_lookup_url'] = trim($this->findCsvMatch($row, 'warranty_lookup_url'));
        $this->item['notes'] = trim($this->findCsvMatch($row, 'notes'));


        Log::debug('Item array is: ');
        Log::debug(print_r($this->item, true));


        if ($editingManufacturer) {
            Log::debug('Updating existing supplier');
            $supplier->update($this->sanitizeItemForUpdating($supplier));
        } else {
            Log::debug('Creating supplier');
            $supplier->fill($this->sanitizeItemForStoring($supplier));
        }

        if ($supplier->save()) {
            $this->log('Manufacturer '.$supplier->name.' created or updated from CSV import');
            return $supplier;

        } else {
            Log::debug($supplier->getErrors());
            $this->logError($supplier, 'Manufacturer "'.$this->item['name'].'"');
            return $supplier->errors;
        }


    }
}