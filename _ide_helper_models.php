<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * Model for Accessories.
 *
 * @version v1.0
 * @property int $id
 * @property string|null $name
 * @property int|null $category_id
 * @property int|null $created_by
 * @property int $qty
 * @property bool $requestable
 * @property bool $archived
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $location_id
 * @property string|null $legacy_purchase_date
 * @property numeric|null $default_purchase_cost
 * @property string|null $legacy_order_number
 * @property int|null $company_id
 * @property int|null $min_amt
 * @property int|null $manufacturer_id
 * @property string|null $model_number
 * @property string|null $image
 * @property int|null $default_supplier_id
 * @property string|null $notes
 * @property int|null $legacy_qty
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $assetlog
 * @property-read int|null $assetlog_count
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AccessoryCheckout> $checkouts
 * @property-read int|null $checkouts_count
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Supplier|null $defaultSupplier
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $lastCheckout
 * @property-read int|null $last_checkout_count
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \App\Models\Manufacturer|null $manufacturer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $openRequests
 * @property-read int|null $open_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read mixed $purchase_date_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $quantityAdjustLogs
 * @property-read int|null $quantity_adjust_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $requests
 * @property-read int|null $requests_count
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $manager_id
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AccessoryCheckout> $users
 * @property-read int|null $users_count
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory accessoriesForShow()
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory archived()
 * @method static \Database\Factories\AccessoryFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory newModelQuery()
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory newQuery()
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory notArchived()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accessory onlyTrashed()
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderByCreatedByName(string $order)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderByLastPurchaseCost(string $direction = 'asc')
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderByLastPurchaseDate(string $direction = 'asc')
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderByOrderNumber(string $direction = 'asc')
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderByTotalOrderCost(string $direction = 'asc')
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderCategory(string $order)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderCompany(string $order)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderLocation(string $order)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderManufacturer(string $order)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderPercentRemaining(string $order)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderRemaining(string $order)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory orderSupplier(string $order)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory query()
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory requestable()
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory requestedBy(\App\Models\User $user)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory textSearch($search)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereArchived($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereCategoryId($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereCompanyId($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereCreatedAt($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereCreatedBy($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereDefaultPurchaseCost($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereDefaultSupplierId($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereDeletedAt($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereId($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereImage($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereLegacyOrderNumber($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereLegacyPurchaseDate($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereLegacyQty($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereLocationId($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereManufacturerId($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereMinAmt($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereModelNumber($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereName($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereNotes($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereQty($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereRequestable($value)
 * @method static \App\Models\Builders\AccessoryQueryBuilder<static>|Accessory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accessory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accessory withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAccessory {}
}

namespace App\Models{
/**
 * Model for Accessories.
 *
 * @version v1.0
 * @property int $id
 * @property int|null $created_by
 * @property int|null $accessory_id
 * @property int|null $assigned_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $note
 * @property string|null $assigned_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \App\Models\Accessory|null $accessory
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $assignedTo
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout assetsAssigned()
 * @method static \Database\Factories\AccessoryCheckoutFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout locationAssigned()
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout newModelQuery()
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout newQuery()
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout query()
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout textSearch($search)
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout userAssigned()
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout whereAccessoryId($value)
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout whereAssignedTo($value)
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout whereAssignedType($value)
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout whereCreatedAt($value)
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout whereCreatedBy($value)
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout whereId($value)
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout whereNote($value)
 * @method static \App\Models\Builders\AccessoryCheckoutQueryBuilder<static>|AccessoryCheckout whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAccessoryCheckout {}
}

namespace App\Models{
/**
 * Model for the Actionlog (the table that keeps a historical log of
 * checkouts, checkins, and updates).
 *
 * @version v1.0
 * @property int $id
 * @property int|null $created_by
 * @property string $action_type
 * @property int|null $target_id
 * @property string|null $target_type
 * @property int|null $location_id
 * @property string|null $note
 * @property string|null $filename
 * @property string $item_type
 * @property int $item_id
 * @property string|null $expected_checkin
 * @property int $quantity
 * @property int|null $order_item_id
 * @property int|null $accepted_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $thread_id
 * @property int|null $company_id
 * @property string|null $accept_signature
 * @property string|null $log_meta
 * @property string|null $action_date
 * @property string|null $stored_eula
 * @property string|null $action_source
 * @property string|null $remote_ip
 * @property string|null $user_agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Company> $company
 * @property-read int|null $company_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Component> $components
 * @property-read int|null $components_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable> $consumables
 * @property-read int|null $consumables_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\OrderItem|null $orderItem
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $target
 * @property-read \App\Models\User|null $user
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\ActionlogFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog forApiHistory()
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog newModelQuery()
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Actionlog onlyTrashed()
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog orderByAssetNextAuditDate(string $order)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog orderByFilteredItemName(string $classString, string $order)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog orderByItemName(string $order)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog orderByLocation(string $order)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog orderByPolymorphicItemName(string $order)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog query()
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog textSearch($search)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereAcceptSignature($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereAcceptedId($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereActionDate($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereActionSource($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereActionType($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereCompanyId($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereCreatedAt($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereCreatedBy($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereDeletedAt($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereExpectedCheckin($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereFilename($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereId($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereItemId($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereItemType($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereLocationId($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereLogMeta($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereNote($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereOrderItemId($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereQuantity($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereRemoteIp($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereStoredEula($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereTargetId($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereTargetType($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereThreadId($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereUpdatedAt($value)
 * @method static \App\Models\Builders\ActionlogQueryBuilder<static>|Actionlog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Actionlog withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Actionlog withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperActionlog {}
}

namespace App\Models{
/**
 * Model for Assets.
 *
 * @version v1.0
 * @property ?int $location_id
 * @property \Carbon\Carbon|string|null $next_audit_date
 * @property \Carbon\Carbon|string|null $last_audit_date
 * @property \Carbon\Carbon|string|null $asset_eol_date
 * @property ?int $company_id
 * @property \Carbon\Carbon|string|null $last_checkin
 * @property bool $requestable
 * @property Asset|Location|User|null $assigned Current checkout target. morphTo relation named `assigned` (declared on method `assignedTo()`). Eloquent resolves the dynamic property by the morphTo name arg, not the method name, so this `@property` is distinct from `@property ... $assignedTo` written by ide-helper.
 * @property Asset|Location|User|null $assignedTo Current checkout target. morphTo relation named `assigned` (declared on method `assignedTo()`). Eloquent resolves the dynamic property by the morphTo name arg, not the method name, so this `@property` is distinct from `@property ... $assigned` written by ide-helper.
 * @property int $id
 * @property string|null $name
 * @property string|null $asset_tag
 * @property int|null $model_id
 * @property string|null $serial
 * @property \Illuminate\Support\Carbon|null $purchase_date
 * @property bool $eol_explicit
 * @property numeric|null $purchase_cost
 * @property string|null $order_number
 * @property int|null $assigned_to
 * @property string|null $notes
 * @property string|null $image
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $physical
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $status_id
 * @property int|null $archived
 * @property int|null $warranty_months
 * @property int|null $depreciate
 * @property int|null $supplier_id
 * @property int|null $rtd_location_id
 * @property string|null $accepted
 * @property \Illuminate\Support\Carbon|null $last_checkout
 * @property \Illuminate\Support\Carbon|null $expected_checkin
 * @property string|null $assigned_type
 * @property int $checkin_counter
 * @property int $requests_counter
 * @property int $checkout_counter
 * @property int|null $byod
 * @property string|null $_snipeit_imei_1
 * @property string|null $_snipeit_phone_number_2
 * @property string|null $_snipeit_ram_3
 * @property string|null $_snipeit_cpu_4
 * @property string|null $_snipeit_mac_address_5
 * @property string|null $_snipeit_test_encrypted_6
 * @property string|null $_snipeit_test_checkbox_7
 * @property string|null $_snipeit_test_radio_8
 * @property string|null $_snipeit_notes_9
 * @property string|null $_snipeit_sample_date_10
 * @property string|null $_snipeit_sample_datetime_11
 * @property string|null $_snipeit__12
 * @property string|null $_snipeit_dscsdaffds_13
 * @property string|null $_snipeit_last_seen_network_heartbeat_14
 * @property string|null $_snipeit_disk_encryption_15
 * @property string|null $_snipeit_some_number_16
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $assetlog
 * @property-read int|null $assetlog_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AccessoryCheckout> $assignedAccessories
 * @property-read int|null $assigned_accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Asset> $assignedAssets
 * @property-read int|null $assigned_assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CalendarEvent> $calendarEvents
 * @property-read int|null $calendar_events_count
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Component> $components
 * @property-read int|null $components_count
 * @property-read \App\Models\Location|null $defaultLoc
 * @property-read \App\Models\Depreciation|null $depreciation
 * @property-read mixed $display_name
 * @property-read mixed $eol_date
 * @property-read mixed $eol_diff_for_humans
 * @property-read mixed $eol_diff_in_days
 * @property-read mixed $eol_formatted_date
 * @property-read mixed $expected_checkin_diff_for_humans
 * @property-read mixed $expected_checkin_formatted_date
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \App\Models\AssetExternalSource|null $externalSource
 * @property-read string $detailed_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read mixed $last_audit_diff
 * @property-read mixed $last_audit_diff_for_humans
 * @property-read mixed $last_audit_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseSeat> $licenseseats
 * @property-read int|null $licenseseats_count
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Maintenance> $maintenances
 * @property-read int|null $maintenances_count
 * @property-read \App\Models\Manufacturer|null $manufacturer
 * @property-read \App\Models\AssetModel|null $model
 * @property-read mixed $next_audit_diff_for_humans
 * @property-read mixed $next_audit_diff_in_days
 * @property-read mixed $next_audit_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $openRequests
 * @property-read int|null $open_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read mixed $purchase_date_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $requests
 * @property-read int|null $requests_count
 * @property-write mixed $category_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $parent_id
 * @property-read \App\Models\Statuslabel|null $status
 * @property-read \App\Models\Supplier|null $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @property-read mixed $warranty_expires
 * @property-read mixed $warranty_expires_diff
 * @property-read mixed $warranty_expires_diff_for_humans
 * @property-read mixed $warranty_expires_formatted_date
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset accepted()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset archived()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset assetsByLocation(\App\Models\Location $location)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset assetsForShow()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset assignedSearch(string $search)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset byDepreciationId(int $search)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset byLocationId(int $search)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset byManufacturer(array|string $manufacturer_id)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset checkedOutToTargetInDepartment(array $search)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset deployed()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset dueForAudit(\App\Models\Setting $settings)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset dueForCheckin(\App\Models\Setting $settings)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset dueOrOverdueForAudit(\App\Models\Setting $settings)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset dueOrOverdueForCheckin(\App\Models\Setting $settings)
 * @method static \Database\Factories\AssetFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset hardware()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset inCategory(array|string $category_id)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset inModelList(array $modelIdListing)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset newModelQuery()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset newQuery()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset notArchived()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset notYetAccepted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset onlyTrashed()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderAssigned(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderByCreatedByName(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderByJobTitle(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderByLastPurchaseCost(string $direction = 'asc')
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderByLastPurchaseDate(string $direction = 'asc')
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderByOrderNumber(string $direction = 'asc')
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderByTotalOrderCost(string $direction = 'asc')
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderCategory(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderCompany(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderExternalSource(string $order, string $column)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderLocation(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderManufacturer(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderModelNumber(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderModels(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderRtdLocation(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderStatus(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset orderSupplier(string $order)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset overdueForAudit()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset overdueForCheckin()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset pastEol()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset pending()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset query()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset rejected()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset requestable()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset requestedBy(\App\Models\User $user)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset rtd()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset textSearch($search)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset undeployable()
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereAccepted($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereArchived($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereAssetEolDate($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereAssetTag($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereAssignedTo($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereAssignedType($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereByod($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereCheckinCounter($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereCheckoutCounter($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereCompanyId($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereCreatedAt($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereCreatedBy($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereDeletedAt($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereDepreciate($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereEolExplicit($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereExpectedCheckin($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereId($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereImage($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereLastAuditDate($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereLastCheckin($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereLastCheckout($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereLocationId($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereModelId($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereName($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereNextAuditDate($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereNotes($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereOrderNumber($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset wherePhysical($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset wherePurchaseCost($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset wherePurchaseDate($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereRequestable($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereRequestsCounter($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereRtdLocationId($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSerial($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeit12($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitCpu4($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitDiskEncryption15($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitDscsdaffds13($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitImei1($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitLastSeenNetworkHeartbeat14($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitMacAddress5($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitNotes9($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitPhoneNumber2($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitRam3($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitSampleDate10($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitSampleDatetime11($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitSomeNumber16($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitTestCheckbox7($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitTestEncrypted6($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSnipeitTestRadio8($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereStatusId($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereSupplierId($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereUpdatedAt($value)
 * @method static \App\Models\Builders\AssetQueryBuilder<static>|Asset whereWarrantyMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asset withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAsset {}
}

namespace App\Models{
/**
 * Sync-adapter side row for an asset. Combines the identity mapping
 * (which adapter sourced this asset via source + external_id) with
 * the last-known network / OS inventory the vendor reported
 * (primary_mac / primary_ip / os / os_version / last_seen).
 *
 * One row per asset today. SyncAdapter upserts on the
 * (source, external_id) unique index and updates the inventory
 * columns on every sync run.
 *
 * @property int $id
 * @property int|null $company_id
 * @property int $asset_id
 * @property string $source
 * @property string $external_id
 * @property string|null $primary_mac
 * @property string|null $primary_ip
 * @property string|null $os
 * @property string|null $os_version
 * @property \Illuminate\Support\Carbon|null $last_seen
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Asset|null $asset
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereLastSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereOs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereOsVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource wherePrimaryIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource wherePrimaryMac($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetExternalSource whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAssetExternalSource {}
}

namespace App\Models{
/**
 * Model for Asset Models. Asset Models contain higher level
 * attributes that are common among the same type of asset.
 *
 * @version v1.0
 * @property int $id
 * @property string $name
 * @property string|null $mfg_marketing_name
 * @property \Illuminate\Support\Carbon|null $mfg_release_date
 * @property \Illuminate\Support\Carbon|null $mfg_discontinued_date
 * @property string|null $tag_slug
 * @property string|null $model_number
 * @property string|null $hardware_identifier
 * @property int|null $min_amt
 * @property int|null $manufacturer_id
 * @property int|null $category_id
 * @property int $require_serial
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $depreciation_id
 * @property int|null $created_by
 * @property int|null $eol
 * @property string|null $image
 * @property int $deprecated_mac_address
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $fieldset_id
 * @property string|null $notes
 * @property int $requestable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $archivedAssets
 * @property-read int|null $archived_assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assignedAssets
 * @property-read int|null $assigned_assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $availableAssets
 * @property-read int|null $available_assets_count
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomField> $defaultValues
 * @property-read int|null $default_values_count
 * @property-read \App\Models\Depreciation|null $depreciation
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \App\Models\CustomFieldset|null $fieldset
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \App\Models\Manufacturer|null $manufacturer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $openRequests
 * @property-read int|null $open_requests_count
 * @property-read mixed $purchase_date_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $requests
 * @property-read int|null $requests_count
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\AssetModelFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel inCategory(array $categoryIdListing)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel newModelQuery()
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetModel onlyTrashed()
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel orderByCreatedByName(string $order)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel orderCategory(string $order)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel orderFieldset(string $order)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel orderManufacturer(string $order)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel orderPercentRemaining(string $order)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel query()
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel requestable()
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel requestedBy(\App\Models\User $user)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel searchByManufacturerOrCat(string $search)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel textSearch($search)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereCategoryId($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereCreatedAt($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereCreatedBy($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereDeletedAt($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereDeprecatedMacAddress($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereDepreciationId($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereEol($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereFieldsetId($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereHardwareIdentifier($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereId($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereImage($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereManufacturerId($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereMfgDiscontinuedDate($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereMfgMarketingName($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereMfgReleaseDate($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereMinAmt($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereModelNumber($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereName($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereNotes($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereRequestable($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereRequireSerial($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereTagSlug($value)
 * @method static \App\Models\Builders\AssetModelQueryBuilder<static>|AssetModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetModel withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAssetModel {}
}

namespace App\Models{
/**
 * Row in the calendar_events index table. One instance per
 * (source_type, source_id, source_field) triple - a single source
 * model can publish multiple rows if its calendarEventDefinitions()
 * declares multiple fields.
 *
 * Deliberately thin. This model exists for the observer's upsert
 * path and for the read-path range/type filter query; consumers that
 * want to render events read the source model live via ->source
 * rather than pulling values off this row.
 *
 * @property int $id
 * @property string $source_type
 * @property int $source_id
 * @property string $source_field
 * @property string $event_type
 * @property \Illuminate\Support\Carbon $start
 * @property \Illuminate\Support\Carbon|null $end
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereSourceField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CalendarEvent withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCalendarEvent {}
}

namespace App\Models{
/**
 * Model for Categories. Categories are a higher-level group
 * than Asset Models, and handle things like whether or not
 * to require acceptance from the user, whether or not to
 * send a EULA to the user, etc.
 *
 * @version v1.0
 * @property int $id
 * @property string $name
 * @property string|null $tag_slug
 * @property string|null $tag_color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $eula_text
 * @property int $use_default_eula
 * @property int $require_acceptance
 * @property bool $alert_on_response
 * @property string|null $category_type
 * @property int $checkin_email
 * @property string|null $image
 * @property string|null $notes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Component> $components
 * @property-read int|null $components_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable> $consumables
 * @property-read int|null $consumables_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetModel> $models
 * @property-read int|null $models_count
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $showableAssets
 * @property-read int|null $showable_assets_count
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category newModelQuery()
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category query()
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category requiresAcceptance()
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category textSearch($search)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereAlertOnResponse($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereCategoryType($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereCheckinEmail($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereCreatedAt($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereCreatedBy($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereDeletedAt($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereEulaText($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereId($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereImage($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereName($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereNotes($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereRequireAcceptance($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereTagColor($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereTagSlug($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereUpdatedAt($value)
 * @method static \App\Models\Builders\CategoryQueryBuilder<static>|Category whereUseDefaultEula($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCategory {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $checkoutable_type
 * @property int $checkoutable_id
 * @property int|null $assigned_to_id
 * @property int|null $qty
 * @property string|null $signature_filename
 * @property string|null $note
 * @property int|null $alert_on_response_id
 * @property \Illuminate\Support\Carbon|null $accepted_at
 * @property \Illuminate\Support\Carbon|null $declined_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $stored_eula
 * @property string|null $stored_eula_file
 * @property-read \App\Models\User|null $assignedTo
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $checkoutable
 * @property-read mixed $checkoutable_category_name
 * @property-read mixed $display_checkoutable_type
 * @property-read string $checkoutable_item_type
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance declined()
 * @method static \Database\Factories\CheckoutAcceptanceFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance forUser(\App\Models\User $user)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance hasFiles()
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance newModelQuery()
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutAcceptance onlyTrashed()
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance pending()
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance query()
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance visibleToCurrentUser()
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereAcceptedAt($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereAlertOnResponseId($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereAssignedToId($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereCheckoutableId($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereCheckoutableType($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereCreatedAt($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereDeclinedAt($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereDeletedAt($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereId($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereNote($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereQty($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereSignatureFilename($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereStoredEula($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereStoredEulaFile($value)
 * @method static \App\Models\Builders\CheckoutAcceptanceQueryBuilder<static>|CheckoutAcceptance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutAcceptance withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutAcceptance withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCheckoutAcceptance {}
}

namespace App\Models{
/**
 * @property \App\Enums\CheckoutRequestState $state
 * @property int $id
 * @property int $user_id
 * @property int $requestable_id
 * @property string $requestable_type
 * @property int $quantity
 * @property int $fulfilled_quantity
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $canceled_at
 * @property \Illuminate\Support\Carbon|null $fulfilled_at
 * @property int|null $fulfilled_by
 * @property int|null $checkout_actionlog_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CalendarEvent> $calendarEvents
 * @property-read int|null $calendar_events_count
 * @property-read \App\Models\Actionlog|null $checkoutActionlog
 * @property-read \App\Models\User|null $fulfilledBy
 * @property-read mixed|null $calendar_end
 * @property-read mixed|null $calendar_start
 * @property-read string|null $name
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $requestable
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $requestedItem
 * @property-read \App\Models\User|null $user
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest canceled()
 * @method static \Database\Factories\CheckoutRequestFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest fulfilled()
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest newModelQuery()
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutRequest onlyTrashed()
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest open()
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest pending()
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest query()
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest textSearch($search)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereCanceledAt($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereCheckoutActionlogId($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereCreatedAt($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereDeletedAt($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereEndDate($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereFulfilledAt($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereFulfilledBy($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereFulfilledQuantity($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereId($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereNotes($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereQuantity($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereRequestableId($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereRequestableType($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereStartDate($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereState($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereUpdatedAt($value)
 * @method static \App\Models\Builders\CheckoutRequestQueryBuilder<static>|CheckoutRequest whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutRequest withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckoutRequest withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCheckoutRequest {}
}

namespace App\Models{
/**
 * Model for Companies.
 *
 * @version v1.8
 * @property int $id
 * @property string $name
 * @property string|null $tag_slug
 * @property int|null $parent_id
 * @property string|null $tag_color
 * @property string|null $fax
 * @property string|null $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $created_by
 * @property string|null $notes
 * @property string|null $webhook_selected
 * @property string|null $webhook_endpoint
 * @property string|null $webhook_channel
 * @property string|null $webhook_botname
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Company> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Component> $components
 * @property-read int|null $components_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable> $consumables
 * @property-read int|null $consumables_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read Company|null $parent
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company companyableChildren($query)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company companyables($column = 'company_id', $table_name = null)
 * @method static \Database\Factories\CompanyFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company newModelQuery()
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company onlyTrashed()
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company orderParent(string $order)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company query()
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company textSearch($search)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company usersByCompanyIds(array $companyIds)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereCreatedAt($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereCreatedBy($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereDeletedAt($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereEmail($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereFax($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereId($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereImage($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereName($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereNotes($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereParentId($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company wherePhone($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereTagColor($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereTagSlug($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereUpdatedAt($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereWebhookBotname($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereWebhookChannel($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereWebhookEndpoint($value)
 * @method static \App\Models\Builders\CompanyQueryBuilder<static>|Company whereWebhookSelected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperCompany {}
}

namespace App\Models{
/**
 * Model for Components.
 *
 * @version v1.0
 * @property int $id
 * @property string|null $name
 * @property int|null $category_id
 * @property int|null $location_id
 * @property int|null $company_id
 * @property int|null $created_by
 * @property int|null $default_supplier_id
 * @property int $qty
 * @property string|null $legacy_order_number
 * @property string|null $legacy_purchase_date
 * @property numeric|null $default_purchase_cost
 * @property string|null $model_number
 * @property int|null $manufacturer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $min_amt
 * @property string|null $serial
 * @property string|null $image
 * @property string|null $notes
 * @property int|null $legacy_qty
 * @property bool $requestable
 * @property bool $archived
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $assetlog
 * @property-read int|null $assetlog_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read mixed $calculated_purchase_cost
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Supplier|null $defaultSupplier
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \App\Models\Manufacturer|null $manufacturer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $openRequests
 * @property-read int|null $open_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read mixed $purchase_date_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $quantityAdjustLogs
 * @property-read int|null $quantity_adjust_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $requests
 * @property-read int|null $requests_count
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $manager_id
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $unconstrainedAssets
 * @property-read int|null $unconstrained_assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component archived()
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component componentsForShow()
 * @method static \Database\Factories\ComponentFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component newModelQuery()
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component newQuery()
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component notArchived()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component onlyTrashed()
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderByLastPurchaseCost(string $direction = 'asc')
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderByLastPurchaseDate(string $direction = 'asc')
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderByOrderNumber(string $direction = 'asc')
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderByTotalOrderCost(string $direction = 'asc')
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderCategory(string $order)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderCompany(string $order)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderLocation(string $order)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderManufacturer(string $order)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderPercentRemaining(string $order)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderRemaining(string $order)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component orderSupplier(string $order)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component query()
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component requestable()
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component requestedBy(\App\Models\User $user)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component textSearch($search)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereArchived($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereCategoryId($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereCompanyId($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereCreatedAt($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereCreatedBy($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereDefaultPurchaseCost($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereDefaultSupplierId($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereDeletedAt($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereId($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereImage($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereLegacyOrderNumber($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereLegacyPurchaseDate($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereLegacyQty($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereLocationId($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereManufacturerId($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereMinAmt($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereModelNumber($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereName($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereNotes($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereQty($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereRequestable($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereSerial($value)
 * @method static \App\Models\Builders\ComponentQueryBuilder<static>|Component whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperComponent {}
}

namespace App\Models{
/**
 * Model for Accessories.
 *
 * @version v1.0
 * @property int $id
 * @property int|null $created_by
 * @property int|null $assigned_qty
 * @property int|null $component_id
 * @property int|null $asset_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $note
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \App\Models\Component|null $component
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Component> $components
 * @property-read int|null $components_count
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment newModelQuery()
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment newQuery()
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment orderByComponentName(string $order)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment orderByCreatedByName(string $order)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment query()
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment textSearch($search)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment whereAssetId($value)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment whereAssignedQty($value)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment whereComponentId($value)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment whereCreatedAt($value)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment whereCreatedBy($value)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment whereId($value)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment whereNote($value)
 * @method static \App\Models\Builders\ComponentAssignmentQueryBuilder<static>|ComponentAssignment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperComponentAssignment {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property int|null $category_id
 * @property int|null $location_id
 * @property int|null $created_by
 * @property int|null $default_supplier_id
 * @property int $qty
 * @property bool $requestable
 * @property bool $archived
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $legacy_purchase_date
 * @property numeric|null $default_purchase_cost
 * @property string|null $legacy_order_number
 * @property int|null $company_id
 * @property int|null $min_amt
 * @property string|null $model_number
 * @property int|null $manufacturer_id
 * @property string|null $item_no
 * @property string|null $image
 * @property string|null $notes
 * @property int|null $legacy_qty
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $assetlog
 * @property-read int|null $assetlog_count
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConsumableAssignment> $consumableAssignments
 * @property-read int|null $consumable_assignments_count
 * @property-read \App\Models\Supplier|null $defaultSupplier
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \App\Models\Manufacturer|null $manufacturer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $openRequests
 * @property-read int|null $open_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read mixed $purchase_date_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $quantityAdjustLogs
 * @property-read int|null $quantity_adjust_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $requests
 * @property-read int|null $requests_count
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $manager_id
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $unconstrainedUsers
 * @property-read int|null $unconstrained_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable archived()
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable consumablesForShow()
 * @method static \Database\Factories\ConsumableFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable newModelQuery()
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable newQuery()
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable notArchived()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consumable onlyTrashed()
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderByLastPurchaseCost(string $direction = 'asc')
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderByLastPurchaseDate(string $direction = 'asc')
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderByOrderNumber(string $direction = 'asc')
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderByTotalOrderCost(string $direction = 'asc')
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderCategory(string $order)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderCompany(string $order)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderLocation(string $order)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderManufacturer(string $order)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderPercentRemaining(string $order)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderRemaining(string $order)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable orderSupplier(string $order)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable query()
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable requestable()
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable requestedBy(\App\Models\User $user)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable textSearch($search)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereArchived($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereCategoryId($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereCompanyId($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereCreatedAt($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereCreatedBy($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereDefaultPurchaseCost($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereDefaultSupplierId($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereDeletedAt($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereId($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereImage($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereItemNo($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereLegacyOrderNumber($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereLegacyPurchaseDate($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereLegacyQty($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereLocationId($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereManufacturerId($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereMinAmt($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereModelNumber($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereName($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereNotes($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereQty($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereRequestable($value)
 * @method static \App\Models\Builders\ConsumableQueryBuilder<static>|Consumable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consumable withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consumable withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperConsumable {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $created_by
 * @property int|null $consumable_id
 * @property int|null $assigned_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $consumed_at
 * @property-read \App\Models\User|null $adminuser
 * @property-read \App\Models\Consumable|null $consumable
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment consumed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment outstanding()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment whereConsumableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment whereConsumedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableAssignment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperConsumableAssignment {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $format
 * @property string $element
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property string|null $field_values
 * @property int $field_encrypted
 * @property string|null $db_column
 * @property string|null $help_text
 * @property int $show_in_email
 * @property bool|null $show_in_requestable_list
 * @property int|null $is_unique
 * @property int|null $display_in_user_view
 * @property int|null $auto_add_to_fieldsets
 * @property int|null $show_in_listview
 * @property int $display_checkin
 * @property int $display_checkout
 * @property int $display_audit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetModel> $defaultValues
 * @property-read int|null $default_values_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomFieldset> $fieldset
 * @property-read int|null $fieldset_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\CustomFieldFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereAutoAddToFieldsets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereDbColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereDisplayAudit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereDisplayCheckin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereDisplayCheckout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereDisplayInUserView($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereElement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereFieldEncrypted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereFieldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereHelpText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereShowInEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereShowInListview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereShowInRequestableList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCustomField {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomField> $fields
 * @property-read int|null $fields_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetModel> $models
 * @property-read int|null $models_count
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomFieldset applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\CustomFieldsetFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomFieldset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomFieldset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomFieldset query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomFieldset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomFieldset whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomFieldset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomFieldset whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomFieldset whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCustomFieldset {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $tag_color
 * @property string|null $fax
 * @property string|null $phone
 * @property int|null $created_by
 * @property int|null $company_id
 * @property int|null $location_id
 * @property int|null $manager_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \App\Models\User|null $manager
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\DepartmentFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department newModelQuery()
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department onlyTrashed()
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department orderCompany(string $order)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department orderLocation(string $order)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department orderManager(string $order)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department query()
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department textSearch($search)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereCompanyId($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereCreatedAt($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereCreatedBy($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereDeletedAt($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereFax($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereId($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereImage($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereLocationId($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereManagerId($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereName($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereNotes($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department wherePhone($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereTagColor($value)
 * @method static \App\Models\Builders\DepartmentQueryBuilder<static>|Department whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDepartment {}
}

namespace App\Models{
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \App\Models\Depreciation|null $depreciation
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Depreciable applyOffsetAndLimit(int $total)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Depreciable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Depreciable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Depreciable query()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDepreciable {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $months
 * @property numeric|null $depreciation_min
 * @property string|null $fiscal_year_start_month
 * @property float|null $rate_multiplier
 * @property string $depreciation_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetModel> $models
 * @property-read int|null $models_count
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\DepreciationFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation newModelQuery()
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation newQuery()
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation query()
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation textSearch($search)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereCreatedAt($value)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereCreatedBy($value)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereDepreciationMin($value)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereDepreciationType($value)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereFiscalYearStartMonth($value)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereId($value)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereMonths($value)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereName($value)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereRateMultiplier($value)
 * @method static \App\Models\Builders\DepreciationQueryBuilder<static>|Depreciation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDepreciation {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $permissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property string|null $notes
 * @property string|null $scim_externalid
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\GroupFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group newModelQuery()
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group newQuery()
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group query()
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group textSearch($search)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group whereCreatedAt($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group whereCreatedBy($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group whereId($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group whereName($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group whereNotes($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group wherePermissions($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group whereScimExternalid($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|Group whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGroup {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string $file_path
 * @property int $filesize
 * @property string|null $import_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $header_row
 * @property array<array-key, mixed>|null $first_row
 * @property array<array-key, mixed>|null $field_map
 * @property int|null $created_by
 * @property int|null $processing_by
 * @property string|null $processing_started_at
 * @property-read \App\Models\User|null $adminuser
 * @method static \Database\Factories\ImportFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFieldMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFilesize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereFirstRow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereHeaderRow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereImportType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereProcessingBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereProcessingStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Import whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperImport {}
}

namespace App\Models\Labels{
/**
 * @property int $id
 * @property string $name
 * @property string|null $base_label
 * @property string $type
 * @property array<array-key, mixed>|null $overrides
 * @property array<array-key, mixed>|null $config_snapshot
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereBaseLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereConfigSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereOverrides($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomUserLabel withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCustomUserLabel {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ldap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ldap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ldap query()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLdap {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $serial
 * @property \Illuminate\Support\Carbon|null $purchase_date
 * @property numeric|null $purchase_cost
 * @property string|null $order_number
 * @property int $seats
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $depreciation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $license_name
 * @property string|null $license_email
 * @property int|null $depreciate
 * @property int|null $supplier_id
 * @property \Illuminate\Support\Carbon|null $expiration_date
 * @property string|null $purchase_order
 * @property \Illuminate\Support\Carbon|null $termination_date
 * @property int|null $maintained
 * @property int $reassignable
 * @property int|null $company_id
 * @property int|null $manufacturer_id
 * @property int|null $category_id
 * @property int|null $min_amt
 * @property bool $requestable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $assetlog
 * @property-read int|null $assetlog_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $assignedusers
 * @property-read int|null $assignedusers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CalendarEvent> $calendarEvents
 * @property-read int|null $calendar_events_count
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Depreciation|null $depreciation
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseSeat> $freeSeats
 * @property-read int|null $free_seats_count
 * @property-read int $assigned_seats_count
 * @property-read mixed $avail_seats_count
 * @property-read mixed $free_seat_count
 * @property-read int $license_seats_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseSeat> $licenseSeatsRelation
 * @property-read int|null $license_seats_relation_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseSeat> $licenseseats
 * @property-read int|null $licenseseats_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \App\Models\Manufacturer|null $manufacturer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $openRequests
 * @property-read int|null $open_requests_count
 * @property-read mixed $purchase_date_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CheckoutRequest> $requests
 * @property-read int|null $requests_count
 * @property-write mixed $department_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $warranty_months
 * @property-read \App\Models\Supplier|null $supplier
 * @property-read mixed $terminates_diff_for_humans
 * @property-read mixed $terminates_diff_in_days
 * @property-read mixed $terminates_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License activeLicenses()
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License expiredLicenses()
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License expiringLicenses(int $days = 60, bool $includeExpired = false)
 * @method static \Database\Factories\LicenseFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License newModelQuery()
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License onlyTrashed()
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License orderCompany(string $order)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License orderManufacturer(string $order)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License orderPercentRemaining(string $order)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License orderSupplier(string $order)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License query()
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License requestable()
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License requestedBy(\App\Models\User $user)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License textSearch($search)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License textSearchWithoutSerial($search)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereCategoryId($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereCompanyId($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereCreatedAt($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereCreatedBy($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereDeletedAt($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereDepreciate($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereDepreciationId($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereExpirationDate($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereId($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereLicenseEmail($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereLicenseName($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereMaintained($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereManufacturerId($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereMinAmt($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereName($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereNotes($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereOrderNumber($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License wherePurchaseCost($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License wherePurchaseDate($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License wherePurchaseOrder($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereReassignable($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereRequestable($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereSeats($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereSerial($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereSupplierId($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereTerminationDate($value)
 * @method static \App\Models\Builders\LicenseQueryBuilder<static>|License whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLicense {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $license_id
 * @property int|null $assigned_to
 * @property bool $unreassignable_seat
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $asset_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \App\Models\Asset|null $asset
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $assetlog
 * @property-read int|null $assetlog_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\License|null $license
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read mixed $name
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \App\Models\User|null $user
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat byAssigned()
 * @method static \Database\Factories\LicenseSeatFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat newModelQuery()
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseSeat onlyTrashed()
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat orderCompany(string $order)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat orderDepartments(string $order)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat query()
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat textSearch($search)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereAssetId($value)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereAssignedTo($value)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereCreatedAt($value)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereCreatedBy($value)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereDeletedAt($value)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereId($value)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereLicenseId($value)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereNotes($value)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereUnreassignableSeat($value)
 * @method static \App\Models\Builders\LicenseSeatQueryBuilder<static>|LicenseSeat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseSeat withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseSeat withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLicenseSeat {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $tag_color
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property string|null $address
 * @property string|null $address2
 * @property string|null $zip
 * @property string|null $fax
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $parent_id
 * @property string|null $currency
 * @property string|null $ldap_ou
 * @property int|null $manager_id
 * @property string|null $image
 * @property int|null $company_id
 * @property string|null $notes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AccessoryCheckout> $assignedAccessories
 * @property-read int|null $assigned_accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assignedAssets
 * @property-read int|null $assigned_assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Location> $children
 * @property-read int|null $children_count
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Component> $components
 * @property-read int|null $components_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable> $consumables
 * @property-read int|null $consumables_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \App\Models\User|null $manager
 * @property-read Location|null $parent
 * @property-read mixed $purchase_date_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $rtd_assets
 * @property-read int|null $rtd_assets_count
 * @property-write mixed $category_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\LocationFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location newModelQuery()
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location onlyTrashed()
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location orderByCreatedByName(string $order)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location orderCompany(string $order)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location orderManager(string $order)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location orderParent(string $order)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location query()
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location textSearch($search)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereAddress($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereAddress2($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereCity($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereCompanyId($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereCountry($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereCreatedAt($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereCreatedBy($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereCurrency($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereDeletedAt($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereFax($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereId($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereImage($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereLdapOu($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereManagerId($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereName($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereNotes($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereParentId($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location wherePhone($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereState($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereTagColor($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereUpdatedAt($value)
 * @method static \App\Models\Builders\LocationQueryBuilder<static>|Location whereZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLocation {}
}

namespace App\Models{
/**
 * Model for Asset Maintenances.
 *
 * @version v1.0
 * @property-read \App\Models\Asset|null $asset
 * @property-read \App\Models\Actionlog|null $assetlog
 * @property-read \App\Models\Supplier|null $supplier
 * @property-read \App\Models\MaintenanceType|null $maintenanceType
 * @property-read \App\Models\User|null $adminuser
 * @property-read \App\Models\User|null $responsibleParty
 * @property-read \App\Models\User|null $completedByUser
 * @property-read \Illuminate\Database\Eloquent\Model|null $item Polymorphic parent (usually Asset). See item() below.
 * @property int $id
 * @property int $item_id
 * @property string|null $item_type
 * @property int|null $supplier_id
 * @property string $asset_maintenance_type
 * @property string $name
 * @property string|null $url
 * @property int $is_warranty
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $expected_completion_date
 * @property int|null $asset_maintenance_time
 * @property string|null $notes
 * @property string|null $image
 * @property numeric|null $cost
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $maintenance_type_id
 * @property int|null $checked_out_to_id
 * @property string|null $checked_out_to_type
 * @property int|null $responsible_party_id
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $completed_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read int|null $assetlog_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CalendarEvent> $calendarEvents
 * @property-read int|null $calendar_events_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $checkedOutTo
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property mixed $asset_id
 * @property mixed $completion_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance active()
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance completed()
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance dueForCompletion(\App\Models\Setting $settings)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance dueOrOverdueForCompletion(\App\Models\Setting $settings)
 * @method static \Database\Factories\MaintenanceFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance newModelQuery()
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Maintenance onlyTrashed()
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderByAssetModelName(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderByAssetModelNumber(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderByAssetName(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderByAssetSerial(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderByCompletedAt(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderByMaintenanceType(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderBySupplier(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderByTag(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderLocationName(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance orderStatusName(string $order)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance overdueForCompletion()
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance query()
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance textSearch($search)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereAssetMaintenanceTime($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereAssetMaintenanceType($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereCheckedOutToId($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereCheckedOutToType($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereCompletedAt($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereCompletedBy($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereCost($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereCreatedAt($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereCreatedBy($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereDeletedAt($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereExpectedCompletionDate($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereId($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereImage($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereIsWarranty($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereItemId($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereItemType($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereMaintenanceTypeId($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereName($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereNotes($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereResponsiblePartyId($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereStartDate($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereSupplierId($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereUpdatedAt($value)
 * @method static \App\Models\Builders\MaintenanceQueryBuilder<static>|Maintenance whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Maintenance withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Maintenance withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMaintenance {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $tag_color
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read string $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Maintenance> $maintenances
 * @property-read int|null $maintenances_count
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\MaintenanceTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType orWhereMultipleColumns(array $columns, $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType textSearch($search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType whereTagColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceType withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMaintenanceType {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $tag_slug
 * @property string|null $tag_color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $url
 * @property string|null $support_url
 * @property string|null $warranty_lookup_url
 * @property string|null $support_phone
 * @property string|null $support_email
 * @property string|null $image
 * @property string|null $notes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Component> $components
 * @property-read int|null $components_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable> $consumables
 * @property-read int|null $consumables_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetModel> $models
 * @property-read int|null $models_count
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\ManufacturerFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer newModelQuery()
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manufacturer onlyTrashed()
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer query()
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer textSearch($search)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereCreatedAt($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereCreatedBy($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereDeletedAt($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereId($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereImage($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereName($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereNotes($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereSupportEmail($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereSupportPhone($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereSupportUrl($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereTagColor($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereTagSlug($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereUpdatedAt($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereUrl($value)
 * @method static \App\Models\Builders\ManufacturerQueryBuilder<static>|Manufacturer whereWarrantyLookupUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manufacturer withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Manufacturer withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperManufacturer {}
}

namespace App\Models{
/**
 * Acquisition record for inventory. One row per real-world order with
 * an optional order_number, supplier, purchase_date, currency, and
 * company scope. Line items (things acquired, quantities, prices) live
 * on the OrderItem polymorphic pivot so a single Order can carry
 * accessories, consumables, components, assets, and license seats
 * side by side.
 *
 * Explicitly NOT a purchase-order workflow (no state machine,
 * approvals, receiving). Just the "what was acquired, when, from
 * whom" record that the adjust-quantity flow, the AssetObserver, and
 * the CSV importers write to.
 *
 * File attachments (receipt / invoice PDFs) are not modeled here yet.
 *
 * @property int $id
 * @property string|null $order_number
 * @property int|null $supplier_id
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $purchase_date
 * @property int|null $created_by
 * @property string|null $notes
 * @property string|null $currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $admin
 * @property-read \App\Models\User|null $adminuser
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $warranty_months
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order applyOffsetAndLimit(int $total)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOrder {}
}

namespace App\Models{
/**
 * One line on an Order. Polymorphic to the inventory model that was
 * acquired (Accessory / Consumable / Component / Asset / License) so a
 * single Order can span multiple item types. qty is the number of units
 * on this line. Price is the per-unit cost (matches the semantic that
 * the existing purchase_cost columns use across the codebase).
 *
 * @property int $id
 * @property int $order_id
 * @property string $item_type
 * @property int $item_id
 * @property int $qty
 * @property numeric|null $price
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Actionlog|null $actionlog
 * @property-read \App\Models\User|null $admin
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $item
 * @property-read \App\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereItemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOrderItem {}
}

namespace App\Models{
/**
 * Model for predefined kits.
 *
 * @author [D. Minaev.] [<dmitriy.minaev.v@gmail.com>]
 * @version v1.0
 * @property int $id
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable> $consumables
 * @property-read int|null $consumables_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetModel> $models
 * @property-read int|null $models_count
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\PredefinedKitFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit newModelQuery()
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit newQuery()
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit query()
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit textSearch($search)
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit whereCreatedAt($value)
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit whereCreatedBy($value)
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit whereId($value)
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit whereName($value)
 * @method static \App\Models\Builders\PredefinedKitQueryBuilder<static>|PredefinedKit whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPredefinedKit {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $created_by
 * @property string $type
 * @property string $name
 * @property array<array-key, mixed> $options
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool|null $is_shared
 * @property-read \App\Models\User|null $creator
 * @property-read mixed $display_name
 * @method static \Database\Factories\ReportTemplateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate whereIsShared($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportTemplate withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperReportTemplate {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $permissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property string|null $notes
 * @property string|null $scim_externalid
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup newModelQuery()
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup newQuery()
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup query()
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup textSearch($search)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup whereCreatedAt($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup whereCreatedBy($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup whereId($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup whereName($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup whereNotes($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup wherePermissions($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup whereScimExternalid($value)
 * @method static \App\Models\Builders\GroupQueryBuilder<static>|SCIMGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSCIMGroup {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $email
 * @property string $password
 * @property string|null $permissions
 * @property int $activated
 * @property int|null $created_by
 * @property string|null $activation_code
 * @property string|null $activated_at
 * @property string|null $last_login
 * @property string|null $persist_code
 * @property string|null $reset_password_code
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $display_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $website
 * @property string|null $country
 * @property string|null $gravatar
 * @property int|null $location_id
 * @property string|null $phone
 * @property string|null $mobile
 * @property string|null $jobtitle
 * @property int|null $manager_id
 * @property string|null $employee_num
 * @property string|null $avatar
 * @property string|null $username
 * @property string|null $notes
 * @property int|null $legacy_company_id
 * @property string|null $remember_token
 * @property int $ldap_import
 * @property string|null $locale
 * @property int $show_in_list
 * @property string|null $two_factor_secret
 * @property int $two_factor_enrolled
 * @property int $two_factor_optin
 * @property int|null $department_id
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $skin
 * @property string|null $nav_link_color
 * @property string|null $link_light_color
 * @property string|null $link_dark_color
 * @property int|null $remote
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $scim_externalid
 * @property int $autoassign_licenses
 * @property int|null $vip
 * @property int $enable_sounds
 * @property int $enable_confetti
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $acceptances
 * @property-read int|null $acceptances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assetlog
 * @property-read int|null $assetlog_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Maintenance> $assignedMaintenances
 * @property-read int|null $assigned_maintenances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CalendarEvent> $calendarEvents
 * @property-read int|null $calendar_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $checkoutRequests
 * @property-read int|null $checkout_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Company> $companies
 * @property-read int|null $companies_count
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable> $consumables
 * @property-read int|null $consumables_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $directLicenses
 * @property-read int|null $direct_licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $eulas
 * @property-read int|null $eulas_count
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read string $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Maintenance> $maintenances
 * @property-read int|null $maintenances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Location> $managedLocations
 * @property-read int|null $managed_locations_count
 * @property-read \App\Models\User|null $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $managesUsers
 * @property-read int|null $manages_users_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read mixed $purchase_date_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReportTemplate> $reportTemplates
 * @property-read int|null $report_templates_count
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @property-read \App\Models\Location|null $userloc
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $userlog
 * @property-read int|null $userlog_count
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser byGroup(int $id)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser getNotDeleted()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser matchEmailOrUsername(string $user_username, string $user_email)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser newModelQuery()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser newQuery()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser onlyAdminsAndSuperAdmins()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser onlySuperAdmins()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SCIMUser onlyTrashed()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser orderCompany(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser orderDepartment(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser orderLocation(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser orderManager(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser query()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser simpleNameSearch(string $search)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser textSearch($search)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser userLocation(int $location, string $search)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereActivated($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereActivatedAt($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereActivationCode($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereAddress($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereAutoassignLicenses($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereAvatar($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereCity($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereCountry($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereCreatedAt($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereCreatedBy($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereDeletedAt($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereDepartmentId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereDisplayName($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereEmail($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereEmployeeNum($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereEnableConfetti($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereEnableSounds($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereEndDate($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereFirstName($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereGravatar($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereJobtitle($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereLastLogin($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereLastName($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereLdapImport($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereLegacyCompanyId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereLinkDarkColor($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereLinkLightColor($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereLocale($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereLocationId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereManagerId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereMobile($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereNavLinkColor($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereNotes($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser wherePassword($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser wherePermissions($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser wherePersistCode($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser wherePhone($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereRememberToken($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereRemote($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereResetPasswordCode($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereScimExternalid($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereShowInList($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereSkin($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereStartDate($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereState($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereTwoFactorEnrolled($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereTwoFactorOptin($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereTwoFactorSecret($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereUpdatedAt($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereUsername($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereVip($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereWebsite($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser whereZip($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|SCIMUser withInventoryRelations(int $id, bool $withAssets = true, bool $withLicenses = true, bool $withAccessories = true, bool $withConsumables = true, bool $withComponents = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SCIMUser withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SCIMUser withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSCIMUser {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nonce
 * @property string $not_valid_after
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SamlNonce newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SamlNonce newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SamlNonce query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SamlNonce whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SamlNonce whereNonce($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SamlNonce whereNotValidAfter($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSamlNonce {}
}

namespace App\Models{
/**
 * Named filter-preset for a listing page's advanced-search. Each
 * record stores a URL query string captured from the advanced-search
 * modal. Private to the creator for v1 (no sharing/default yet).
 *
 * @property int $id
 * @property int $created_by
 * @property string $table_name
 * @property string|null $model
 * @property string $name
 * @property string $query_string
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @method static \Database\Factories\SavedSearchFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch forTable(string $tableName)
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch newModelQuery()
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch newQuery()
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch ownedByCurrent()
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch query()
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch whereCreatedAt($value)
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch whereCreatedBy($value)
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch whereId($value)
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch whereModel($value)
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch whereName($value)
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch whereQueryString($value)
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch whereTableName($value)
 * @method static \App\Models\Builders\SavedSearchQueryBuilder<static>|SavedSearch whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSavedSearch {}
}

namespace App\Models{
/**
 * Settings model.
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property int $per_page
 * @property string $site_name
 * @property int|null $qr_code
 * @property string|null $qr_text
 * @property int|null $display_asset_name
 * @property int|null $display_checkout_date
 * @property int|null $display_eol
 * @property int $auto_increment_assets
 * @property string|null $auto_increment_prefix
 * @property int $scope_counters_by_company
 * @property int $load_remote
 * @property string|null $logo
 * @property string|null $header_color
 * @property string|null $nav_link_color
 * @property string|null $link_light_color
 * @property string|null $link_dark_color
 * @property string|null $alert_email
 * @property int $alerts_enabled
 * @property string|null $default_eula_text
 * @property string|null $webhook_endpoint
 * @property string|null $webhook_channel
 * @property string|null $webhook_botname
 * @property string|null $webhook_selected
 * @property string|null $default_currency
 * @property string|null $custom_css
 * @property int $brand
 * @property string|null $ldap_enabled
 * @property string|null $ldap_server
 * @property string|null $ldap_uname
 * @property string|null $ldap_pword
 * @property string|null $ldap_basedn
 * @property string|null $ldap_default_group
 * @property string|null $ldap_filter
 * @property string|null $ldap_username_field
 * @property string|null $ldap_lname_field
 * @property string|null $ldap_fname_field
 * @property string|null $ldap_display_name
 * @property string|null $ldap_auth_filter_query
 * @property int|null $ldap_version
 * @property string|null $ldap_active_flag
 * @property string|null $ldap_dept
 * @property string|null $ldap_emp_num
 * @property string|null $ldap_email
 * @property string|null $ldap_phone_field
 * @property string|null $ldap_mobile
 * @property string|null $ldap_jobtitle
 * @property string|null $ldap_manager
 * @property string|null $ldap_address
 * @property string|null $ldap_city
 * @property string|null $ldap_state
 * @property string|null $ldap_zip
 * @property string|null $ldap_country
 * @property string|null $ldap_location
 * @property int $full_multiple_companies_support
 * @property int $scope_locations_fmcs
 * @property int $null_company_is_floater
 * @property bool $block_api_user_agents
 * @property string|null $blocked_api_user_agents
 * @property bool $block_blank_api_user_agents
 * @property int $ldap_server_cert_ignore
 * @property string|null $locale
 * @property int|null $week_start
 * @property int $labels_per_page
 * @property numeric $labels_width
 * @property numeric $labels_height
 * @property numeric $labels_pmargin_left
 * @property numeric $labels_pmargin_right
 * @property numeric $labels_pmargin_top
 * @property numeric $labels_pmargin_bottom
 * @property numeric $labels_display_bgutter
 * @property numeric $labels_display_sgutter
 * @property int $labels_fontsize
 * @property numeric $labels_pagewidth
 * @property numeric $labels_pageheight
 * @property int $labels_display_name
 * @property int $labels_display_serial
 * @property int $labels_display_tag
 * @property int|null $alt_barcode_enabled
 * @property int|null $alert_interval
 * @property int|null $alert_threshold
 * @property string|null $name_display_format
 * @property string|null $email_domain
 * @property string|null $email_format
 * @property string|null $username_format
 * @property int $is_ad
 * @property string|null $ad_domain
 * @property string $ldap_port
 * @property int $ldap_tls
 * @property int $zerofill_count
 * @property int $ldap_pw_sync
 * @property string|null $two_factor_enabled
 * @property int $require_accept_signature
 * @property string $date_display_format
 * @property string $time_display_format
 * @property int $next_auto_tag_base
 * @property string|null $login_note
 * @property int|null $thumbnail_max_h
 * @property int $pwd_secure_uncommon
 * @property string|null $pwd_secure_complexity
 * @property int $pwd_secure_min
 * @property int|null $audit_interval
 * @property int|null $audit_warning_days
 * @property int $show_url_in_emails
 * @property string|null $custom_forgot_pass_url
 * @property int $show_alerts_in_menu
 * @property int $labels_display_company_name
 * @property int $show_archived_in_list
 * @property string|null $dashboard_message
 * @property string|null $support_footer
 * @property string|null $footer_text
 * @property string|null $modellist_displays
 * @property int $login_remote_user_enabled
 * @property int $login_common_disabled
 * @property string $login_remote_user_custom_logout_url
 * @property string|null $skin
 * @property int $show_images_in_email
 * @property string|null $admin_cc_email
 * @property int $admin_cc_always
 * @property int $labels_display_model
 * @property string|null $privacy_policy_link
 * @property string|null $version_footer
 * @property int $unique_serial
 * @property int $logo_print_assets
 * @property string|null $depreciation_method
 * @property string|null $favicon
 * @property string|null $default_avatar
 * @property string|null $email_logo
 * @property string|null $label_logo
 * @property string|null $acceptance_pdf_logo
 * @property int $allow_user_skin
 * @property int $show_assigned_assets
 * @property string $login_remote_user_header_name
 * @property int $ad_append_domain
 * @property int $saml_enabled
 * @property string|null $saml_idp_metadata
 * @property string|null $saml_attr_mapping_username
 * @property int $saml_forcelogin
 * @property int $saml_slo
 * @property string|null $saml_sp_x509cert
 * @property string|null $saml_sp_privatekey
 * @property string|null $saml_custom_settings
 * @property string|null $saml_sp_x509certNew
 * @property string|null $digit_separator
 * @property string|null $ldap_client_tls_cert
 * @property int $ldap_use_sasl_external_bind
 * @property string|null $ldap_client_tls_key
 * @property string|null $dash_chart_type
 * @property int $label2_enable
 * @property string|null $label2_template
 * @property string|null $label2_title
 * @property bool $label2_asset_logo
 * @property string $label2_1d_type
 * @property string $label2_2d_type
 * @property string|null $label2_2d_prefix
 * @property string $label2_2d_target
 * @property string $label2_fields
 * @property int $label2_empty_row_count
 * @property int|null $google_login
 * @property string|null $google_client_id
 * @property string|null $google_client_secret
 * @property int|null $profile_edit
 * @property bool|null $require_checkinout_notes
 * @property int $shortcuts_enabled
 * @property int|null $due_checkin_days
 * @property int $ldap_invert_active_flag
 * @property bool $manager_view_enabled Allow managers to view assets assigned to their subordinates
 * @property string|null $fleet_url
 * @property string|null $fleet_token
 * @property-read string $label2_template_display
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\SettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAcceptancePdfLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAdAppendDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAdDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAdminCcAlways($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAdminCcEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAlertEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAlertInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAlertThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAlertsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAllowUserSkin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAltBarcodeEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAuditInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAuditWarningDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAutoIncrementAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAutoIncrementPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereBlockApiUserAgents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereBlockBlankApiUserAgents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereBlockedApiUserAgents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCustomCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCustomForgotPassUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDashChartType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDashboardMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDateDisplayFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDefaultAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDefaultCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDefaultEulaText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDepreciationMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDigitSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDisplayAssetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDisplayCheckoutDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDisplayEol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDueCheckinDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEmailDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEmailFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEmailLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFleetToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFleetUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFooterText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFullMultipleCompaniesSupport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereGoogleClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereGoogleClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereGoogleLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereHeaderColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereIsAd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel21dType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel22dPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel22dTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel22dType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel2AssetLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel2EmptyRowCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel2Enable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel2Fields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel2Template($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabel2Title($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsDisplayBgutter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsDisplayCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsDisplayModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsDisplaySerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsDisplaySgutter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsDisplayTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsFontsize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsPageheight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsPagewidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsPerPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsPmarginBottom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsPmarginLeft($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsPmarginRight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsPmarginTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLabelsWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapActiveFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapAuthFilterQuery($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapBasedn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapClientTlsCert($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapClientTlsKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapDefaultGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapDept($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapEmpNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapFilter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapFnameField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapInvertActiveFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapJobtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapLnameField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapManager($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapPhoneField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapPwSync($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapPword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapServer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapServerCertIgnore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapTls($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapUname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapUseSaslExternalBind($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapUsernameField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLdapZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLinkDarkColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLinkLightColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLoadRemote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLoginCommonDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLoginNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLoginRemoteUserCustomLogoutUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLoginRemoteUserEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLoginRemoteUserHeaderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogoPrintAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereManagerViewEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereModellistDisplays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereNameDisplayFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereNavLinkColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereNextAutoTagBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereNullCompanyIsFloater($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePerPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePrivacyPolicyLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereProfileEdit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePwdSecureComplexity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePwdSecureMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePwdSecureUncommon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereQrCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereQrText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereRequireAcceptSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereRequireCheckinoutNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSamlAttrMappingUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSamlCustomSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSamlEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSamlForcelogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSamlIdpMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSamlSlo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSamlSpPrivatekey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSamlSpX509cert($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSamlSpX509certNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereScopeCountersByCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereScopeLocationsFmcs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereShortcutsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereShowAlertsInMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereShowArchivedInList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereShowAssignedAssets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereShowImagesInEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereShowUrlInEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSiteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSkin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSupportFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereThumbnailMaxH($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTimeDisplayFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTwoFactorEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUniqueSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUsernameFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereVersionFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereWebhookBotname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereWebhookChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereWebhookEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereWebhookSelected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereWeekStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereZerofillCount($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSetting {}
}

namespace App\Models{
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SnipeModel applyOffsetAndLimit(int $total)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SnipeModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SnipeModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SnipeModel query()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSnipeModel {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $deployable
 * @property int $pending
 * @property int $archived
 * @property string|null $notes
 * @property string|null $color
 * @property int|null $show_in_nav
 * @property int|null $default_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel archived()
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel deployable()
 * @method static \Database\Factories\StatuslabelFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel newModelQuery()
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statuslabel onlyTrashed()
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel pending()
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel query()
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel textSearch($search)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel undeployable()
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereArchived($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereColor($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereCreatedAt($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereCreatedBy($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereDefaultLabel($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereDeletedAt($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereDeployable($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereId($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereName($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereNotes($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel wherePending($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereShowInNav($value)
 * @method static \App\Models\Builders\StatuslabelQueryBuilder<static>|Statuslabel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statuslabel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statuslabel withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperStatuslabel {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $tag_color
 * @property string|null $address
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $phone
 * @property string|null $fax
 * @property string|null $email
 * @property string|null $contact
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $zip
 * @property string|null $url
 * @property string|null $image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read \App\Models\User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assetsRelation
 * @property-read int|null $assets_relation_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Component> $components
 * @property-read int|null $components_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable> $consumables
 * @property-read int|null $consumables_count
 * @property-read mixed $display_name
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Maintenance> $maintenances
 * @property-read int|null $maintenances_count
 * @property-read mixed $purchase_date_formatted
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $department_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $location_id
 * @property-write mixed $manager_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier applyOffsetAndLimit(int $total)
 * @method static \Database\Factories\SupplierFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier newModelQuery()
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier onlyTrashed()
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier orderByCreatedByName(string $order)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier query()
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier textSearch($search)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereAddress($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereAddress2($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereCity($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereContact($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereCountry($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereCreatedAt($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereCreatedBy($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereDeletedAt($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereEmail($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereFax($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereId($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereImage($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereName($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereNotes($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier wherePhone($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereState($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereTagColor($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereUpdatedAt($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereUrl($value)
 * @method static \App\Models\Builders\SupplierQueryBuilder<static>|Supplier whereZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSupplier {}
}

namespace App\Models{
/**
 * One configured instance of a sync adapter. Multiple instances of the
 * same adapter_type are allowed (e.g., two Fleet installs, one Fleet + one
 * Kandji), each with independent credentials, own tab on the settings
 * page, own row in asset_external_sources where source = the instance slug.
 *
 * Slugs auto-generate from the label at create time and are immutable
 * after, because asset_external_sources.source references them and reassigning
 * slugs later would orphan assets.
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $slug
 * @property string $adapter_type
 * @property string $label
 * @property bool $active
 * @property array<array-key, mixed>|null $config
 * @property \Illuminate\Support\Carbon|null $last_synced_at
 * @property string|null $last_sync_result
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereAdapterType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereLastSyncResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereLastSyncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SyncAdapterInstance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSyncAdapterInstance {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $email
 * @property string $password
 * @property string|null $permissions
 * @property int $activated
 * @property int|null $created_by
 * @property string|null $activation_code
 * @property string|null $activated_at
 * @property string|null $last_login
 * @property string|null $persist_code
 * @property string|null $reset_password_code
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $display_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $website
 * @property string|null $country
 * @property string|null $gravatar
 * @property int|null $location_id
 * @property string|null $phone
 * @property string|null $mobile
 * @property string|null $jobtitle
 * @property int|null $manager_id
 * @property string|null $employee_num
 * @property string|null $avatar
 * @property string|null $username
 * @property string|null $notes
 * @property int|null $legacy_company_id
 * @property string|null $remember_token
 * @property int $ldap_import
 * @property string|null $locale
 * @property int $show_in_list
 * @property string|null $two_factor_secret
 * @property int $two_factor_enrolled
 * @property int $two_factor_optin
 * @property int|null $department_id
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $skin
 * @property string|null $nav_link_color
 * @property string|null $link_light_color
 * @property string|null $link_dark_color
 * @property int|null $remote
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $scim_externalid
 * @property int $autoassign_licenses
 * @property int|null $vip
 * @property int $enable_sounds
 * @property int $enable_confetti
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $acceptances
 * @property-read int|null $acceptances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accessory> $accessories
 * @property-read int|null $accessories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $actionlog
 * @property-read int|null $actionlog_count
 * @property-read User|null $adminuser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assetlog
 * @property-read int|null $assetlog_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $assets
 * @property-read int|null $assets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Maintenance> $assignedMaintenances
 * @property-read int|null $assigned_maintenances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CalendarEvent> $calendarEvents
 * @property-read int|null $calendar_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asset> $checkoutRequests
 * @property-read int|null $checkout_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Company> $companies
 * @property-read int|null $companies_count
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable> $consumables
 * @property-read int|null $consumables_count
 * @property-read User|null $createdBy
 * @property-read \App\Models\Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $directLicenses
 * @property-read int|null $direct_licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $eulas
 * @property-read int|null $eulas_count
 * @property-read mixed $expires_diff_for_humans
 * @property-read mixed $expires_diff_in_days
 * @property-read mixed $expires_formatted_date
 * @property-read string $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $log
 * @property-read int|null $log_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Maintenance> $maintenances
 * @property-read int|null $maintenances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Location> $managedLocations
 * @property-read int|null $managed_locations_count
 * @property-read User|null $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $managesUsers
 * @property-read int|null $manages_users_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read mixed $purchase_date_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReportTemplate> $reportTemplates
 * @property-read int|null $report_templates_count
 * @property-write mixed $category_id
 * @property-write mixed $company_id
 * @property-write mixed $depreciation_id
 * @property-write mixed $field_set_id
 * @property-write mixed $manufacturer_id
 * @property-write mixed $min_amt
 * @property-write mixed $model_id
 * @property-write mixed $parent_id
 * @property-write mixed $purchase_cost
 * @property-write mixed $purchase_date
 * @property-write mixed $rtd_location_id
 * @property-write mixed $status_id
 * @property-write mixed $supplier_id
 * @property-write mixed $warranty_months
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $uploads
 * @property-read int|null $uploads_count
 * @property-read \App\Models\Location|null $userloc
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Actionlog> $userlog
 * @property-read int|null $userlog_count
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User applyOffsetAndLimit(int $total)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User byGroup(int $id)
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User getNotDeleted()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User matchEmailOrUsername(string $user_username, string $user_email)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User newModelQuery()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User newQuery()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User onlyAdminsAndSuperAdmins()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User onlySuperAdmins()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User orWhereMultipleColumns(array $columns, $term)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User orderByCreatedBy(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User orderCompany(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User orderDepartment(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User orderLocation(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User orderManager(string $order)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User query()
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User simpleNameSearch(string $search)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User textSearch($search)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User userLocation(int $location, string $search)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereActivated($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereActivatedAt($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereActivationCode($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereAddress($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereAutoassignLicenses($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereAvatar($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereCity($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereCountry($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereCreatedAt($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereCreatedBy($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereDeletedAt($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereDepartmentId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereDisplayName($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereEmail($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereEmployeeNum($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereEnableConfetti($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereEnableSounds($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereEndDate($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereFirstName($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereGravatar($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereJobtitle($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereLastLogin($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereLastName($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereLdapImport($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereLegacyCompanyId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereLinkDarkColor($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereLinkLightColor($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereLocale($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereLocationId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereManagerId($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereMobile($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereNavLinkColor($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereNotes($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User wherePassword($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User wherePermissions($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User wherePersistCode($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User wherePhone($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereRememberToken($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereRemote($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereResetPasswordCode($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereScimExternalid($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereShowInList($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereSkin($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereStartDate($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereState($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereTwoFactorEnrolled($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereTwoFactorOptin($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereTwoFactorSecret($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereUpdatedAt($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereUsername($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereVip($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereWebsite($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User whereZip($value)
 * @method static \App\Models\Builders\UserQueryBuilder<static>|User withInventoryRelations(int $id, bool $withAssets = true, bool $withLicenses = true, bool $withAccessories = true, bool $withConsumables = true, bool $withComponents = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

