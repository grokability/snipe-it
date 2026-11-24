<?php

namespace App\Importer;

use App\Models\Asset;
use App\Models\License;

class LicenseImporter extends ItemImporter
{
    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    protected function handle($row)
    {
        // LicenseImporter deliberately does NOT call parent::handle(). The
        // parent unconditionally assigns $this->item entries for every
        // shared field, which conflates "column absent from CSV" with
        // "column present but empty" and prevents empty CSV cells from
        // clearing existing DB values on update. LicenseImporter builds
        // $this->item exclusively via setItemFromCsvIfPresent so absent
        // columns never enter the update payload (preserving DB values)
        // and present-but-empty columns land as empty strings (clearing
        // DB values). The base sanitize's reject-empty pass is disabled
        // by $rejectEmptyOnUpdate on ItemImporter.
        $this->item = [];

        // Shared lookup fields. Present-and-empty clears the FK; absent
        // preserves it; present-and-set resolves and stores the id.
        foreach ([
            ['category_id', 'category', fn ($v) => $this->createOrFetchCategory($v)],
            ['company_id', 'company', fn ($v) => $this->createOrFetchCompany($v)],
            ['manufacturer_id', 'manufacturer', fn ($v) => $this->createOrFetchManufacturer($v)],
            ['supplier_id', 'supplier', fn ($v) => $this->createOrFetchSupplier($v)],
        ] as [$itemKey, $csvKey, $resolver]) {
            if ($this->csvRowHas($row, $csvKey)) {
                $value = $this->findCsvMatch($row, $csvKey);
                $this->item[$itemKey] = ($value !== '') ? $resolver($value) : null;
            }
        }

        // Shared straight assignments from ItemImporter's field list that
        // apply to licenses.
        $this->setItemFromCsvIfPresent($row, 'name', 'item_name');
        $this->setItemFromCsvIfPresent($row, 'notes');
        $this->setItemFromCsvIfPresent($row, 'purchase_cost');

        if ($this->csvRowHas($row, 'purchase_date')) {
            $raw = $this->findCsvMatch($row, 'purchase_date');
            if ($raw !== '') {
                $this->item['purchase_date'] = $raw;
                $this->item['purchase_date'] = $this->parseOrNullDate('purchase_date');
            } else {
                $this->item['purchase_date'] = null;
            }
        }

        // Serial and asset_tag both need special handling. serial IS a
        // License field so it goes through the helper. asset_tag is captured
        // separately for the seat-checkout logic in createLicenseIfNotExists.
        $this->setItemFromCsvIfPresent($row, 'serial');

        // checkout_class + checkout_target are internal signals for the
        // seat-checkout logic below; neither is fillable on License, so
        // sanitize's fillable filter drops them before the DB write.
        $this->item['checkout_class'] = $this->findCsvMatch($row, 'checkout_class');
        $this->item['checkout_target'] = $this->determineCheckout($row);
        $this->item['created_by'] = $this->created_by;

        $this->createLicenseIfNotExists($row);
    }

    /**
     * Create the license if it does not exist.
     *
     * @author Daniel Melzter
     *
     * @since 4.0
     *
     * @return License|mixed|null
     *                            updated @author Jes Vinsmoke
     *
     * @since 6.1
     */
    public function createLicenseIfNotExists(array $row)
    {
        // serial and name are the identity fields for the create-or-update
        // match. Both are populated in handle() via setItemFromCsvIfPresent,
        // so a CSV without those columns will produce empty keys here.
        // Fallback to empty string so the where clauses do not blow up on
        // undefined indexes; the resulting query will match nothing, and the
        // validation error path takes over on the save attempt.
        $serial = $this->item['serial'] ?? '';
        $name = $this->item['name'] ?? '';

        $editingLicense = false;

        // When the CSV row supplies a serial, match on serial + name. When
        // the row has no serial (either the column is absent from the CSV
        // or the cell is empty), match on name plus "no serial", where
        // "no serial" covers both NULL and empty-string DB values. Without
        // this null-or-empty branch, users importing a seat-assignment CSV
        // that omits the serial column would create a new License per row
        // instead of matching to the one they just created above, because
        // the fillable insert path stores absent columns as NULL while the
        // match query previously compared against ''.
        $query = License::where('name', $name);
        if ($serial !== '') {
            $query->where('serial', $serial);
        } else {
            $query->where(function ($q) {
                $q->whereNull('serial')->orWhere('serial', '');
            });
        }
        $license = $query->first();

        // Asset tag is captured separately for the seat-checkout logic below;
        // it is not itself a License column, so it does not go through the
        // item array (asset_tag isn't in License's fillable).
        $asset_tag = $this->csvRowHas($row, 'asset_tag') ? $this->findCsvMatch($row, 'asset_tag') : '';

        if ($license) {
            if (! $this->updating) {
                // Duplicate license record. Don't recreate it, but still
                // honor the row's seat-assignment intent so users can run
                // a "assign these users to seats" CSV in create mode
                // without every row silently dropping the checkout.
                if ($serial !== '') {
                    $this->log('A matching License '.$name.' with serial '.$serial.' already exists, checking for seat assignment');
                } else {
                    $this->log('A matching License '.$name.' with no serial number already exists, checking for seat assignment');
                }

                $this->recordSkipped();
                $this->assignSeatIfCheckoutTargetSet($license, $asset_tag);

                return;
            }

            $this->log('Updating License');
            $editingLicense = true;
        } else {
            $this->log('No Matching License, Creating a new one');
            $license = new License;
        }

        $this->setItemFromCsvIfPresent($row, 'license_email');
        $this->setItemFromCsvIfPresent($row, 'license_name');
        $this->setItemFromCsvIfPresent($row, 'maintained');
        $this->setItemFromCsvIfPresent($row, 'purchase_order');
        $this->setItemFromCsvIfPresent($row, 'order_number');
        $this->setItemFromCsvIfPresent($row, 'reassignable');
        $this->setItemFromCsvIfPresent($row, 'min_amt');
        $this->setItemFromCsvIfPresent($row, 'seats');
        // Persist the requestable flag on both create + update, matching
        // the accessory / consumable / component importers now that
        // License is a first-class Requestable. License's
        // setRequestableAttribute normalizes "0"/"1"/"true"/"false"/""
        // so any of the common CSV shapes lands correctly.
        $this->setItemFromCsvIfPresent($row, 'requestable');

        // Dates need parseOrNullDate after the raw value is in $this->item.
        // Empty value stays null (which clears the DB field on update).
        foreach (['expiration_date', 'termination_date'] as $dateField) {
            if ($this->csvRowHas($row, $dateField)) {
                $raw = $this->findCsvMatch($row, $dateField);
                if ($raw !== '') {
                    $this->item[$dateField] = $raw;
                    $this->item[$dateField] = $this->parseOrNullDate($dateField);
                } else {
                    $this->item[$dateField] = null;
                }
            }
        }

        if ($editingLicense) {
            $license->update($this->sanitizeItemForUpdating($license));
        } else {
            $license->fill($this->sanitizeItemForStoring($license));
            $license->created_by = $this->created_by;
        }

        // This sets an attribute on the Loggable trait for the action log
        $license->setImported(true);

        // For new licenses we need to save, for existing ones update() already saved
        $licenseWasSaved = $editingLicense || $license->save();

        if ($licenseWasSaved) {
            $this->log('License '.$name.' with serial number '.$serial.' was created or updated');

            if ($editingLicense) {
                $this->recordUpdated();
            } else {
                $this->recordCreated();
            }

            $this->assignSeatIfCheckoutTargetSet($license, $asset_tag);

            return;
        }
        $this->recordErrored();
        $this->logError($license, 'License "'.$name.'"');
    }

