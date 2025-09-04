<?php

namespace App\Importer;

use App\Models\Supplier;
use Illuminate\Support\Facades\Log;

/**
 * When we are importing users via an Asset/etc import, we use createOrFetchUser() in
 * Importer\Importer.php. [ALG]
 *
 * Class SupplierImporter
 */
class SupplierImporter extends ItemImporter
{
    protected $suppliers;

    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        parent::handle($row);
        $this->createSupplierIfNotExists($row);
    }

    /**
     * Create a supplier if a duplicate does not exist.
     * @todo Investigate how this should interact with Importer::createSupplierIfNotExists
     *
     * @author A. Gianotto
     * @since 6.1.0
     * @param array $row
     */
    public function createSupplierIfNotExists(array $row)
    {

        $editingSupplier = false;

        $supplier = Supplier::where('name', '=', $this->findCsvMatch($row, 'name'))->first();

        if ($this->findCsvMatch($row, 'id')!='') {
            // Override supplier if an ID was given
            \Log::debug('Finding supplier by ID: '.$this->findCsvMatch($row, 'id'));
            $supplier = Supplier::find($this->findCsvMatch($row, 'id'));
        }


        if ($supplier) {
            if (! $this->updating) {
                $this->log('A matching Supplier '.$this->item['name'].' already exists');
                return;
            }

            $this->log('Updating Supplier');
            $editingSupplier = true;
        } else {
            $this->log('No Matching Supplier, Create a new one');
            $supplier = new Supplier;
            $supplier->created_by = auth()->id();
        }

        // Pull the records from the CSV to determine their values
        $this->item['name'] = trim($this->findCsvMatch($row, 'name'));
        $this->item['address'] = trim($this->findCsvMatch($row, 'address'));
        $this->item['address2'] = trim($this->findCsvMatch($row, 'address2'));
        $this->item['city'] = trim($this->findCsvMatch($row, 'city'));
        $this->item['state'] = trim($this->findCsvMatch($row, 'state'));
        $this->item['country'] = trim($this->findCsvMatch($row, 'country'));
        $this->item['zip'] = trim($this->findCsvMatch($row, 'zip'));
        $this->item['phone'] = trim($this->findCsvMatch($row, 'phone'));
        $this->item['fax'] = trim($this->findCsvMatch($row, 'fax'));
        $this->item['email'] = trim($this->findCsvMatch($row, 'email'));
        $this->item['contact'] = trim($this->findCsvMatch($row, 'contact'));
        $this->item['url'] = trim($this->findCsvMatch($row, 'url'));
        $this->item['notes'] = trim($this->findCsvMatch($row, 'notes'));


        Log::debug('Item array is: ');
        Log::debug(print_r($this->item, true));


        if ($editingSupplier) {
            Log::debug('Updating existing supplier');
            $supplier->update($this->sanitizeItemForUpdating($supplier));
        } else {
            Log::debug('Creating supplier');
            $supplier->fill($this->sanitizeItemForStoring($supplier));
        }

        if ($supplier->save()) {
            $this->log('Supplier '.$supplier->name.' created or updated from CSV import');
            return $supplier;

        } else {
            Log::debug($supplier->getErrors());
            $this->logError($supplier, 'Supplier "'.$this->item['name'].'"');
            return $supplier->errors;
        }


    }
}