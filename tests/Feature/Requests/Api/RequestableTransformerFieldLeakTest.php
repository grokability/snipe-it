<?php

namespace Tests\Feature\Requests\Api;

use App\Models\Accessory;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for GHSA-g85w-9jhf-37h8. The /account/requestable
 * tabs are open to any authenticated user (that is intentional - the
 * page itself is open), and previously each tab's API endpoint emitted
 * the full admin transformer shape, which leaked purchase_cost,
 * supplier, notes, order_number, purchase_order, and for licenses the
 * license_name + license_email licensee contact details to callers
 * without the corresponding `view` permission. The fix adds a reduced
 * `transformRequested*` method on each transformer, matching the shape
 * `AssetsTransformer::transformRequestedAsset` already uses for the
 * hardware tab, and the four `requestable()` controllers now call it.
 *
 * Each test here is run as a user holding only `assets.view.requestable`
 * (granted via `->viewRequestableAssets()`). That permission now gates
 * all five requestable endpoints after GHSA-944j-96m5-3vpp +
 * GHSA-wgj5-4mrx-52rx, which closed the "no authz check at all" gap on
 * the four non-Asset variants. See `RequestableAuthzAndSearchOracleTest`
 * for the gate + search-oracle regression coverage.
 */
class RequestableTransformerFieldLeakTest extends TestCase
{
    public function test_accessories_requestable_response_does_not_expose_sensitive_fields(): void
    {
        Accessory::factory()->create(['requestable' => 1]);

        $row = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.accessories.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertNotNull($row);
        foreach (['purchase_cost', 'total_cost', 'supplier', 'notes', 'orders', 'purchase_date', 'min_amt', 'created_by'] as $leakedField) {
            $this->assertArrayNotHasKey(
                $leakedField,
                $row,
                "Field `{$leakedField}` must not appear in the /account/requestable accessories response. It leaks admin-only data to callers without accessories.view."
            );
        }

        // Positive assertions on the fields the UI formatter needs.
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('remaining_qty', $row);
        $this->assertArrayHasKey('assigned_to_self', $row);
        $this->assertArrayHasKey('request', $row['available_actions']);
    }

    public function test_consumables_requestable_response_does_not_expose_sensitive_fields(): void
    {
        Consumable::factory()->create(['requestable' => 1]);

        $row = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.consumables.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertNotNull($row);
        foreach (['purchase_cost', 'total_cost', 'supplier', 'notes', 'orders', 'purchase_date', 'min_amt', 'created_by'] as $leakedField) {
            $this->assertArrayNotHasKey(
                $leakedField,
                $row,
                "Field `{$leakedField}` must not appear in the /account/requestable consumables response."
            );
        }

        $this->assertArrayHasKey('remaining', $row);
        $this->assertArrayHasKey('assigned_to_self', $row);
        $this->assertArrayHasKey('request', $row['available_actions']);
    }

    public function test_components_requestable_response_does_not_expose_sensitive_fields(): void
    {
        Component::factory()->create(['requestable' => 1]);

        $row = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.components.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertNotNull($row);
        foreach (['purchase_cost', 'total_cost', 'supplier', 'notes', 'orders', 'purchase_date', 'min_amt', 'created_by'] as $leakedField) {
            $this->assertArrayNotHasKey(
                $leakedField,
                $row,
                "Field `{$leakedField}` must not appear in the /account/requestable components response."
            );
        }

        $this->assertArrayHasKey('remaining', $row);
        $this->assertArrayHasKey('assigned_to_self', $row);
        $this->assertArrayHasKey('request', $row['available_actions']);
    }

    public function test_models_requestable_response_does_not_expose_sensitive_fields(): void
    {
        // GHSA-8m7q-2x28-8wh3 was the fifth requestable endpoint missed by
        // the original g85w fix. AssetModelsTransformer had no reduced
        // `transformRequested*` method, so the controller returned the
        // full admin shape (notes, default_fieldset_values with every
        // custom field's name / db_column / default / format / required,
        // min_amt, eol, depreciation, require_serial, assets_count +
        // _assigned + _archived, remaining, percent_remaining,
        // created_by, qr_code_url) to any caller holding only
        // `assets.view.requestable`.
        AssetModel::factory()->create(['requestable' => 1]);

        $row = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.assetmodels.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertNotNull($row);
        foreach ([
            'notes', 'default_fieldset_values', 'min_amt', 'created_by',
            'depreciation', 'eol', 'require_serial', 'fieldset',
            'assets_count', 'assets_assigned_count', 'assets_archived_count',
            'percent_remaining', 'qr_code_url', 'category', 'manufacturer',
        ] as $leakedField) {
            $this->assertArrayNotHasKey(
                $leakedField,
                $row,
                "Field `{$leakedField}` must not appear in the /account/requestable models response."
            );
        }

        // Positive-path: the fields the UI tab's row formatter needs.
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('model_number', $row);
        $this->assertArrayHasKey('remaining', $row);
        $this->assertArrayHasKey('assigned_to_self', $row);
        $this->assertArrayHasKey('request', $row['available_actions']);
    }

    public function test_licenses_requestable_response_does_not_expose_sensitive_fields(): void
    {
        License::factory()->create([
            'requestable' => 1,
            'purchase_cost' => '499.99',
            'order_number' => 'ORD-INTERNAL-1234',
            'purchase_order' => 'PO-INTERNAL-5678',
            'notes' => 'Admin-only context about this license',
            'license_name' => 'Alice Admin',
            'license_email' => 'alice@internal.example',
        ]);

        $row = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.licenses.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertNotNull($row);
        foreach (['purchase_cost', 'purchase_cost_numeric', 'supplier', 'notes', 'order_number', 'purchase_order', 'purchase_date', 'license_name', 'license_email', 'product_key', 'min_amt', 'created_by'] as $leakedField) {
            $this->assertArrayNotHasKey(
                $leakedField,
                $row,
                "Field `{$leakedField}` must not appear in the /account/requestable licenses response. It leaks admin-only data (including licensee contact details) to callers without licenses.view."
            );
        }

        $this->assertArrayHasKey('free_seats_count', $row);
        $this->assertArrayHasKey('category', $row);
        $this->assertArrayHasKey('assigned_to_self', $row);
        $this->assertArrayHasKey('request', $row['available_actions']);
    }
}