    /**
     * Attempt a single-seat checkout on $license for the checkout_target
     * captured on $this->item in handle(). Called from all three create-or-
     * update paths (created, updated, skipped-because-duplicate) so a
     * seat-assignment CSV works regardless of whether the license record
     * itself was newly created, updated, or already existed.
     *
     * If $license has no free seats, no checkout_target was supplied, or
     * a company mismatch trips the canCheckoutTo gate, this is a no-op
     * with a log line.
     */
    protected function assignSeatIfCheckoutTargetSet(License $license, string $asset_tag): void
    {
        if ($license->seats <= 0) {
            return;
        }

        $checkout_target = $this->item['checkout_target'] ?? null;
        $asset = $asset_tag !== '' ? Asset::where('asset_tag', $asset_tag)->first() : null;

        if (! $checkout_target && ! $asset) {
            return;
        }

        if ($checkout_target && ! $this->targetPermittedByContract(License::class, $checkout_target, trans('general.license'), $license->name)) {
            return;
        }

        $targetLicense = $license->freeSeat();
        if (is_null($targetLicense)) {
            // No free seats left on the license. The row's license
            // create/update already succeeded (or was correctly skipped as
            // a duplicate), but the seat-assignment portion cannot proceed.
            // Surface this as an errored row so the wizard tally reflects
            // that the caller's intent was only partially fulfilled, and
            // log a specific message the caller can act on. Without this,
            // a seat-assignment CSV whose row count exceeds the license's
            // available seats silently drops the trailing rows on the
            // floor (reported in #19467 follow-up).
            // The guard at the top of this method already returned when
            // both $checkout_target and $asset were falsy, so the else
            // branch here is reached only when $asset is truthy.
            if ($checkout_target) {
                $target_label = $checkout_target->name ?: ($checkout_target->username ?: (string) $checkout_target->id);
            } else {
                $target_label = $asset->present()->name();
            }

            $this->log(trans('admin/licenses/message.import.no_free_seats', [
                'license' => $license->name,
                'target' => $target_label,
            ]));
            $this->addErrorToBag($license, 'seats', trans('admin/licenses/message.import.no_free_seats', [
                'license' => $license->name,
                'target' => $target_label,
            ]));
            $this->recordErrored();

            return;
        }

        if ($checkout_target) {
            if (! $license->canCheckoutTo($checkout_target)) {
                $this->log(trans('general.error_checkout_company_mismatch', [
                    'item' => trans('general.license').' "'.$license->name.'"',
                    'item_company' => $license->company?->name ?? trans('general.unassigned'),
                    'target' => ($checkout_target->name ?? $checkout_target->username ?? $checkout_target->id),
                ]));

                return;
            }
            $targetLicense->assigned_to = $checkout_target->id;
            $targetLicense->created_by = $this->created_by;
            if ($asset) {
                $targetLicense->asset_id = $asset->id;
            }
            $targetLicense->save();

            $this->maybeSendWelcomeEmail($checkout_target);

            return;
        }

        if (! $license->canCheckoutTo($asset)) {
            $this->log(trans('general.error_checkout_company_mismatch', [
                'item' => trans('general.license').' "'.$license->name.'"',
                'item_company' => $license->company->name ?? trans('general.unassigned'),
                'target' => trans('general.asset').' "'.$asset->display_name.'"',
            ]));

            return;
        }
        $targetLicense->created_by = $this->created_by;
        $targetLicense->asset_id = $asset->id;
        $targetLicense->save();
    }
}
