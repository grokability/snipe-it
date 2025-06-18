<?php

namespace App\Importer;

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;
use App\Events\CheckoutableCheckedIn;
use Illuminate\Support\Facades\Crypt;

class AssetImporter extends ItemImporter
{
    protected $defaultStatusLabelId;

    public function __construct($filename)
    {
        parent::__construct($filename);

        $this->defaultStatusLabelId = Statuslabel::first()?->id;

        if (!is_null(Statuslabel::deployable()->first())) {
            $this->defaultStatusLabelId = Statuslabel::deployable()->first()?->id;
        }

        if (is_null($this->defaultStatusLabelId)) {
            $defaultLabel = Statuslabel::create([
                'name' => 'Default Status',
                'deployable' => 0,
                'pending' => 1,
                'archived' => 0,
                'notes' => 'Default status label created by AssetImporter',
            ]);

            $this->defaultStatusLabelId = $defaultLabel->id;
        }
    }

    protected function handle($row)
    {
        // ItemImporter handles the general fetching.
        parent::handle($row);

        if ($this->customFields) {
            foreach ($this->customFields as $customField) {
                $customFieldValue = $this->array_smart_custom_field_fetch($row, $customField);

                if ($customFieldValue) {
                    if ($customField->field_encrypted == 1) {
                        $this->item['custom_fields'][$customField->db_column_name()] = Crypt::encrypt($customFieldValue);
                        $this->log('Custom Field '.$customField->name.': '.Crypt::encrypt($customFieldValue));
                    } else {
                        $this->item['custom_fields'][$customField->db_column_name()] = $customFieldValue;
                        $this->log('Custom Field '.$customField->name.': '.$customFieldValue);
                    }
                } else {
                    // Clear out previous data.
                    $this->item['custom_fields'][$customField->db_column_name()] = null;
                }
            }
        }


        $this->createAssetIfNotExists($row);
    }

