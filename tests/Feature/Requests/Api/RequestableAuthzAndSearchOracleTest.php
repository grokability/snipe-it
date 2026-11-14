<?php

namespace Tests\Feature\Requests\Api;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\License;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for GHSA-944j-96m5-3vpp (accessories / consumables /
 * components) and GHSA-wgj5-4mrx-52rx (licenses). The four non-Asset
 * requestable endpoints had two paired gaps.
 *
 * 1. Authz: no `authorize()` call. Any authenticated user could hit them
 *    regardless of role. AssetController::requestable gated on
 *    `assets.view.requestable` but the other four did not. Fix: added
 *    `viewRequestable` policy methods on AccessoryPolicy, ConsumablePolicy,
 *    ComponentPolicy, LicensePolicy, each reusing the same
 *    `assets.view.requestable` permission so the five requestable
 *    endpoints share one consistent gate.
 *
 * 2. Search oracle: `TextSearch` on the request `search` param walked
 *    each model's full `$searchableAttributes`, which included
 *    admin-only columns (`notes` on accessories / consumables /
 *    components, `serial` + `notes` + `license_name` + `license_email` +
 *    `order_number` + `purchase_order` on licenses). The output-side
 *    transformer stripped those fields but the result-count
 *    differential still let a caller use the search param as a content
 *    oracle. Fix: scoped the search columns in each `requestable()`
 *    controller to the exact set the UI tab exposes (`name` +
 *    `model_number` on the three items, `name` on licenses).
 */
class RequestableAuthzAndSearchOracleTest extends TestCase
{
    private const NOTES_SENTINEL = 'INTERNAL-REORDER-FROM-ACME-ONLY';

    private const LICENSE_SERIAL_SENTINEL = 'ZX9Q7M2K-SECRET-PRODUCT-KEY';

    public function test_accessories_requestable_requires_assets_view_requestable_permission(): void
    {
        Accessory::factory()->create(['requestable' => 1]);

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.accessories.requestable'))
            ->assertForbidden();
    }

    public function test_consumables_requestable_requires_assets_view_requestable_permission(): void
    {
        Consumable::factory()->create(['requestable' => 1]);

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.consumables.requestable'))
            ->assertForbidden();
    }

    public function test_components_requestable_requires_assets_view_requestable_permission(): void
    {
        Component::factory()->create(['requestable' => 1]);

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.components.requestable'))
            ->assertForbidden();
    }

    public function test_licenses_requestable_requires_assets_view_requestable_permission(): void
    {
        License::factory()->create(['requestable' => 1]);

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.licenses.requestable'))
            ->assertForbidden();
    }

    public function test_models_requestable_requires_assets_view_requestable_permission(): void
    {
        AssetModel::factory()->create(['requestable' => 1]);

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.assetmodels.requestable'))
            ->assertForbidden();
    }

    public function test_models_requestable_search_cannot_oracle_notes(): void
    {
        AssetModel::factory()->create([
            'requestable' => 1,
            'name' => 'MacBook Pro M3',
            'notes' => self::NOTES_SENTINEL,
        ]);

        $caller = User::factory()->viewRequestableAssets()->create();

        $this->assertSame(0, $this->actingAsForApi($caller)
            ->getJson(route('api.assetmodels.requestable', ['search' => 'REORDER-FROM-ACME']))
            ->assertOk()
            ->json('total'));

        $this->assertGreaterThan(0, $this->actingAsForApi($caller)
            ->getJson(route('api.assetmodels.requestable', ['search' => 'MacBook']))
            ->assertOk()
            ->json('total'));
    }

    public function test_accessories_requestable_search_cannot_oracle_notes(): void
    {
        Accessory::factory()->create([
            'requestable' => 1,
            'name' => 'Headphones',
            'notes' => self::NOTES_SENTINEL,
        ]);

        $caller = User::factory()->viewRequestableAssets()->create();

        $bySentinelSubstring = $this->actingAsForApi($caller)
            ->getJson(route('api.accessories.requestable', ['search' => 'REORDER-FROM-ACME']))
            ->assertOk()
            ->json();
        $this->assertSame(0, $bySentinelSubstring['total']);

        $structuredOracle = $this->actingAsForApi($caller)
            ->getJson(route('api.accessories.requestable', [
                'search' => '{"notes":"is:'.self::NOTES_SENTINEL.'"}',
            ]))
            ->assertOk()
            ->json();
        $this->assertSame(0, $structuredOracle['total']);

        $byName = $this->actingAsForApi($caller)
            ->getJson(route('api.accessories.requestable', ['search' => 'Headphones']))
            ->assertOk()
            ->json();
        $this->assertGreaterThan(0, $byName['total']);
    }

