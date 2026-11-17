<?php

namespace App\Importer;

use App\Events\CheckoutableCheckedOut;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;

class AccessoryImporter extends ItemImporter
{
    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        // AccessoryImporter deliberately does NOT call parent::handle(). See
        // LicenseImporter / AssetImporter for the same pattern: absent CSV
        // columns are left out of $this->item so update mode preserves the
        // DB value, and present-but-empty cells land as null so update mode
        // clears the DB value. The base sanitize's reject-empty pass is
        // disabled by $rejectEmptyOnUpdate on ItemImporter.
        $this->item = [];

        // Shared lookup fields. Present-and-empty clears the FK; absent
        // preserves it; present-and-set resolves and stores the id.
        // department + manager kept as side-effect calls even though they
        // are not in Accessory's fillable, so createOrFetchUser can find
        // an auto-created department when it mints a checkout-target user.
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
        // order_number is not on the Accessory model any more — recorded
        // as an Order + OrderItem via recordOrderForImportedRow() after
        // the create-branch save below.
        $this->setItemFromCsvIfPresent($row, 'purchase_cost');
        $this->setItemFromCsvIfPresent($row, 'model_number');
        $this->setItemFromCsvIfPresent($row, 'min_amt');
        $this->setItemFromCsvIfPresent($row, 'qty', 'quantity');
        $this->setItemFromCsvIfPresent($row, 'requestable');
        $this->setItemFromCsvIfPresent($row, 'archived');

        if ($this->csvRowHas($row, 'image')) {
            $raw = $this->findCsvMatch($row, 'image');
            $this->item['image'] = ($raw !== '') ? basename($raw) : null;
        }

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

        // Internal signals used by the checkout logic below; neither is
        // fillable on Accessory so sanitize's fillable filter drops them.
        $this->item['checkout_class'] = $this->findCsvMatch($row, 'checkout_class');
        $this->item['checkout_target'] = $this->determineCheckout($row);
        $this->item['created_by'] = $this->created_by;

        $this->createAccessoryIfNotExists($row);
    }

    /**
     * Create an accessory if a duplicate does not exist
     *
     * @author Daniel Melzter
     *
     * @since 3.0
     */
    public function createAccessoryIfNotExists($row)
    {
        $name = $this->item['name'] ?? '';
        $accessory = Accessory::where('name', $name)->first();
        if ($accessory) {
            if (! $this->updating) {
                $this->log('A matching Accessory '.$name.' already exists.  ');
                $this->recordSkipped();

                $this->maybeCheckoutAccessory($accessory);

                return;
            }

            $this->log('Updating Accessory');
            $this->item['model_number'] = trim($this->findCsvMatch($row, 'model_number'));
            // qty routes through adjustQuantity so a CSV qty change
            // becomes a QuantityAdjust log entry, matching the API
            // update contract. applyUpdateWithQtyAdjust calls
            // $model->update() internally (already saves), so no
            // additional save() is needed even with Model::unguard()
            // active — supersedes the develop cleanup that dropped the
            // redundant post-update save().
            $this->applyUpdateWithQtyAdjust($accessory, $this->sanitizeItemForUpdating($accessory));
            $accessory->setImported(true);
            $this->recordUpdated();

            $this->maybeCheckoutAccessory($accessory);

            return;
        }
        $this->log('No Matching Accessory, Creating a new one');
        $accessory = new Accessory;
        $accessory->created_by = $this->created_by;
        $accessory->fill($this->sanitizeItemForStoring($accessory));

        // This sets an attribute on the Loggable trait for the action log
        $accessory->setImported(true);
        if ($accessory->save()) {
            $this->log('Accessory '.$name.' was created');
            $this->recordCreated();
            $this->recordOrderForImportedRow($accessory, $row);

            $this->maybeCheckoutAccessory($accessory);

            return;
        }
        $this->recordErrored();
        $this->logError($accessory, 'Accessory');
    }

    /**
     * If the CSV row identified a checkout target (User or Location via
     * ItemImporter::determineCheckout), attach one AccessoryCheckout row
     * and fire the standard CheckoutableCheckedOut event so the actionlog
     * and notification paths behave the same as a UI-driven checkout.
     * Skips silently when there's no target, no free stock, or the
     * target fails the company-match check.
     */
    private function maybeCheckoutAccessory(Accessory $accessory): void
    {
        $target = $this->item['checkout_target'] ?? null;
        if (! $target) {
            return;
        }

        if ($accessory->numRemaining() < 1) {
            $this->log('Accessory '.$accessory->name.' has no free units - skipping checkout');

            return;
        }

        if (! $accessory->canCheckoutTo($target)) {
            $this->log(trans('general.error_checkout_company_mismatch', [
                'item' => trans('general.accessory').' "'.$accessory->name.'"',
                'item_company' => $accessory->company?->name ?? trans('general.unassigned'),
                'target' => ($target->name ?? $target->username ?? $target->id),
            ]));

            return;
        }

        $checkout = new AccessoryCheckout([
            'accessory_id' => $accessory->id,
            'assigned_to' => $target->id,
            'assigned_type' => $target::class,
            'note' => 'Checkout from CSV Importer',
        ]);
        $checkout->created_by = $this->created_by;

        if ($checkout->save()) {
            event(new CheckoutableCheckedOut(
                $accessory,
                $target,
                auth()->user(),
                'Checkout from CSV Importer',
                [],
                1,
                false,
            ));
            $this->log('Accessory '.$accessory->name.' checked out to '.($target->name ?? $target->username ?? $target->id));
            $this->maybeSendWelcomeEmail($target);
        } else {
            $this->logError($checkout, 'AccessoryCheckout');
        }
    }
}