    /**
     * Create the asset if it does not exist.
     *
     * @author Daniel Melzter
     * @since 3.0
     * @param array $row
     * @return Asset|mixed|null
     */
    public function createAssetIfNotExists(array $row)
    {
        $editingAsset = false;
        $asset_tag = $this->findCsvMatch($row, 'asset_tag');

        if (empty($asset_tag)){
            $asset_tag = Asset::autoincrement_asset();
        }



        if ($this->findCsvMatch($row, 'id')!='') {
            // Override asset if an ID was given
            \Log::debug('Finding asset by ID: '.$this->findCsvMatch($row, 'id'));
            $asset = Asset::find($this->findCsvMatch($row, 'id'));
        } else {
            $asset = Asset::where(['asset_tag'=> (string) $asset_tag])->first();
        }
        
        if ($asset) {
            if (! $this->updating) {
                $exists_error = trans('general.import_asset_tag_exists', ['asset_tag' => $asset_tag]);
                $this->log($exists_error);
                $this->addErrorToBag($asset, 'asset_tag', $exists_error);
                return $exists_error;
            }

            $this->log('Updating Asset');
            $editingAsset = true;
        } else {
            $this->log('No Matching Asset, Creating a new one');
            $asset = new Asset;
        }

        // If no status ID is found
        if (! array_key_exists('status_id', $this->item) && ! $editingAsset) {
            $this->log('No status ID field found, defaulting to first deployable status label.');
            $this->item['status_id'] = $this->defaultStatusLabelId;
        }

        $this->item['notes'] = trim($this->findCsvMatch($row, 'asset_notes'));
        $this->item['image'] = trim($this->findCsvMatch($row, 'image'));
        $this->item['requestable'] = trim(($this->fetchHumanBoolean($this->findCsvMatch($row, 'requestable'))) == 1) ? '1' : 0;
        $asset->requestable = $this->item['requestable'];
        $this->item['warranty_months'] = intval(trim($this->findCsvMatch($row, 'warranty_months')));
        $this->item['model_id'] = $this->createOrFetchAssetModel($row);
        $this->item['byod'] = ($this->fetchHumanBoolean(trim($this->findCsvMatch($row, 'byod'))) == 1) ? '1' : 0;
        $this->item['last_checkin'] = trim($this->findCsvMatch($row, 'last_checkin'));
        $this->item['last_checkout'] = trim($this->findCsvMatch($row, 'last_checkout'));
        $this->item['expected_checkin'] = trim($this->findCsvMatch($row, 'expected_checkin'));
        $this->item['last_audit_date'] = trim($this->findCsvMatch($row, 'last_audit_date'));
        $this->item['next_audit_date'] = trim($this->findCsvMatch($row, 'next_audit_date'));
        $this->item['asset_eol_date'] = trim($this->findCsvMatch($row, 'asset_eol_date'));
        $this->item['asset_tag'] = $asset_tag;

        // We need to save the user if it exists so that we can checkout to user later.
        // Sanitizing the item will remove it.
        if (array_key_exists('checkout_target', $this->item)) {
            $target = $this->item['checkout_target'];
        }

        $item = $this->sanitizeItemForStoring($asset, $editingAsset);

        // The location id fetched by the csv reader is actually the rtd_location_id.
        // This will also set location_id, but then that will be overridden by the
        // checkout method if necessary below.
        if (isset($this->item['location_id'])) {
            $item['rtd_location_id'] = $this->item['location_id'];
        }


        /**
         * We use this to backdate the checkin action further down
         */
        $checkin_date = date('Y-m-d H:i:s');
        if ($this->item['last_checkin']!='') {
            $item['last_checkin'] = $this->parseOrNullDate('last_checkin', 'datetime');
            $checkout_date = $this->item['last_checkin'];
        }

        /**
         * We use this to backdate the checkout action further down
         */
        $checkout_date = date('Y-m-d H:i:s');
        if ($this->item['last_checkout']!='') {
            $item['last_checkout'] = $this->parseOrNullDate('last_checkout', 'datetime');
            $checkout_date = $this->item['last_checkout'];
        }

        if ($this->item['expected_checkin']!='') {
            $item['expected_checkin'] = $this->parseOrNullDate('expected_checkin');
        }

        if ($this->item['last_audit_date']!='') {
            $item['last_audit_date'] = $this->parseOrNullDate('last_audit_date');
        }

        if ($this->item['next_audit_date']!='') {
            $item['next_audit_date'] = $this->parseOrNullDate('next_audit_date');
        }

        if ($this->item['asset_eol_date']!='') {
            $item['asset_eol_date'] = $this->parseOrNullDate('asset_eol_date');
        }


        if ($editingAsset) {
            $asset->update($item);
        } else {
            $asset->fill($item);
        }

        // If we're updating, we don't want to overwrite old fields.
        if (array_key_exists('custom_fields', $this->item)) {
            foreach ($this->item['custom_fields'] as $custom_field => $val) {
                $asset->{$custom_field} = $val;
            }
        }

        // This sets an attribute on the Loggable trait for the action log
        $asset->setImported(true);

        if ($asset->save()) {

            $this->log('Asset '.$this->item['name'].' with serial number '.$this->item['serial'].' was created');

            // If we have a target to checkout to, lets do so.
            //-- created_by is a property of the abstract class Importer, which this class inherits from and it's set by
            //-- the class that needs to use it (command importer or GUI importer inside the project).
            if (isset($target) && ($target !== false)) {
                if (!is_null($asset->assigned_to)){
                    if ($asset->assigned_to != $target->id) {
                        event(new CheckoutableCheckedIn($asset, User::find($asset->assigned_to), auth()->user(), 'Checkin from CSV Importer', $checkin_date));
                    }
                }

                $asset->fresh()->checkOut($target, $this->created_by, $checkout_date, null, 'Checkout from CSV Importer',  $asset->name);
            }

            return;
        }
        $this->logError($asset, 'Asset "'.$this->item['name'].'"');
    }


}