    public function test_consumables_requestable_search_cannot_oracle_notes(): void
    {
        Consumable::factory()->create([
            'requestable' => 1,
            'name' => 'Printer Paper',
            'notes' => self::NOTES_SENTINEL,
        ]);

        $caller = User::factory()->viewRequestableAssets()->create();

        $this->assertSame(0, $this->actingAsForApi($caller)
            ->getJson(route('api.consumables.requestable', ['search' => 'REORDER-FROM-ACME']))
            ->assertOk()
            ->json('total'));

        $this->assertGreaterThan(0, $this->actingAsForApi($caller)
            ->getJson(route('api.consumables.requestable', ['search' => 'Printer']))
            ->assertOk()
            ->json('total'));
    }

    public function test_components_requestable_search_cannot_oracle_notes(): void
    {
        Component::factory()->create([
            'requestable' => 1,
            'name' => 'RAM Module',
            'notes' => self::NOTES_SENTINEL,
        ]);

        $caller = User::factory()->viewRequestableAssets()->create();

        $this->assertSame(0, $this->actingAsForApi($caller)
            ->getJson(route('api.components.requestable', ['search' => 'REORDER-FROM-ACME']))
            ->assertOk()
            ->json('total'));

        $this->assertGreaterThan(0, $this->actingAsForApi($caller)
            ->getJson(route('api.components.requestable', ['search' => 'RAM']))
            ->assertOk()
            ->json('total'));
    }

    public function test_licenses_requestable_search_cannot_oracle_serial(): void
    {
        License::factory()->create([
            'requestable' => 1,
            'name' => 'Adobe CC',
            'serial' => self::LICENSE_SERIAL_SENTINEL,
            'notes' => self::NOTES_SENTINEL,
            'license_name' => 'Alice Admin',
            'license_email' => 'alice@internal.example',
        ]);

        $caller = User::factory()->viewRequestableAssets()->create();

        $bySerialSubstring = $this->actingAsForApi($caller)
            ->getJson(route('api.licenses.requestable', ['search' => 'ZX9Q7M2K']))
            ->assertOk()
            ->json();
        $this->assertSame(0, $bySerialSubstring['total']);

        $byNotesSubstring = $this->actingAsForApi($caller)
            ->getJson(route('api.licenses.requestable', ['search' => 'REORDER-FROM-ACME']))
            ->assertOk()
            ->json();
        $this->assertSame(0, $byNotesSubstring['total']);

        $byLicenseName = $this->actingAsForApi($caller)
            ->getJson(route('api.licenses.requestable', ['search' => 'Alice']))
            ->assertOk()
            ->json();
        $this->assertSame(0, $byLicenseName['total']);

        $byLicenseEmail = $this->actingAsForApi($caller)
            ->getJson(route('api.licenses.requestable', ['search' => 'alice@internal']))
            ->assertOk()
            ->json();
        $this->assertSame(0, $byLicenseEmail['total']);

        $byName = $this->actingAsForApi($caller)
            ->getJson(route('api.licenses.requestable', ['search' => 'Adobe']))
            ->assertOk()
            ->json();
        $this->assertGreaterThan(0, $byName['total']);
    }

    // GHSA-799x-8mjm-g656: the Asset requestable endpoint was the one
    // sibling that missed the TextSearch-scoping fix from commit 9328fe7b8
    // and continued to search notes, order_number, purchase_cost, and every
    // unencrypted custom-field column (including show_in_requestable_list=0
    // fields) through Asset::$searchableAttributes + searchCustomFields.
    // Total count served as a boolean oracle for substring guessing.

    public function test_assets_requestable_search_cannot_oracle_notes(): void
    {
        $this->seedRequestableAsset(['notes' => self::NOTES_SENTINEL]);
        $caller = User::factory()->viewRequestableAssets()->create();

        $bySentinelSubstring = $this->actingAsForApi($caller)
            ->getJson(route('api.assets.requestable', ['search' => 'REORDER-FROM-ACME']))
            ->assertOk()
            ->json();
        $this->assertSame(0, $bySentinelSubstring['total']);

        $byName = $this->actingAsForApi($caller)
            ->getJson(route('api.assets.requestable', ['search' => 'Oracle-Target']))
            ->assertOk()
            ->json();
        $this->assertGreaterThan(0, $byName['total']);
    }

