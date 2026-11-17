<?php

namespace App\Importer;

use App\Events\CheckoutableCheckedOut;
use App\Models\Consumable;
use App\Models\User;

class ConsumableImporter extends ItemImporter
{
    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        // ConsumableImporter deliberately does NOT call parent::handle(). See
        // LicenseImporter / AssetImporter / AccessoryImporter for the same
        // pattern: absent CSV columns stay out of $this->item so update mode
        // preserves the DB value, and present-but-empty cells land as null
        // so update mode clears the DB value. The base sanitize's reject-empty
        // pass is disabled by $rejectEmptyOnUpdate on ItemImporter.
        $this->item = [];

        // Shared lookup fields. Present-and-empty clears the FK; absent
        // preserves it; present-and-set resolves and stores the id.
        // department + manager kept as side-effect calls even though they
        // are not in Consumable's fillable, so createOrFetchUser can find
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
        // order_number is not on the Consumable model any more — recorded
        // as an Order + OrderItem via recordOrderForImportedRow() after
        // the create-branch save below.
        $this->setItemFromCsvIfPresent($row, 'purchase_cost');
        $this->setItemFromCsvIfPresent($row, 'model_number');
        $this->setItemFromCsvIfPresent($row, 'min_amt');
        $this->setItemFromCsvIfPresent($row, 'qty', 'quantity');
        $this->setItemFromCsvIfPresent($row, 'requestable');
        $this->setItemFromCsvIfPresent($row, 'archived');
        $this->setItemFromCsvIfPresent($row, 'item_no');

        if ($this->csvRowHas($row, 'purchase_date')) {
            $raw = $this->findCsvMatch($row, 'purchase_date');
            $this->item['purchase_date'] = null;
            if ($raw !== '') {
                $this->item['purchase_date'] = $raw;
                $this->item['purchase_date'] = $this->parseOrNullDate('purchase_date');
            }
        }

        // Mirror supplier_id / purchase_cost into the parent's
        // default_* template fields so future orders pre-populate from
        // the last-known-good CSV import. See Consumable::$fillable and
        // ItemImporter::recordOrderForImportedRow for the split — Order
        // rows still receive supplier_id / purchase_cost via that helper
        // (that's the per-acquisition record); default_* here is the
        // per-parent template.
        if (array_key_exists('supplier_id', $this->item)) {
            $this->item['default_supplier_id'] = $this->item['supplier_id'];
        }
        if (array_key_exists('purchase_cost', $this->item)) {
            $this->item['default_purchase_cost'] = $this->item['purchase_cost'];
        }

        // Internal signals for the checkout logic; neither is fillable on
        // Consumable so sanitize's fillable filter drops them.
        $this->item['checkout_class'] = $this->findCsvMatch($row, 'checkout_class');
        $this->item['checkout_target'] = $this->determineCheckout($row);
        $this->item['created_by'] = $this->created_by;

        $this->createConsumableIfNotExists($row);
    }

    /**
     * Create a consumable if a duplicate does not exist
     *
     * @author Daniel Melzter
     *
     * @param  array  $row  CSV Row Being parsed.
     *
     * @since 3.0
     */
    public function createConsumableIfNotExists($row)
    {
        $name = trim($this->item['name'] ?? '');
        $consumable = Consumable::where('name', $name)->first();
        if ($consumable) {

            if (! $this->updating) {
                $this->log('A matching Consumable '.$name.' already exists.  ');
                $this->recordSkipped();

                $this->maybeCheckoutConsumable($consumable);

                return;
            }
            $this->log('Updating Consumable');
            // qty routes through adjustQuantity so a CSV qty change
            // becomes a QuantityAdjust log entry, matching the API
            // update contract.
            $this->applyUpdateWithQtyAdjust($consumable, $this->sanitizeItemForUpdating($consumable));
            $consumable->setImported(true);
            $this->recordUpdated();

            $this->maybeCheckoutConsumable($consumable);

            return;
        }

        $this->log('No matching consumable, creating one');
        $consumable = new Consumable;
        $consumable->created_by = $this->created_by;
        $consumable->fill($this->sanitizeItemForStoring($consumable));

        // This sets an attribute on the Loggable trait for the action log
        $consumable->setImported(true);
        if ($consumable->save()) {
            $this->log('Consumable '.$name.' was created');
            $this->recordCreated();
            $this->recordOrderForImportedRow($consumable, $row);

            $this->maybeCheckoutConsumable($consumable);

            return;
        }
        $this->recordErrored();
        $this->logError($consumable, 'Consumable');
    }

    /**
     * Consumables can only be checked out to users (schema constraint -
     * consumables_users.assigned_to is a plain FK to users, no
     * assigned_type). Silently skip if the CSV row's checkout target
     * resolves to a Location. Attaches a single unit and fires
     * CheckoutableCheckedOut so the actionlog / notification path
     * matches a UI-driven checkout.
     */
    private function maybeCheckoutConsumable(Consumable $consumable): void
    {
        $target = $this->item['checkout_target'] ?? null;
        if (! $target || ! ($target instanceof User)) {
            return;
        }

        if ($consumable->numRemaining() < 1) {
            $this->log('Consumable '.$consumable->name.' has no free units - skipping checkout');

            return;
        }

        if (! $consumable->canCheckoutTo($target)) {
            $this->log(trans('general.error_checkout_company_mismatch', [
                'item' => trans('general.consumable').' "'.$consumable->name.'"',
                'item_company' => $consumable->company?->name ?? trans('general.unassigned'),
                'target' => ($target->name ?? $target->username ?? $target->id),
            ]));

            return;
        }

        $consumable->users()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'created_by' => $this->created_by,
            'assigned_to' => $target->id,
            'note' => 'Checkout from CSV Importer',
        ]);

        event(new CheckoutableCheckedOut(
            $consumable,
            $target,
            auth()->user(),
            'Checkout from CSV Importer',
            [],
            1,
            false,
        ));

        $this->log('Consumable '.$consumable->name.' checked out to '.($target->username ?? $target->id));
        $this->maybeSendWelcomeEmail($target);
    }
}
