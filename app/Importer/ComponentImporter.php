<?php

namespace App\Importer;

use App\Models\Asset;
use App\Models\Component;

class ComponentImporter extends ItemImporter
{
    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        // ComponentImporter deliberately does NOT call parent::handle(). See
        // LicenseImporter / AssetImporter / AccessoryImporter / ConsumableImporter
        // for the same pattern: absent CSV columns stay out of $this->item so
        // update mode preserves the DB value, and present-but-empty cells land
        // as null so update mode clears the DB value. The base sanitize's
        // reject-empty pass is suppressed via the sanitizeItemForStoring
        // override below.
        $this->item = [];

        // Shared lookup fields. Present-and-empty clears the FK; absent
        // preserves it; present-and-set resolves and stores the id.
        // department + manager kept as side-effect calls even though they
        // are not in Component's fillable, in case a future subclass hook
        // starts using them (parity with the other importers).
        foreach ([
            ['category_id', 'category', fn ($v) => $this->createOrFetchCategory($v)],
            ['company_id', 'company', fn ($v) => $this->createOrFetchCompany($v)],
            ['location_id', 'location', fn ($v) => $this->createOrFetchLocation($v)],
            ['manufacturer_id', 'manufacturer', fn ($v) => $this->createOrFetchManufacturer($v)],
            ['supplier_id', 'supplier', fn ($v) => $this->createOrFetchSupplier($v)],
            ['department_id', 'department', fn ($v) => $this->createOrFetchDepartment($v)],
        ] as [$itemKey, $csvKey, $resolver]) {
            if ($this->csvRowHas($row, $csvKey)) {
                $value = $this->findCsvMatch($row, $csvKey);
                $this->item[$itemKey] = ($value !== '') ? $resolver($value) : null;
            }
        }

        if ($this->csvRowHas($row, 'manager_first_name')) {
            $first = $this->findCsvMatch($row, 'manager_first_name');
            $last = $this->findCsvMatch($row, 'manager_last_name');
            $this->item['manager_id'] = ($first !== '') ? $this->fetchManager($first, $last) : null;
        }

        $this->setItemFromCsvIfPresent($row, 'name', 'item_name');
        $this->setItemFromCsvIfPresent($row, 'notes');
        // order_number is not on the Component model any more — recorded
        // as an Order + OrderItem via recordOrderForImportedRow() after
        // the create-branch save below.
        $this->setItemFromCsvIfPresent($row, 'purchase_cost');
        $this->setItemFromCsvIfPresent($row, 'model_number');
        $this->setItemFromCsvIfPresent($row, 'min_amt');
        $this->setItemFromCsvIfPresent($row, 'qty', 'quantity');
        $this->setItemFromCsvIfPresent($row, 'serial');
        // Persist the requestable flag on both create + update, matching
        // the accessory / consumable importers now that Component is a
        // first-class Requestable. Component::setRequestableAttribute
        // normalizes "0"/"1"/"true"/"false"/"" so any of the common CSV
        // shapes lands correctly.
        $this->setItemFromCsvIfPresent($row, 'requestable');
        $this->setItemFromCsvIfPresent($row, 'archived');

        // asset_tag is not in Component's fillable but is used by the
        // checkout-to-asset path in createComponentIfNotExists.
        $this->setItemFromCsvIfPresent($row, 'asset_tag');

        if ($this->csvRowHas($row, 'purchase_date')) {
            $raw = $this->findCsvMatch($row, 'purchase_date');
            if ($raw !== '') {
                $this->item['purchase_date'] = $raw;
                $this->item['purchase_date'] = $this->parseOrNullDate('purchase_date');
            } else {
                $this->item['purchase_date'] = null;
            }
        }

        // See ConsumableImporter::handle for the default_* mirror rationale.
        if (array_key_exists('supplier_id', $this->item)) {
            $this->item['default_supplier_id'] = $this->item['supplier_id'];
        }
        if (array_key_exists('purchase_cost', $this->item)) {
            $this->item['default_purchase_cost'] = $this->item['purchase_cost'];
        }

        $this->item['created_by'] = $this->created_by;

        $this->createComponentIfNotExists($row);
    }

    /**
     * Create a component if a duplicate does not exist
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     */
    public function createComponentIfNotExists($row = null)
    {
        $name = trim($this->item['name'] ?? '');
        $serial = trim($this->item['serial'] ?? '');

        $this->log('Creating Component');
        $component = Component::where('name', $name)
            ->where('serial', $serial)
            ->first();

        if ($component) {
            $this->log('A matching Component '.$name.' with serial '.$serial.' already exists.  ');
            if (! $this->updating) {
                $this->log('Skipping Component');
                $this->recordSkipped();

                return;
            }
            $this->log('Updating Component');
            // qty routes through adjustQuantity so a CSV qty change
            // becomes a QuantityAdjust log entry, matching the API
            // update contract.
            $this->applyUpdateWithQtyAdjust($component, $this->sanitizeItemForUpdating($component));
            $component->setImported(true);
            $this->recordUpdated();

            return;
        }
        $this->log('No matching component, creating one');
        $component = new Component;
        $component->created_by = $this->created_by;
        $component->fill($this->sanitizeItemForStoring($component));

        // This sets an attribute on the Loggable trait for the action log
        $component->setImported(true);
        if ($component->save()) {
            $this->log('Component '.$name.' was created');
            $this->recordCreated();
            if ($row !== null) {
                $this->recordOrderForImportedRow($component, $row);
            }

            // If we have an asset tag, checkout to that asset.
            if (! empty($this->item['asset_tag']) && ($asset = Asset::where('asset_tag', $this->item['asset_tag'])->first())) {
                if (! $component->canCheckoutTo($asset)) {
                    $this->log(trans('general.error_checkout_company_mismatch', [
                        'item' => trans('general.component').' "'.$component->name.'"',
                        'item_company' => $component->company?->name ?? trans('general.unassigned'),
                        'target' => trans('general.asset').' "'.$asset->display_name.'"',
                    ]));
                } else {
                    $component->assets()->attach($component->id, [
                        'component_id' => $component->id,
                        'created_by' => $this->created_by,
                        'created_at' => date('Y-m-d H:i:s'),
                        'assigned_qty' => 1, // Only assign the first one to the asset
                        'asset_id' => $asset->id,
                    ]);
                }
            }

            return;
        }
        $this->recordErrored();
        $this->logError($component, 'Component');
    }
}
