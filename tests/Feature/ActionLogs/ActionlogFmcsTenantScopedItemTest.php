<?php

namespace Tests\Feature\ActionLogs;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for GHSA-m955-p5pg-78mv.
 *
 * Covers the scalar-company half of action_logs tenant scoping. Asset,
 * License, Accessory, Consumable, Component, Location, and Department
 * are tenant-scoped through a scalar company_id column on their own
 * tables. Action_logs that land with company_id = NULL for one of
 * these item types are historical rows from before the item was
 * tenant-assigned. Before the fix, CompanyableScope treated ALL null-
 * company non-User rows as safe admin-authored global config, so after
 * the item was later assigned to Company A, viewers in Company B could
 * still read the asset_tag / serial / name / notes / log_meta diff
 * from the pre-assignment logs through the Activity Report listing
 * endpoint.
 *
 * User is also tenant-scoped but through the company_user pivot, not
 * a scalar column. Pivot-based gating lives in the sibling file
 * ActionlogFmcsUserItemScopeTest (GHSA-mch3-g6rh-gj22).
 *
 * Fix (Actionlog::applyCrossCompanyScope, delegated from Company::
 * scopeCompanyables): null-company action_logs with a scalar-company
 * item_type now require the item's CURRENT company_id to be in the
 * caller's companies.
 */
class ActionlogFmcsTenantScopedItemTest extends TestCase
{
    private function makeViewerInCompany(Company $company, array $permissionKeys = ['reports.view']): User
    {
        $permissions = [];
        foreach ($permissionKeys as $key) {
            $permissions[$key] = '1';
        }

        return $company->users()->save(
            User::factory()->make(['permissions' => json_encode($permissions)])
        );
    }

    public function test_strict_fmcs_hides_null_company_log_for_cross_company_asset(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyB);

