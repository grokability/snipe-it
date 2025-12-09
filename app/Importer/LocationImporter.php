<?php

namespace App\Importer;

use App\Models\Location;
use Illuminate\Support\Facades\Log;

/**
 * When we are importing users via an Asset/etc import, we use createOrFetchUser() in
 * Importer\Importer.php. [ALG]
 *
 * Class LocationImporter
 */
class LocationImporter extends ItemImporter
{
    protected $locations;

    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        parent::handle($row);
        $this->createLocationIfNotExists($row);
    }

    /**
     * Create a location if a duplicate does not exist.
     * @todo Investigate how this should interact with Importer::createLocationIfNotExists
     *
     * @author A. Gianotto
     * @since 6.1.0
     * @param array $row
     */
    public function createLocationIfNotExists(array $row)
    {

        $editingLocation = false;

        $location = Location::where('name', '=', $this->findCsvMatch($row, 'name'))->first();

        if ($this->findCsvMatch($row, 'id')!='') {
            // Override location if an ID was given
            \Log::debug('Finding location by ID: '.$this->findCsvMatch($row, 'id'));
            $location = Location::find($this->findCsvMatch($row, 'id'));
        }


        if ($location) {
            if (! $this->updating) {
                $this->log('A matching Location '.$this->item['name'].' already exists');
                return;
            }

            $this->log('Updating Location');
            $editingLocation = true;
        } else {
            $this->log('No Matching Location, Create a new one');
            $location = new Location;
            $location->created_by = auth()->id();
        }

        // Pull the records from the CSV to determine their values
        $this->item['name'] = trim($this->findCsvMatch($row, 'name'));
        $this->item['address'] = trim($this->findCsvMatch($row, 'address'));
        $this->item['address2'] = trim($this->findCsvMatch($row, 'address2'));
        $this->item['city'] = trim($this->findCsvMatch($row, 'city'));
        $this->item['state'] = trim($this->findCsvMatch($row, 'state'));
        $this->item['country'] = trim($this->findCsvMatch($row, 'country'));
        $this->item['zip'] = trim($this->findCsvMatch($row, 'zip'));
        $this->item['currency'] = trim($this->findCsvMatch($row, 'currency'));
        $this->item['ldap_ou'] = trim($this->findCsvMatch($row, 'ldap_ou'));
        $this->item['manager'] = trim($this->findCsvMatch($row, 'manager'));
        $this->item['manager_username'] = trim($this->findCsvMatch($row, 'manager_username'));
        $this->item['notes'] = trim($this->findCsvMatch($row, 'notes'));
        $this->item['tag_color'] = trim($this->findCsvMatch($row, 'tag_color'));


        if ($this->findCsvMatch($row, 'parent_location')) {
            $this->item['parent_id'] = $this->createOrFetchLocation(trim($this->findCsvMatch($row, 'parent_location')));
        }

        if (!empty($this->item['manager'])) {
            if ($manager = $this->createOrFetchUser($row, 'manager')) {
                $this->item['manager_id'] = $manager->id;
            }
        }

        Log::debug('Item array is: ');
        Log::debug(print_r($this->item, true));


        if ($editingLocation) {
            Log::debug('Updating existing location');
            $location->update($this->sanitizeItemForUpdating($location));
        } else {
            Log::debug('Creating location');
            if ($this->findCsvMatch($row, 'company')) {
                $this->item['company_id'] = $this->createOrFetchCompany(trim($this->findCsvMatch($row, 'company')));
            }
            $location->fill($this->sanitizeItemForStoring($location));
        }

        if ($location->save()) {
            $this->log('Location '.$location->name.' created or updated from CSV import');
            return $location;

        } else {
            Log::debug($location->getErrors());
            $this->logError($location, 'Location "'.$this->item['name'].'"');
            return $location->errors;
        }


    }
}