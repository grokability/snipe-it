<?php

namespace App\Importer;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;

class AssetImporter extends ItemImporter
{
    protected $defaultStatusLabelId;

    public function __construct($filename)
    {
        parent::__construct($filename);

        $this->defaultStatusLabelId = Statuslabel::first()?->id;

        if (! is_null(Statuslabel::deployable()->first())) {
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
        // AssetImporter deliberately does NOT call parent::handle(). The
        // parent unconditionally assigns $this->item entries for every
        // shared field, which conflates "column absent from CSV" with
        // "column present but empty" and prevents empty CSV cells from
        // clearing existing DB values on update. AssetImporter builds
        // $this->item exclusively via setItemFromCsvIfPresent so absent
        // columns never enter the update payload (preserving DB values)
        // and present-but-empty columns land as null (clearing DB values).
        // The base sanitize's reject-empty pass is disabled by
        // $rejectEmptyOnUpdate on ItemImporter.
        $this->item = [];

        // Shared lookup fields. Present-and-empty clears the FK; absent
        // preserves it; present-and-set resolves and stores the id. Some
        // of these (category, manufacturer, department, manager) are not
        // in Asset's fillable and get dropped by sanitize before the DB
        // write, but the resolver calls still have important side effects
        // like auto-creating the referenced record so downstream lookups
        // (createOrFetchAssetModel needs the category, createOrFetchUser
        // needs the department) can find them.
        foreach ([
            ['category_id', 'category', fn ($v) => $this->createOrFetchCategory($v)],
            ['company_id', 'company', fn ($v) => $this->createOrFetchCompany($v)],
            ['location_id', 'location', fn ($v) => $this->createOrFetchLocation($v)],
            ['rtd_location_id', 'default_location', fn ($v) => $this->createOrFetchLocation($v)],
            ['manufacturer_id', 'manufacturer', fn ($v) => $this->createOrFetchManufacturer($v)],
            ['status_id', 'status', fn ($v) => $this->createOrFetchStatusLabel($v)],
            ['supplier_id', 'supplier', fn ($v) => $this->createOrFetchSupplier($v)],
            ['department_id', 'department', fn ($v) => $this->createOrFetchDepartment($v)],
        ] as [$itemKey, $csvKey, $resolver]) {
            if ($this->csvRowHas($row, $csvKey)) {
                $value = $this->findCsvMatch($row, $csvKey);
                $this->item[$itemKey] = ($value !== '') ? $resolver($value) : null;
            }
        }

        // Manager needs both first + last name; treat "first name column
        // present" as the presence signal for the whole lookup. Not in
        // Asset's fillable, but createOrFetchUser reads $this->item['manager_id']
        // when auto-creating a checkout-target user.
        if ($this->csvRowHas($row, 'manager_first_name')) {
            $first = $this->findCsvMatch($row, 'manager_first_name');
            $last = $this->findCsvMatch($row, 'manager_last_name');
            $this->item['manager_id'] = ($first !== '') ? $this->fetchManager($first, $last) : null;
        }

        // Straight CSV-to-item assignments for the Asset fillable set. Note
        // asset_notes not notes: assets use a different CSV column name to
        // avoid the ItemImporter's shared 'notes' handling.
        $this->setItemFromCsvIfPresent($row, 'name', 'item_name');
        $this->setItemFromCsvIfPresent($row, 'notes', 'asset_notes');
        // Assets keep order_number on the parent column. AssetObserver's
        // created hook writes the matching Order + OrderItem so we
        // don't call recordOrderForImportedRow here (that would
        // duplicate).
        $this->setItemFromCsvIfPresent($row, 'order_number');
        $this->setItemFromCsvIfPresent($row, 'purchase_cost');
        $this->setItemFromCsvIfPresent($row, 'serial');

        if ($this->csvRowHas($row, 'image')) {
            $raw = $this->findCsvMatch($row, 'image');
            $this->item['image'] = ($raw !== '') ? basename($raw) : null;
        }

        if ($this->csvRowHas($row, 'warranty_months')) {
            $raw = $this->findCsvMatch($row, 'warranty_months');
            $this->item['warranty_months'] = ($raw !== '') ? intval($raw) : null;
        }

        // Boolean flags: present-and-empty means clear (0). Absent means
        // don't touch on update. On create the default fallback below
        // fills in 0 for both columns if the CSV was absent.
        foreach (['requestable', 'byod'] as $flag) {
            if ($this->csvRowHas($row, $flag)) {
                $raw = $this->findCsvMatch($row, $flag);
                $this->item[$flag] = ($raw !== '') ? (($this->fetchHumanBoolean($raw) == 1) ? '1' : 0) : 0;
            }
        }

        // Model lookup. asset_model absent from CSV means "don't touch";
        // if present but resolves to nothing, leave model_id unset so the
        // required-field validator catches it.
        if ($this->csvRowHas($row, 'asset_model')) {
            $modelId = $this->createOrFetchAssetModel($row);
            if ($modelId !== null) {
                $this->item['model_id'] = $modelId;
            }
        }

        // Dates: raw value stored so parseOrNullDate can read it, then the
        // parsed date replaces it. Empty CSV cell becomes null (clearing
        // the DB field on update). Datetime format on the checkout/checkin
        // fields preserves any time portion in the CSV.
        foreach ([
            'purchase_date' => 'date',
            'last_checkin' => 'datetime',
            'last_checkout' => 'datetime',
            'expected_checkin' => 'datetime',
            'last_audit_date' => 'date',
            'next_audit_date' => 'date',
            'asset_eol_date' => 'date',
        ] as $dateField => $format) {
            if ($this->csvRowHas($row, $dateField)) {
                $raw = $this->findCsvMatch($row, $dateField);
                if ($raw !== '') {
                    $this->item[$dateField] = $raw;
                    $this->item[$dateField] = $this->parseOrNullDate($dateField, $format);
                } else {
                    $this->item[$dateField] = null;
                }
            }
        }

        // Internal signals used by the checkout logic below. Neither is
        // fillable on Asset so sanitize's fillable filter always drops
        // them before the DB write.
        $this->item['checkout_class'] = $this->findCsvMatch($row, 'checkout_class');
        $this->item['checkout_target'] = $this->determineCheckout($row);
        $this->item['created_by'] = $this->created_by;

        // Custom fields keep their existing logic. Only populated when the
        // matching CSV column is present in the row; unchanged from the
        // pre-refactor behavior.
        if ($this->customFields) {
            foreach ($this->customFields as $customField) {
                $customFieldValue = $this->array_smart_custom_field_fetch($row, $customField);

                if (! is_null($customFieldValue)) {
                    if ($customField->field_encrypted == 1) {
                        $this->item['custom_fields'][$customField->db_column_name()] = $customFieldValue;
                        $this->log('Custom Field '.$customField->name.': [encrypted]');
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
     *
     * @since 3.0
     *
     * @return Asset|mixed|null
     */
    public function createAssetIfNotExists(array $row)
    {
        $editingAsset = false;
        $asset_tag = $this->findCsvMatch($row, 'asset_tag');

        if (empty($asset_tag)) {
            $asset_tag = Asset::autoincrement_asset();
        }

        if ($this->findCsvMatch($row, 'id') != '') {
            // Override asset if an ID was given
            \Log::debug('Finding asset by ID: '.$this->findCsvMatch($row, 'id'));
            $asset = Asset::with('assignedTo')->find($this->findCsvMatch($row, 'id'));
        } else {
            $asset = Asset::with('assignedTo')->where(['asset_tag' => (string) $asset_tag])->first();
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
            // created_by is not in Asset's $fillable, so the value that
            // handle() puts on $this->item is stripped by sanitizeItemForStoring()
            // before it reaches the model. Set it directly. Use $this->created_by
            // (the property set by setCreatedBy() from both ItemImportRequest
            // and ObjectImportCommand) rather than auth()->id() so CLI-run
            // imports get the --user_id option value instead of null.
            $asset->created_by = $this->created_by;
        }

        // On create: default requestable/byod/status_id if the CSV did not
        // include those columns. On update: leave them unset in $this->item
        // so absent columns preserve existing values.
        if (! $editingAsset) {
            if (! array_key_exists('requestable', $this->item)) {
                $this->item['requestable'] = 0;
            }
            if (! array_key_exists('byod', $this->item)) {
                $this->item['byod'] = 0;
            }
            if (! array_key_exists('status_id', $this->item)) {
                $this->log('No status ID field found, defaulting to first deployable status label.');
                $this->item['status_id'] = $this->defaultStatusLabelId;
            }
        }

        $this->item['asset_tag'] = $asset_tag;

        // We need to save the user if it exists so that we can checkout to user later.
        // Sanitizing the item will remove it.
        if (array_key_exists('checkout_target', $this->item)) {
            $target = $this->item['checkout_target'];
        }

        // Log a warning when the operator populated a checkout column for
        // this row but no target could be resolved. Prior behavior silently
        // no-op'd the checkout side of the import in this case, so the
        // row's other fields updated but no checkout event fired and
        // nothing surfaced to the operator. Common triggers: the named
        // location doesn't exist yet AND autocreate failed, or the older
        // two-column shape where checkout_class was required and not
        // mapped. See the determineCheckout comment.
        $checkoutColumnPopulated = $this->findCsvMatch($row, 'checkout_location')
            || $this->findCsvMatch($row, 'checkout_asset')
            || $this->findCsvMatch($row, 'checkout_user')
            || $this->findCsvMatch($row, 'checkout_class')
            || $this->findCsvMatch($row, 'email')
            || $this->findCsvMatch($row, 'username');
        if ($checkoutColumnPopulated && empty($target)) {
            $this->log('WARNING: A checkout column was populated for asset tag "'.$asset_tag.'" but no checkout target could be resolved from the row. The asset will be updated but no checkout event will fire.');
        }

        $item = $this->sanitizeItemForStoring($asset, $editingAsset);

        if (isset($this->item['rtd_location_id'])) {
            $item['rtd_location_id'] = $this->item['rtd_location_id'];
        } elseif (isset($this->item['location_id'])) {
            $item['rtd_location_id'] = $this->item['location_id'];
        }

        // Backdate helpers for the checkin/checkout events below.
        $checkin_date = date('Y-m-d H:i:s');
        if (! empty($this->item['last_checkin'])) {
            $checkin_date = $this->item['last_checkin'];
        }
        $checkout_date = date('Y-m-d H:i:s');
        if (! empty($this->item['last_checkout'])) {
            $checkout_date = $this->item['last_checkout'];
        }

        if ($editingAsset) {
            $asset->update($item);
            $asset->setImported(true);
        } else {
            $asset->fill($item);
            $asset->setImported(true);
        }

        // If we're updating, we don't want to overwrite old fields.
        // Apply custom fields to asset attributes if they exist
        $customFieldsToSave = [];
        if (array_key_exists('custom_fields', $this->item)) {
            foreach ($this->item['custom_fields'] as $custom_field => $val) {
                $asset->{$custom_field} = $val;
                $customFieldsToSave[$custom_field] = $val;
            }
        }

        // For existing assets that have custom fields, update them.
        // This avoids the issue of calling save() twice with Model::unguard() active.
        if ($editingAsset && ! empty($customFieldsToSave)) {
            $asset->update($customFieldsToSave);
            $success = true;
        } elseif (! $editingAsset) {
            // For new assets, save with all changes (custom fields included via direct attribute assignment above)
            $success = $asset->save();
        } else {
            // For existing assets without custom fields, update() already saved everything
            $success = true;
        }

        if ($success) {

            $name = $this->item['name'] ?? '';
            $serial = $this->item['serial'] ?? '';
            $this->log('Asset '.$name.' with serial number '.$serial.' created or updated');

            if ($editingAsset) {
                $this->recordUpdated();
            } else {
                $this->recordCreated();
                // AssetObserver::created already wrote the Order +
                // OrderItem from the asset's own columns.
            }

            // If we have a target to checkout to, lets do so.
            if (isset($target) && ($target !== false)) {
                // Concurrency guard, same shape as Api\AssetsController::checkout.
                // Two admins importing overlapping CSVs (or one admin importing
                // while another checkout runs through the UI) could race here:
                // the fresh() read + canCheckoutTo() check is followed by a
                // checkOut() call with no row lock. Re-fetch the row under
                // lockForUpdate and evaluate the ownership / eligibility
                // conditions against the locked snapshot. If a racing operator
                // claimed the asset in the interim, skip this row rather than
                // stacking a duplicate history entry.
                $asset = Asset::whereKey($asset->id)->lockForUpdate()->first();
                if (! $asset) {
                    return;
                }

                if (! $asset->canCheckoutTo($target)) {
                    $this->log(trans('general.error_checkout_company_mismatch', [
                        'item' => trans('general.asset').' "'.$asset->display_name.'"',
                        'item_company' => $asset->company?->name ?? trans('general.unassigned'),
                        'target' => ($target->name ?? $target->username ?? $target->id),
                    ]));
                } else {
                    $targetType = get_class($target);
                    $alreadyCheckedOutToTarget = ($asset->assigned_to == $target->id) && ($asset->assigned_type === $targetType);

                    // Skip duplicate checkout noise when update mode keeps the same assignment target.
                    if (! $alreadyCheckedOutToTarget) {
                        if (! is_null($asset->assigned_to)) {
                            event(new CheckoutableCheckedIn($asset, $asset->assigned, auth()->user(), 'Checkin from CSV Importer', $checkin_date));
                        }

                        $asset->checkOut($target, $this->created_by, $checkout_date, null, 'Checkout from CSV Importer', $asset->name);

                        $this->maybeSendWelcomeEmail($target);
                    }
                }
            }

            return;
        }
        $this->recordErrored();
        $name = $this->item['name'] ?? '';
        $this->logError($asset, 'Asset "'.$name.'"');
    }
}