    public function test_assets_requestable_search_cannot_oracle_order_number(): void
    {
        $this->seedRequestableAsset(['order_number' => 'PO-SECRET-7731']);
        $caller = User::factory()->viewRequestableAssets()->create();

        $this->assertSame(0, $this->actingAsForApi($caller)
            ->getJson(route('api.assets.requestable', ['search' => 'PO-SECRET-7731']))
            ->assertOk()
            ->json('total'));
    }

    public function test_assets_requestable_search_cannot_oracle_purchase_cost(): void
    {
        $this->seedRequestableAsset(['purchase_cost' => 4242.42]);
        $caller = User::factory()->viewRequestableAssets()->create();

        $this->assertSame(0, $this->actingAsForApi($caller)
            ->getJson(route('api.assets.requestable', ['search' => '4242.42']))
            ->assertOk()
            ->json('total'));
    }

    public function test_assets_requestable_search_cannot_oracle_hidden_custom_field(): void
    {
        $field = CustomField::factory()->create([
            'name' => 'Hidden Serial Prefix',
            'show_in_requestable_list' => '0',
            'field_encrypted' => 0,
        ]);
        $fieldset = CustomFieldset::factory()->create(['name' => 'Oracle Fieldset']);
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);

        $asset = $this->seedRequestableAsset([], $fieldset);
        $asset->{$field->db_column_name()} = 'CF-SECRET-CODE-9F3A1';
        $asset->save();

        $caller = User::factory()->viewRequestableAssets()->create();

        $bySubstring = $this->actingAsForApi($caller)
            ->getJson(route('api.assets.requestable', ['search' => 'SECRET-CODE-9F3A']))
            ->assertOk()
            ->json('total');
        $this->assertSame(0, $bySubstring);

        // Exact-match filter on the hidden field's db column must also be
        // ignored (the oracle also worked through per-field query params).
        $byExactMatch = $this->actingAsForApi($caller)
            ->getJson(route('api.assets.requestable', [
                $field->db_column_name() => 'CF-SECRET-CODE-9F3A1',
            ]))
            ->assertOk()
            ->json('total');
        $this->assertGreaterThan(
            0,
            $byExactMatch,
            'Exact-match filter on a show_in_requestable_list=0 field must be ignored (query runs unfiltered), not act as an oracle.'
        );
    }

    public function test_assets_requestable_exact_match_on_visible_custom_field_still_works(): void
    {
        $field = CustomField::factory()->create([
            'name' => 'Visible SKU',
            'show_in_requestable_list' => '1',
            'field_encrypted' => 0,
        ]);
        $fieldset = CustomFieldset::factory()->create(['name' => 'Visible Fieldset']);
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);

        $asset = $this->seedRequestableAsset([], $fieldset);
        $asset->{$field->db_column_name()} = 'VISIBLE-SKU-ABC';
        $asset->save();

        $caller = User::factory()->viewRequestableAssets()->create();

        $hit = $this->actingAsForApi($caller)
            ->getJson(route('api.assets.requestable', [
                $field->db_column_name() => 'VISIBLE-SKU-ABC',
            ]))
            ->assertOk()
            ->json('total');
        $this->assertGreaterThan(0, $hit);

        $miss = $this->actingAsForApi($caller)
            ->getJson(route('api.assets.requestable', [
                $field->db_column_name() => 'WRONG-VALUE',
            ]))
            ->assertOk()
            ->json('total');
        $this->assertSame(0, $miss);
    }

    private function seedRequestableAsset(array $overrides = [], ?CustomFieldset $fieldset = null): Asset
    {
        $status = Statuslabel::where('name', 'Ready to Deploy')->first()
            ?? Statuslabel::factory()->rtd()->create(['name' => 'Ready to Deploy']);

        $modelAttrs = $fieldset ? ['fieldset_id' => $fieldset->id] : [];

        return Asset::factory()->create(array_merge([
            'name' => 'Oracle-Target',
            'requestable' => 1,
            'status_id' => $status->id,
            'model_id' => \App\Models\AssetModel::factory()->create($modelAttrs)->id,
        ], $overrides));
    }
}
