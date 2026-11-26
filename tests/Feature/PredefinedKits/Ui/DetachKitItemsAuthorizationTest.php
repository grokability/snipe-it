<?php

namespace Tests\Feature\PredefinedKits\Ui;

use App\Models\Accessory;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\License;
use App\Models\PredefinedKit;
use App\Models\User;
use Tests\TestCase;

/**
 * Web sibling of Api\DetachKitItemsAuthorizationTest. The web controller
 * had the same parent-only `authorize('update', PredefinedKit::class)`
 * pattern and now resolves the child through the parent relationship
 * plus `authorize('view', $child)`. Web redirects with an error on a
 * missing or unreadable child instead of returning JSON 404.
 */
class DetachKitItemsAuthorizationTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Licenses
    // -------------------------------------------------------------------------

    public function test_web_detach_kit_license_requires_view_permission_on_license(): void
    {
        $kit = PredefinedKit::factory()->create();
        $license = License::factory()->create();
        $kit->licenses()->attach($license->id, ['quantity' => 1]);

        $this->actingAs(User::factory()->editPredefinedKits()->create())
            ->delete(route('kits.licenses.detach', ['kit' => $kit->id, 'license_id' => $license->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('kits_licenses', [
            'kit_id' => $kit->id,
            'license_id' => $license->id,
        ]);
    }

    public function test_web_detach_kit_license_succeeds_with_both_permissions(): void
    {
        $kit = PredefinedKit::factory()->create();
        $license = License::factory()->create();
        $kit->licenses()->attach($license->id, ['quantity' => 1]);

        $this->actingAs(User::factory()->editPredefinedKits()->viewLicenses()->create())
            ->delete(route('kits.licenses.detach', ['kit' => $kit->id, 'license_id' => $license->id]))
            ->assertRedirect(route('kits.edit', $kit->id));

        $this->assertDatabaseMissing('kits_licenses', [
            'kit_id' => $kit->id,
            'license_id' => $license->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Models
    // -------------------------------------------------------------------------

    public function test_web_detach_kit_model_requires_view_permission_on_model(): void
    {
        $kit = PredefinedKit::factory()->create();
        $model = AssetModel::factory()->create();
        $kit->models()->attach($model->id, ['quantity' => 1]);

        $this->actingAs(User::factory()->editPredefinedKits()->create())
            ->delete(route('kits.models.detach', ['kit' => $kit->id, 'model_id' => $model->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('kits_models', [
            'kit_id' => $kit->id,
            'model_id' => $model->id,
        ]);
    }

    public function test_web_detach_kit_model_succeeds_with_both_permissions(): void
    {
        $kit = PredefinedKit::factory()->create();
        $model = AssetModel::factory()->create();
        $kit->models()->attach($model->id, ['quantity' => 1]);

        $this->actingAs(User::factory()->editPredefinedKits()->viewAssetModels()->create())
            ->delete(route('kits.models.detach', ['kit' => $kit->id, 'model_id' => $model->id]))
            ->assertRedirect(route('kits.edit', $kit->id));

        $this->assertDatabaseMissing('kits_models', [
            'kit_id' => $kit->id,
            'model_id' => $model->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Consumables
    // -------------------------------------------------------------------------

    public function test_web_detach_kit_consumable_requires_view_permission_on_consumable(): void
    {
        $kit = PredefinedKit::factory()->create();
        $consumable = Consumable::factory()->create();
        $kit->consumables()->attach($consumable->id, ['quantity' => 1]);

        $this->actingAs(User::factory()->editPredefinedKits()->create())
            ->delete(route('kits.consumables.detach', ['kit' => $kit->id, 'consumable_id' => $consumable->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('kits_consumables', [
            'kit_id' => $kit->id,
            'consumable_id' => $consumable->id,
        ]);
    }

    public function test_web_detach_kit_consumable_succeeds_with_both_permissions(): void
    {
        $kit = PredefinedKit::factory()->create();
        $consumable = Consumable::factory()->create();
        $kit->consumables()->attach($consumable->id, ['quantity' => 1]);

        $this->actingAs(User::factory()->editPredefinedKits()->viewConsumables()->create())
            ->delete(route('kits.consumables.detach', ['kit' => $kit->id, 'consumable_id' => $consumable->id]))
            ->assertRedirect(route('kits.edit', $kit->id));

        $this->assertDatabaseMissing('kits_consumables', [
            'kit_id' => $kit->id,
            'consumable_id' => $consumable->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Accessories
    // -------------------------------------------------------------------------

    public function test_web_detach_kit_accessory_requires_view_permission_on_accessory(): void
    {
        $kit = PredefinedKit::factory()->create();
        $accessory = Accessory::factory()->create();
        $kit->accessories()->attach($accessory->id, ['quantity' => 1]);

        $this->actingAs(User::factory()->editPredefinedKits()->create())
            ->delete(route('kits.accessories.detach', ['kit' => $kit->id, 'accessory_id' => $accessory->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('kits_accessories', [
            'kit_id' => $kit->id,
            'accessory_id' => $accessory->id,
        ]);
    }

    public function test_web_detach_kit_accessory_succeeds_with_both_permissions(): void
    {
        $kit = PredefinedKit::factory()->create();
        $accessory = Accessory::factory()->create();
        $kit->accessories()->attach($accessory->id, ['quantity' => 1]);

        $this->actingAs(User::factory()->editPredefinedKits()->viewAccessories()->create())
            ->delete(route('kits.accessories.detach', ['kit' => $kit->id, 'accessory_id' => $accessory->id]))
            ->assertRedirect(route('kits.edit', $kit->id));

        $this->assertDatabaseMissing('kits_accessories', [
            'kit_id' => $kit->id,
            'accessory_id' => $accessory->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // FMCS cross-company
    // -------------------------------------------------------------------------

    public function test_web_detach_kit_license_scoped_out_under_fmcs_leaves_pivot_in_place(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $kit = PredefinedKit::factory()->create();
        $licenseB = License::factory()->for($companyB, 'company')->create();
        $kit->licenses()->attach($licenseB->id, ['quantity' => 1]);

        $actor = User::factory()->editPredefinedKits()->viewLicenses()->forCompany($companyA->id)->create();

        $this->actingAs($actor)
            ->delete(route('kits.licenses.detach', ['kit' => $kit->id, 'license_id' => $licenseB->id]))
            ->assertRedirect(route('kits.edit', $kit->id));

        $this->assertDatabaseHas('kits_licenses', [
            'kit_id' => $kit->id,
            'license_id' => $licenseB->id,
        ]);
    }
}