        // Asset currently owned by Company A, with a historical action_log
        // row whose company_id is NULL (the reporter's scenario).
        $asset = Asset::factory()->create(['company_id' => $companyA->id]);
        $log = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        $this->assertNotContains(
            $log->id,
            $visibleIds,
            'Cross-tenant NULL-company Asset action_log leaked to a strict-FMCS viewer in another company.'
        );
    }

    public function test_strict_fmcs_hides_null_company_log_for_cross_company_license(): void
    {
        // Same shape as the asset case, but for licenses, which are the
        // reporter's primary concern. log_meta carries the product key
        // diff and the mask test below covers the serial-exposure half
        // of the advisory.
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyB);

        $license = License::factory()->create(['company_id' => $companyA->id]);
        $log = Actionlog::factory()->create([
            'item_type' => License::class,
            'item_id' => $license->id,
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        $this->assertNotContains($log->id, $visibleIds);
    }

    public function test_strict_fmcs_shows_null_company_log_when_item_is_in_viewer_company(): void
    {
        // Positive regression: viewer in Company A sees the historical
        // NULL-company log because the asset is NOW in Company A, which
        // is what admins expect when looking at an asset's history.
        $this->settings->enableMultipleFullCompanySupport();
        $companyA = Company::factory()->create();

        $viewer = $this->makeViewerInCompany($companyA);
        $asset = Asset::factory()->create(['company_id' => $companyA->id]);

        $log = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $this->assertContains($log->id, Actionlog::query()->pluck('id')->all());
    }

    public function test_global_config_null_company_logs_remain_cross_company_visible(): void
    {
        // Regression guard: AssetModel / Manufacturer / Statuslabel /
        // Category / Depreciation / Supplier are admin-authored global
        // config. Their null-company action_logs have always been visible
        // cross-tenant and must continue to be, so the fix doesn't
        // over-correct and break the global-config half of the rule.
        $this->settings->enableMultipleFullCompanySupport();
        $companyA = Company::factory()->create();

        $viewer = $this->makeViewerInCompany($companyA);

        $samples = [
            [AssetModel::class, AssetModel::factory()->create()->id],
            [Manufacturer::class, Manufacturer::factory()->create()->id],
            [Statuslabel::class, Statuslabel::factory()->create()->id],
            [Category::class, Category::factory()->create()->id],
        ];

        $logIds = [];
        foreach ($samples as [$itemType, $itemId]) {
            $logIds[$itemType] = Actionlog::factory()->create([
                'item_type' => $itemType,
                'item_id' => $itemId,
                'company_id' => null,
            ])->id;
        }

        $this->actingAs($viewer);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        foreach ($logIds as $itemType => $logId) {
            $this->assertContains(
                $logId,
                $visibleIds,
                "Null-company {$itemType} action_log should stay cross-tenant visible (safe global config)."
            );
        }
    }

    public function test_superuser_sees_null_company_logs_regardless_of_item_owner(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $superuser = User::factory()->superuser()->create();
        $assetInA = Asset::factory()->create(['company_id' => $companyA->id]);
        $assetInB = Asset::factory()->create(['company_id' => $companyB->id]);

        $logA = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $assetInA->id,
            'company_id' => null,
        ]);
        $logB = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $assetInB->id,
            'company_id' => null,
        ]);

        $this->actingAs($superuser);

        $visibleIds = Actionlog::query()->pluck('id')->all();
        $this->assertContains($logA->id, $visibleIds);
        $this->assertContains($logB->id, $visibleIds);
    }

    public function test_every_scalar_company_item_type_is_gated_by_items_current_company(): void
    {
        // The runtime registry (Actionlog::scalarCompanyItemTables) is
        // the authoritative list of "CompanyableTrait models with a
        // scalar company_id column." Iterate it directly so new
        // Companyable models are picked up by this test the moment they
        // land, without an explicit test update. Pivot-scoped types
        // (currently just User) are covered in the sibling user-item
        // scope test file.
        //
        // Models without a factory are skipped. The sibling enumeration
        // test confirms the registry is complete, and this test proves
        // the gate works for every registry entry that can be
        // instantiated through the factory path.
        //
        // Location is excluded from this enumeration because its
        // visibility is governed by scope_locations_fmcs, which has
        // its own dedicated pair of tests below (shared vs scoped).
        // Mixing Location in here forces a setting choice that the
        // per-type assertion can't accommodate.
        $this->settings->enableMultipleFullCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyB);

        $registry = Actionlog::scalarCompanyItemTables();

        $this->assertNotEmpty($registry, 'Scalar-company registry is empty. Companyable models are not being discovered.');

        $exercised = [];
        $logIds = [];
        foreach ($registry as $itemType => $_table) {
            /** @var \Illuminate\Database\Eloquent\Model $modelClass */
            $modelClass = $itemType;
            if ($modelClass === Location::class) {
                continue;
            }
            if (! method_exists($modelClass, 'factory')) {
                continue;
            }
            try {
                $item = $modelClass::factory()->create(['company_id' => $companyA->id]);
            } catch (\Throwable $e) {
                continue;
            }
            if (! $item || $item->getKey() === null) {
                continue;
            }

            $exercised[] = $itemType;
            $logIds[$itemType] = Actionlog::factory()->create([
                'item_type' => $itemType,
                'item_id' => $item->getKey(),
                'company_id' => null,
            ])->id;
        }

        $this->actingAs($viewer);

        $visibleIds = Actionlog::query()->pluck('id')->all();

        foreach ($logIds as $itemType => $logId) {
            $this->assertNotContains(
                $logId,
                $visibleIds,
                "Cross-tenant NULL-company {$itemType} action_log leaked."
            );
        }

        // Known-good coverage floor so a factory regression doesn't
        // silently empty this test out.
        $this->assertGreaterThanOrEqual(5, count($exercised), 'Fewer registry entries exercised than expected. Factory regression?');
    }

    public function test_floater_mode_shows_null_company_log_for_floating_item(): void
    {
        // Floater mode's premise: null-company items are shared across
        // tenants. A floating asset (assets.company_id IS NULL) is
        // visible to every viewer under floater, so its historical
        // NULL-company action_log must be too. The scope delegates to
        // the item's own model query, which already returns floating
        // items under floater, and this test pins that delegation.
        $this->settings->enableFloaterMode();
        [, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyB);
        $floatingAsset = Asset::factory()->create(['company_id' => null]);

        $log = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $floatingAsset->id,
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $this->assertContains(
            $log->id,
            Actionlog::query()->pluck('id')->all(),
            'Floating asset NULL-company action_log was hidden from a cross-tenant floater viewer. The scope should mirror the item\'s own visibility.'
        );
    }

    public function test_floater_mode_still_hides_null_company_log_for_owned_cross_tenant_asset(): void
    {
        // Floater opens visibility for TRULY floating items (null
        // owner), not for owned items whose action_log happens to
        // predate the ownership assignment. A Company-A asset with a
        // historical NULL-company log is not floating. A Company-B
        // viewer under floater should still not see its logs.
        $this->settings->enableFloaterMode();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyB);
        $ownedAsset = Asset::factory()->create(['company_id' => $companyA->id]);

        $log = Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $ownedAsset->id,
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $this->assertNotContains($log->id, Actionlog::query()->pluck('id')->all());
    }

    public function test_shared_locations_setting_leaves_location_action_logs_cross_tenant_visible(): void
    {
        // scope_locations_fmcs = 0 (the default) tells Snipe-IT that
        // Locations are cross-tenant shared, and Company::scope-
        // CompanyablesDirectly short-circuits the locations table
        // entirely. Location action_logs need to follow the same rule
        // or the UI inconsistency shows up as "I can see the location
        // but not its edit history."
        $this->settings->enableMultipleFullCompanySupport();
        // Explicit pin. scope_locations_fmcs defaults to 0, but setting
        // it here keeps this test independent of future default flips.
        \App\Models\Setting::getSettings()->update(['scope_locations_fmcs' => 0]);
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyB);
        $location = Location::factory()->create(['company_id' => $companyA->id]);

        $log = Actionlog::factory()->create([
            'item_type' => Location::class,
            'item_id' => $location->id,
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $this->assertContains(
            $log->id,
            Actionlog::query()->pluck('id')->all(),
            'Cross-tenant Location action_log was hidden with scope_locations_fmcs = 0. Locations are shared under that setting and their logs must follow.'
        );
    }

    public function test_scoped_locations_setting_hides_cross_tenant_location_action_logs(): void
    {
        // Mirror of the test above. With scope_locations_fmcs = 1,
        // Locations are tenant-scoped like every other scalar-company
        // item, and their NULL-company history follows the owning
        // tenant.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $viewer = $this->makeViewerInCompany($companyB);
        $location = Location::factory()->create(['company_id' => $companyA->id]);

        $log = Actionlog::factory()->create([
            'item_type' => Location::class,
            'item_id' => $location->id,
            'company_id' => null,
        ]);

        $this->actingAs($viewer);

        $this->assertNotContains($log->id, Actionlog::query()->pluck('id')->all());
    }

    public function test_registry_includes_every_scalar_company_companyable_model(): void
    {
        // Explicit enumeration check so that if someone refactors
        // scalarCompanyItemTables() in a way that misses a model, this
        // test flags it even if no new types are added. Any
        // CompanyableTrait model whose table has a company_id column
        // has to be in the registry, except Actionlog itself (self-
        // reference, filtered by the registry builder).
        $registry = Actionlog::scalarCompanyItemTables();

        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');
            if ($class === Actionlog::class) {
                continue;
            }
            if (! class_exists($class) || ! is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)) {
                continue;
            }
            if ((new \ReflectionClass($class))->isAbstract()) {
                continue;
            }
            if (! in_array(\App\Models\Traits\CompanyableTrait::class, class_uses_recursive($class), true)) {
                continue;
            }

            $instance = app($class);
            if (! \Illuminate\Support\Facades\Schema::hasColumn($instance->getTable(), 'company_id')) {
                continue;
            }

            $this->assertArrayHasKey(
                $class,
                $registry,
                "{$class} uses CompanyableTrait and has a company_id column but is missing from Actionlog::scalarCompanyItemTables()."
            );
        }
    }
}
