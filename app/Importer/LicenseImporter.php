<?php

namespace App\Importer;

use App\Models\Asset;
use App\Models\License;
use Illuminate\Support\Facades\Auth;

class LicenseImporter extends ItemImporter
{
    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        // ItemImporter handles the general fetching.
        parent::handle($row);
        $this->createLicenseIfNotExists($row);
    }

    /**
     * Create the license if it does not exist.
     *
     * @author Daniel Melzter
     * @since 4.0
     * @param array $row
     * @return License|mixed|null
     * updated @author Jes Vinsmoke
     * @since 6.1
     *
     */
    public function createLicenseIfNotExists(array $row)
    {
        $editingLicense = false;
        $license = License::where('serial', $this->item['serial'])->where('name', $this->item['name'])
                    ->first();
        if ($license) {
            if (! $this->updating) {

                if($this->item['serial'] != "") {
                    $this->log('A matching License ' . $this->item['name'] . ' with serial ' . $this->item['serial'] . ' already exists');
                }
                else {
                    $this->log('A matching License ' . $this->item['name'] . ' with no serial number already exists');
                }

                return;
            }

            $this->log('Updating License');
            $editingLicense = true;
        } else {
            $this->log('No Matching License, Creating a new one');
            $license = new License;
        }
        $asset_tag = $this->item['asset_tag'] = trim($this->findCsvMatch($row, 'asset_tag')); // used for checkout out to an asset.

        $this->item["expiration_date"] = null;
        if ($this->findCsvMatch($row, "expiration_date")!='') {
            $this->item["expiration_date"] = date("Y-m-d 00:00:01", strtotime(trim($this->findCsvMatch($row, "expiration_date"))));
        }
        $this->item['license_email'] = trim($this->findCsvMatch($row, 'license_email'));
        $this->item['license_name'] = trim($this->findCsvMatch($row, 'license_name'));
        $this->item['maintained'] = trim($this->findCsvMatch($row, 'maintained'));
        $this->item['purchase_order'] = trim($this->findCsvMatch($row, 'purchase_order'));
        $this->item['order_number'] = trim($this->findCsvMatch($row, 'order_number'));
        $this->item['reassignable'] = trim($this->findCsvMatch($row, 'reassignable'));
        $this->item['manufacturer'] = $this->createOrFetchManufacturer(trim($this->findCsvMatch($row, 'manufacturer')));
        $this->item['min_amt'] = trim($this->findCsvMatch($row, 'min_amt'));

        if($this->item['reassignable'] == "")
        {
            $this->item['reassignable'] = 1;
        }
        $this->item['seats'] = $this->findCsvMatch($row, 'seats');
        
        $this->item["termination_date"] = null;
        if ($this->findCsvMatch($row, "termination_date")!='') {
            $this->item["termination_date"] = date("Y-m-d 00:00:01", strtotime($this->findCsvMatch($row, "termination_date")));
        }

        if ($editingLicense) {
            $license->update($this->sanitizeItemForUpdating($license));
        } else {
            $license->fill($this->sanitizeItemForStoring($license));
            $license->created_by = auth()->id();
        }

        // This sets an attribute on the Loggable trait for the action log
        $license->setImported(true);
        if ($license->save()) {
            $this->log('License '.$this->item['name'].' with serial number '.$this->item['serial'].' was created');

            // Lets try to checkout seats if the fields exist and we have seats.
            if ($license->seats > 0) {
                $checkout_target = $this->item['checkout_target'];
                $asset = Asset::where('asset_tag', $asset_tag)->first();
                $targetLicense = $license->freeSeat();

                if (is_null($targetLicense)){
                    return;
                }

                if ($checkout_target) {
                    $targetLicense->assigned_to = $checkout_target->id;
                    $targetLicense->created_by = auth()->id();
                    if ($asset) {
                        $targetLicense->asset_id = $asset->id;
                    }
                    $targetLicense->save();
                } elseif ($asset) {
                    $targetLicense->created_by = auth()->id();
                    $targetLicense->asset_id = $asset->id;
                    $targetLicense->save();
                }
            }

            return;
        }
        $this->logError($license, 'License "'.$this->item['name'].'"');
    }
}
